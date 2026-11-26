<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\HostingAccount;
use App\Models\HostingPackage;
use App\Models\User;
use App\Services\Domain\PorkbunClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CartService
{
    private const SESSION_TOKEN = 'drypp_cart_token';

    public function __construct(private readonly PorkbunClient $porkbun)
    {
    }

    public function current(Request $request): Cart
    {
        if ($request->user()) {
            return $this->mergeGuestCart($request, $request->user());
        }

        $token = $request->session()->get(self::SESSION_TOKEN);
        $cart = is_string($token)
            ? Cart::query()->where('token', $token)->whereNull('user_id')
                ->where('status', 'active')->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first()
            : null;

        if (! $cart) {
            $token = Str::random(64);
            $cart = Cart::create([
                'token' => $token,
                'currency' => $this->defaultCurrency(),
                'status' => 'active',
                'expires_at' => now()->addDays(30),
            ]);
            $request->session()->put(self::SESSION_TOKEN, $token);
        }

        return $cart->load('items.hostingPackage', 'items.parentItem');
    }

    public function mergeGuestCart(Request $request, User $user): Cart
    {
        $token = $request->session()->get(self::SESSION_TOKEN);

        $cart = DB::transaction(function () use ($token, $user) {
            $userCart = Cart::query()->where('user_id', $user->id)->where('status', 'active')
                ->lockForUpdate()->first();

            $guestCart = is_string($token)
                ? Cart::query()->where('token', $token)->whereNull('user_id')->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })->lockForUpdate()->first()
                : null;

            if (! $userCart && ! $guestCart) {
                $userCart = Cart::query()->where('user_id', $user->id)->where('status', 'checkout_pending')
                    ->latest('id')->lockForUpdate()->first();
            }

            if (! $userCart) {
                $userCart = Cart::create([
                    'user_id' => $user->id,
                    'token' => Str::random(64),
                    'currency' => $this->defaultCurrency(),
                    'status' => 'active',
                ]);
            }

            if (! $guestCart || $guestCart->id === $userCart->id || $userCart->status !== 'active') {
                return $userCart;
            }

            $guestCart->load('items.hostingPackage', 'items.parentItem');
            $userCart->load('items');
            if ($userCart->items->isEmpty()) {
                $userCart->currency = in_array($guestCart->currency, $this->supportedCurrencies(), true)
                    ? $guestCart->currency
                    : $this->defaultCurrency();
                $userCart->save();
            }

            $parentMap = [];
            foreach ($guestCart->items->whereNull('parent_item_id')->where('item_type', '!=', 'hosting') as $guestItem) {
                $query = $userCart->items()->where('item_type', $guestItem->item_type);
                $query = $guestItem->domain_name === null
                    ? $query->whereNull('domain_name')
                    : $query->whereRaw('LOWER(domain_name) = ?', [strtolower($guestItem->domain_name)]);
                $target = $query->where('hosting_package_id', $guestItem->hosting_package_id)->first();

                if (! $target) {
                    $target = $this->copyItem($guestItem, $userCart, null);
                }
                $parentMap[$guestItem->id] = $target->id;
            }

            foreach ($guestCart->items->where('item_type', 'hosting') as $guestItem) {
                $parentId = $guestItem->parent_item_id
                    ? ($parentMap[$guestItem->parent_item_id] ?? null)
                    : null;

                if ($guestItem->parent_item_id && ! $parentId) {
                    continue;
                }

                $duplicate = $userCart->items()->where('item_type', 'hosting')
                    ->where('hosting_package_id', $guestItem->hosting_package_id)
                    ->whereRaw('LOWER(domain_name) = ?', [strtolower((string) $guestItem->domain_name)])
                    ->where('parent_item_id', $parentId)->first();

                $target = $duplicate ?: $this->copyItem($guestItem, $userCart, $parentId);
                $parentMap[$guestItem->id] = $target->id;
            }

            foreach ($guestCart->items->whereNotNull('parent_item_id')->where('item_type', '!=', 'hosting') as $guestItem) {
                $parentId = $parentMap[$guestItem->parent_item_id] ?? null;
                if (! $parentId) {
                    throw new RuntimeException('A guest cart item is missing its parent relationship.');
                }
                $query = $userCart->items()->where('item_type', $guestItem->item_type)
                    ->where('parent_item_id', $parentId);
                $query = $guestItem->domain_name === null
                    ? $query->whereNull('domain_name')
                    : $query->whereRaw('LOWER(domain_name) = ?', [strtolower($guestItem->domain_name)]);
                $target = $query->where('hosting_package_id', $guestItem->hosting_package_id)->first();
                $target ??= $this->copyItem($guestItem, $userCart, $parentId);
                $parentMap[$guestItem->id] = $target->id;
            }

            $guestCart->items()->delete();
            $guestCart->update(['status' => 'converted']);

            return $userCart;
        });

        if (is_string($token)) {
            $request->session()->forget(self::SESSION_TOKEN);
        }

        return $cart->fresh()->load('items.hostingPackage', 'items.parentItem');
    }

    public function addDomain(Request $request, string $domain): CartItem
    {
        $domain = strtolower(trim($domain));
        $cart = $this->current($request);
        $existing = $cart->items()->where('item_type', 'domain_registration')
            ->whereRaw('LOWER(domain_name) = ?', [$domain])->first();

        if ($existing) {
            return $existing;
        }

        $quote = $this->quoteDomain($domain);
        return DB::transaction(function () use ($cart, $domain, $quote) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);

            $existing = $lockedCart->items()->where('item_type', 'domain_registration')
                ->whereRaw('LOWER(domain_name) = ?', [$domain])->first();
            if ($existing) {
                return $existing;
            }

            return $lockedCart->items()->create($this->itemPriceAttributes(
                $lockedCart,
                'domain_registration',
                $domain,
                null,
                'porkbun',
                strtolower(substr(strrchr($domain, '.'), 1)),
                'yearly',
                $quote['selling_price_usd'],
                $quote['metadata']
            ));
        });
    }

    public function addHosting(Request $request, HostingPackage $package, string $domain, ?int $domainItemId = null): CartItem
    {
        if (! $package->is_active || (float) $package->price_usd <= 0) {
            throw new RuntimeException('This hosting package is unavailable or has no valid USD price.');
        }

        $domain = strtolower(trim($domain));
        $cart = $this->current($request);
        return DB::transaction(function () use ($cart, $package, $domain, $domainItemId) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);

            $parent = null;
            if ($domainItemId) {
                $parent = $lockedCart->items()->whereKey($domainItemId)
                    ->where('item_type', 'domain_registration')->firstOrFail();
                $domain = strtolower((string) $parent->domain_name);
            } else {
                $parent = $lockedCart->items()->where('item_type', 'domain_registration')
                    ->whereRaw('LOWER(domain_name) = ?', [$domain])->first();
            }

            if (HostingAccount::query()->whereRaw('LOWER(domain_name) = ?', [$domain])
                ->whereIn('status', ['pending_provisioning', 'active', 'suspended'])->exists()) {
                throw new RuntimeException('This domain already has a Drypp hosting account.');
            }

            $duplicate = $lockedCart->items()->where('item_type', 'hosting')
                ->where('hosting_package_id', $package->id)
                ->whereRaw('LOWER(domain_name) = ?', [$domain])
                ->where('parent_item_id', $parent?->id)->first();
            if ($duplicate) {
                return $duplicate;
            }

            return $lockedCart->items()->create($this->itemPriceAttributes(
                $lockedCart,
                'hosting',
                $domain,
                $package->id,
                'verpex',
                $package->slug,
                $package->billing_cycle,
                (float) $package->price_usd,
                [
                    'package_name' => $package->name,
                    'package_slug' => $package->slug,
                    'billing_period' => $package->billing_cycle,
                ],
                $parent?->id
            ));
        });
    }

    public function updateHosting(Request $request, CartItem $item, HostingPackage $package): CartItem
    {
        $cart = $this->current($request);
        abort_unless($item->cart_id === $cart->id && $item->item_type === 'hosting', 404);
        if (! $package->is_active || (float) $package->price_usd <= 0) {
            throw new RuntimeException('This hosting package is unavailable or has no valid USD price.');
        }

        return DB::transaction(function () use ($cart, $item, $package) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);
            $lockedItem = $lockedCart->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();

            $metadata = [
                'package_name' => $package->name,
                'package_slug' => $package->slug,
                'billing_period' => $package->billing_cycle,
            ];
            $lockedItem->fill($this->itemPriceAttributes(
                $lockedCart,
                'hosting',
                (string) $lockedItem->domain_name,
                $package->id,
                'verpex',
                $package->slug,
                $package->billing_cycle,
                (float) $package->price_usd,
                $metadata,
                $lockedItem->parent_item_id
            ))->save();

            return $lockedItem->fresh('hostingPackage', 'parentItem');
        });
    }

    public function removeItem(Request $request, CartItem $item): void
    {
        $cart = $this->current($request);
        abort_unless($item->cart_id === $cart->id, 404);

        DB::transaction(function () use ($cart, $item) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);
            $lockedItem = $lockedCart->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $ids = $lockedItem->childItems()->pluck('id');
            $lockedCart->items()->whereIn('id', $ids)->delete();
            $lockedItem->delete();
        });
    }

    public function changeCurrency(Request $request, string $currency): Cart
    {
        $currency = strtoupper($currency);
        $this->assertSupportedCurrency($currency);
        $cart = $this->current($request);

        return DB::transaction(function () use ($cart, $currency) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);
            $rate = $currency === 'NGN' ? $this->ngnPerUsd() : null;

            foreach ($lockedCart->items as $item) {
                $metadata = $item->price_metadata ?? [];
                $basePrice = data_get($metadata, 'base_price_usd');
                if (! is_numeric($basePrice)) {
                    throw new RuntimeException('A cart item is missing its USD price snapshot and cannot be converted.');
                }
                $sellingPrice = round((float) $basePrice * ($rate ?? 1), 2);
                $metadata = array_merge($metadata, [
                    'selling_price' => $sellingPrice,
                    'currency' => $currency,
                    'exchange_rate' => $rate,
                    'quoted_at' => now()->toIso8601String(),
                ]);
                $item->update([
                    'unit_price' => $sellingPrice,
                    'line_total' => $sellingPrice * $item->quantity,
                    'currency' => $currency,
                    'price_metadata' => $metadata,
                ]);
            }

            $lockedCart->update(['currency' => $currency]);
            return $lockedCart->fresh()->load('items.hostingPackage', 'items.parentItem');
        });
    }

    public function reprice(Cart $cart): bool
    {
        $currency = strtoupper($cart->currency);
        $this->assertSupportedCurrency($currency);
        $cart->load('items.hostingPackage');
        if ($cart->items->isEmpty()) {
            throw new RuntimeException('Your cart is empty.');
        }

        $quotes = [];
        foreach ($cart->items as $item) {
            if ($item->item_type === 'domain_registration') {
                $quotes[$item->id] = $this->quoteDomain((string) $item->domain_name);
            } elseif ($item->item_type === 'hosting') {
                $package = HostingPackage::query()->whereKey($item->hosting_package_id)
                    ->where('is_active', true)->first();
                if (! $package) {
                    throw new RuntimeException('A hosting package in your cart is no longer available.');
                }
                $quotes[$item->id] = [
                    'selling_price_usd' => (float) $package->price_usd,
                    'metadata' => [
                        'package_name' => $package->name,
                        'package_slug' => $package->slug,
                        'billing_period' => $package->billing_cycle,
                    ],
                    'package' => $package,
                ];
            } else {
                throw new RuntimeException('Your cart contains an unsupported service type.');
            }
        }

        $rate = $currency === 'NGN' ? $this->ngnPerUsd() : 1.0;
        $changed = false;
        DB::transaction(function () use ($cart, $quotes, $rate, $currency, &$changed) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $this->assertActive($lockedCart);
            if (strtoupper($lockedCart->currency) !== $currency) {
                throw new RuntimeException('Your cart currency changed during checkout. Review your cart and try again.');
            }

            foreach ($quotes as $itemId => $quote) {
                $item = $lockedCart->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
                if (isset($quote['package']) && $item->hosting_package_id !== $quote['package']->id) {
                    $changed = true;
                    continue;
                }
                $sellingPrice = round($quote['selling_price_usd'] * $rate, 2);
                $metadata = array_merge($quote['metadata'], [
                    'base_price_usd' => round($quote['selling_price_usd'], 2),
                    'base_currency' => 'USD',
                    'selling_price' => $sellingPrice,
                    'currency' => $currency,
                    'exchange_rate' => $currency === 'NGN' ? $rate : null,
                    'quoted_at' => now()->toIso8601String(),
                ]);
                if (abs((float) $item->unit_price - $sellingPrice) >= 0.01
                    || $item->billing_period !== ($quote['package']->billing_cycle ?? $item->billing_period)) {
                    $changed = true;
                }
                $item->update([
                    'billing_period' => $quote['package']->billing_cycle ?? $item->billing_period,
                    'unit_price' => $sellingPrice,
                    'line_total' => $sellingPrice * $item->quantity,
                    'currency' => $currency,
                    'price_metadata' => $metadata,
                ]);
            }
        });

        return $changed;
    }

    public function quoteDomain(string $domain): array
    {
        $availability = $this->porkbun->checkAvailability($domain);
        $response = data_get($availability, 'json.response', []);
        $available = strtolower((string) data_get($response, 'avail', data_get($response, 'availability', '')));
        if (! $availability['successful'] || ! in_array($available, ['yes', 'available'], true)
            || (bool) data_get($response, 'sandbox', false)) {
            throw new RuntimeException('Live domain availability could not be confirmed for '.$domain.'.');
        }

        $pricing = $this->porkbun->pricing();
        if (! $pricing['successful']) {
            throw new RuntimeException('Live domain pricing could not be retrieved.');
        }

        $tld = strtolower(substr(strrchr($domain, '.'), 1));
        $tldPricing = data_get($pricing, 'json.response.'.$tld);
        $registration = data_get($tldPricing, 'registration');
        if (is_array($registration)) {
            $registration = data_get($registration, 'price');
        }
        $basePrice = data_get($response, 'price');
        if (! is_numeric($basePrice) || (float) $basePrice <= 0) {
            $basePrice = $registration;
        }
        if (! is_numeric($basePrice) || (float) $basePrice <= 0) {
            throw new RuntimeException('A valid live registration price was not returned for '.$domain.'.');
        }

        $markup = max(0, (float) config('services.porkbun.markup_percent', 40));
        $basePrice = round((float) $basePrice, 2);

        return [
            'selling_price_usd' => round($basePrice * (1 + $markup / 100), 2),
            'metadata' => [
                'registrar' => 'porkbun',
                'base_registration_usd' => $basePrice,
                'markup_percent' => $markup,
                'premium' => (bool) data_get($response, 'premium', false),
                'provider_quote' => $tldPricing,
            ],
        ];
    }

    public function supportedCurrencies(): array
    {
        $allowed = array_map('strtoupper', config('services.paystack.currencies', ['USD']));
        return array_values(array_intersect(['USD', 'NGN'], $allowed));
    }

    private function defaultCurrency(): string
    {
        $supported = $this->supportedCurrencies();
        if (in_array('USD', $supported, true)) {
            return 'USD';
        }
        if (in_array('NGN', $supported, true)) {
            $this->ngnPerUsd();
            return 'NGN';
        }

        throw new RuntimeException('No supported payment currency is configured.');
    }

    public function totals(Cart $cart): array
    {
        $cart->loadMissing('items');
        $subtotal = $cart->items->sum(fn (CartItem $item) => (float) $item->line_total);
        $baseSubtotal = $cart->items->sum(fn (CartItem $item) =>
            (float) data_get($item->price_metadata, 'base_price_usd', 0) * $item->quantity);

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
            'base_subtotal_usd' => round($baseSubtotal, 2),
        ];
    }

    public function ngnPerUsd(): float
    {
        $rate = DB::table('currency_display_settings')->value('ngn_per_usd');
        if (! is_numeric($rate) || (float) $rate <= 0) {
            throw new RuntimeException('A valid USD to NGN exchange rate is not configured.');
        }

        return (float) $rate;
    }

    private function quotePriceAttributes(
        Cart $cart,
        float $basePriceUsd,
        array $pricing,
        ?int $parentItemId = null
    ): array {
        $currency = strtoupper($cart->currency);
        $this->assertSupportedCurrency($currency);
        $rate = $currency === 'NGN' ? $this->ngnPerUsd() : 1.0;
        $sellingPrice = round($basePriceUsd * $rate, 2);

        return [
            'parent_item_id' => $parentItemId,
            'quantity' => 1,
            'unit_price' => $sellingPrice,
            'line_total' => $sellingPrice,
            'currency' => $currency,
            'price_metadata' => array_merge($pricing, [
                'base_price_usd' => round($basePriceUsd, 2),
                'base_currency' => 'USD',
                'selling_price' => $sellingPrice,
                'currency' => $currency,
                'exchange_rate' => $currency === 'NGN' ? $rate : null,
                'quoted_at' => now()->toIso8601String(),
            ]),
        ];
    }

    private function itemPriceAttributes(
        Cart $cart,
        string $type,
        string $domain,
        ?int $packageId,
        string $provider,
        ?string $providerProductId,
        ?string $billingPeriod,
        float $basePriceUsd,
        array $pricing,
        ?int $parentItemId = null
    ): array {
        return array_merge([
            'item_type' => $type,
            'domain_name' => $domain,
            'hosting_package_id' => $packageId,
            'provider' => $provider,
            'provider_product_id' => $providerProductId,
            'billing_period' => $billingPeriod,
        ], $this->quotePriceAttributes($cart, $basePriceUsd, $pricing, $parentItemId));
    }

    private function copyItem(CartItem $source, Cart $targetCart, ?int $parentItemId): CartItem
    {
        $metadata = $source->price_metadata ?? [];
        $basePrice = data_get($metadata, 'base_price_usd');
        if (! is_numeric($basePrice)) {
            throw new RuntimeException('A guest cart item is missing its USD price snapshot.');
        }

        $attributes = $this->itemPriceAttributes(
            $targetCart,
            $source->item_type,
            (string) $source->domain_name,
            $source->hosting_package_id,
            (string) $source->provider,
            $source->provider_product_id,
            $source->billing_period,
            (float) $basePrice,
            $metadata,
            $parentItemId
        );
        $attributes['quantity'] = $source->quantity;
        $attributes['line_total'] = (float) $attributes['unit_price'] * $source->quantity;

        return $targetCart->items()->create($attributes);
    }

    private function assertSupportedCurrency(string $currency): void
    {
        if (! in_array($currency, $this->supportedCurrencies(), true)) {
            throw new RuntimeException('This currency is not enabled for Paystack checkout.');
        }
        if ($currency === 'NGN') {
            $this->ngnPerUsd();
        }
    }

    private function assertActive(Cart $cart): void
    {
        if ($cart->status !== 'active') {
            throw new RuntimeException('This cart is no longer available for changes.');
        }
    }
}
