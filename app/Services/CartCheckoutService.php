<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\HostingAccount;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use RuntimeException;

class CartCheckoutService
{
    public function __construct(private readonly CartService $carts)
    {
    }

    /**
     * @return array{order: ServiceOrder|null, prices_changed: bool}
     */
    public function createOrder(Request $request, Cart $cart): array
    {
        if ($cart->service_order_id) {
            $existingOrder = ServiceOrder::findOrFail($cart->service_order_id);
            if (in_array($existingOrder->status, ['awaiting_payment', 'paid', 'processing', 'partially_fulfilled', 'fulfilled'], true)) {
                return ['order' => $existingOrder, 'prices_changed' => false];
            }
            throw new RuntimeException('This cart is already linked to an order that cannot be paid.');
        }

        if ($this->carts->reprice($cart)) {
            return ['order' => null, 'prices_changed' => true];
        }

        $cart = $cart->fresh()->load('items.hostingPackage', 'items.parentItem');
        if ($cart->items->isEmpty()) {
            throw new RuntimeException('Your cart is empty.');
        }

        $cartItems = $cart->items->sortBy('id')->values();
        $itemIds = $cartItems->modelKeys();
        $itemUpdatedAt = $cartItems->mapWithKeys(fn (CartItem $item) => [$item->id => $item->updated_at?->toISOString()]);

        try {
            $order = DB::transaction(function () use ($request, $cart, $itemIds, $itemUpdatedAt) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($lockedCart->status !== 'active' || $lockedCart->service_order_id) {
                throw new RuntimeException('This cart is not available for checkout.');
            }

            $items = $lockedCart->items()->with('hostingPackage', 'parentItem')->orderBy('id')->lockForUpdate()->get();
            if ($items->modelKeys() !== $itemIds) {
                throw new RuntimeException('Your cart changed during checkout. Review it and try again.');
            }
            foreach ($items as $item) {
                if ($item->updated_at?->toISOString() !== $itemUpdatedAt->get($item->id)) {
                    throw new RuntimeException('Your cart changed during checkout. Review it and try again.');
                }
                if ($item->quantity !== 1) {
                    throw new RuntimeException('Cart quantities are not supported for this item.');
                }
            }

            $currency = strtoupper($lockedCart->currency);
            if (! in_array($currency, $this->carts->supportedCurrencies(), true)) {
                throw new RuntimeException('This currency is no longer enabled for Paystack checkout. Update your cart currency.');
            }
            $exchangeRate = $currency === 'NGN' ? $this->carts->ngnPerUsd() : null;
            $hostingItems = $items->where('item_type', 'hosting')->values();
            $allocations = $this->allocateHostingServers($hostingItems);

            $total = round($items->sum(fn (CartItem $item) => (float) $item->line_total), 2);
            $baseTotal = round($items->sum(fn (CartItem $item) =>
                (float) data_get($item->price_metadata, 'base_price_usd', 0) * $item->quantity), 2);
            if ($total <= 0 || $baseTotal <= 0) {
                throw new RuntimeException('The cart total is invalid. Please contact support.');
            }

            $legacyServerId = $hostingItems->count() === 1 ? $allocations[$hostingItems->first()->id]->id : null;
            $legacyPackageId = $hostingItems->count() === 1 ? $hostingItems->first()->hosting_package_id : null;
            $primaryDomain = $items->firstWhere('item_type', 'domain_registration')?->domain_name
                ?? $hostingItems->first()?->domain_name;

            $order = ServiceOrder::create([
                'user_id' => $request->user()->id,
                'hosting_server_id' => $legacyServerId,
                'hosting_package_id' => $legacyPackageId,
                'order_number' => 'DRY-'.Str::upper(Str::random(10)),
                'service_type' => 'marketplace',
                'domain_name' => $primaryDomain,
                'amount_ngn' => $currency === 'NGN' ? (int) round($total) : 0,
                'amount' => $total,
                'currency' => $currency,
                'status' => 'awaiting_payment',
                'expires_at' => now()->addMinutes(max(1, (int) config('services.paystack.order_expiry_minutes', 30))),
                'metadata' => [
                    'checkout_source' => 'cart',
                    'cart_id' => $lockedCart->id,
                    'price_snapshot' => [
                        'base_currency' => 'USD',
                        'base_total' => $baseTotal,
                        'selling_total' => $total,
                        'currency' => $currency,
                        'exchange_rate' => $exchangeRate,
                        'quoted_at' => now()->toIso8601String(),
                    ],
                    'item_count' => $items->count(),
                ],
            ]);

            $orderItemsByCartId = [];
            foreach ($items->where('item_type', 'domain_registration') as $item) {
                $orderItem = $this->createOrderItem($order, $item, null, $currency, $exchangeRate);
                $orderItemsByCartId[$item->id] = $orderItem;
            }
            foreach ($hostingItems as $item) {
                $parentId = $item->parent_item_id
                    ? ($orderItemsByCartId[$item->parent_item_id]->id ?? null)
                    : null;
                if ($item->parent_item_id && ! $parentId) {
                    throw new RuntimeException('A hosting item is no longer attached to a valid domain cart item.');
                }
                $orderItem = $this->createOrderItem(
                    $order,
                    $item,
                    $allocations[$item->id]->id,
                    $currency,
                    $exchangeRate,
                    $parentId
                );
                $orderItemsByCartId[$item->id] = $orderItem;
            }

            $lockedCart->update([
                'status' => 'checkout_pending',
                'service_order_id' => $order->id,
            ]);

            return $order->load('items');
            });
        } catch (QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'domain_reservation')) {
                throw new RuntimeException('This domain is already reserved by another order. Remove it from the cart or contact support.', 0, $exception);
            }

            throw $exception;
        }

        return ['order' => $order, 'prices_changed' => false];
    }

    private function allocateHostingServers(Collection $hostingItems): array
    {
        if ($hostingItems->isEmpty()) {
            return [];
        }

        $servers = HostingServer::query()->where('is_active', true)->orderBy('id')->lockForUpdate()->get();
        if ($servers->isEmpty()) {
            throw new RuntimeException('Hosting checkout is temporarily unavailable because no active hosting server is configured. Ask an administrator to add or activate a server in the Hosting Control Centre.');
        }

        $capacity = [];
        foreach ($servers as $server) {
            $legacyOrders = ServiceOrder::query()->where('hosting_server_id', $server->id)
                ->where('status', 'awaiting_payment')
                ->whereDoesntHave('items', fn ($query) => $query->whereNotNull('hosting_server_id'))
                ->count();
            $itemReservations = ServiceOrderItem::query()->where('hosting_server_id', $server->id)
                ->where('item_type', 'hosting')->whereHas('order', fn ($query) => $query->where('status', 'awaiting_payment'))
                ->count();
            $capacity[$server->id] = [
                'total' => HostingAccount::query()->where('hosting_server_id', $server->id)
                    ->whereIn('status', ['pending_provisioning', 'active', 'suspended'])->count()
                    + $legacyOrders + $itemReservations,
                'packages' => [],
            ];
        }

        $allocations = [];
        foreach ($hostingItems as $item) {
            $package = $item->hostingPackage;
            if (! $package || ! $package->is_active || (float) $package->price_usd <= 0
                || $item->billing_period !== $package->billing_cycle) {
                throw new RuntimeException('A hosting package or its billing period changed. Review your cart.');
            }

            $server = null;
            foreach ($servers as $candidate) {
                $packageId = $package->id;
                if (! array_key_exists($packageId, $capacity[$candidate->id]['packages'])) {
                    $legacyPackageOrders = ServiceOrder::query()->where('hosting_server_id', $candidate->id)
                        ->where('hosting_package_id', $packageId)->where('status', 'awaiting_payment')
                        ->whereDoesntHave('items', fn ($query) => $query->whereNotNull('hosting_server_id'))
                        ->count();
                    $itemPackageReservations = ServiceOrderItem::query()
                        ->where('hosting_server_id', $candidate->id)->where('hosting_package_id', $packageId)
                        ->where('item_type', 'hosting')
                        ->whereHas('order', fn ($query) => $query->where('status', 'awaiting_payment'))
                        ->count();
                    $capacity[$candidate->id]['packages'][$packageId] =
                        HostingAccount::query()->where('hosting_server_id', $candidate->id)
                            ->where('hosting_package_id', $packageId)
                            ->whereIn('status', ['pending_provisioning', 'active', 'suspended'])->count()
                        + $legacyPackageOrders + $itemPackageReservations;
                }

                $packageLimit = (int) data_get(
                    $candidate->package_capacity,
                    $package->slug,
                    $package->max_accounts_per_server
                );
                if ($capacity[$candidate->id]['total'] < $candidate->max_accounts
                    && $capacity[$candidate->id]['packages'][$packageId] < $packageLimit) {
                    $server = $candidate;
                    $capacity[$candidate->id]['total']++;
                    $capacity[$candidate->id]['packages'][$packageId]++;
                    break;
                }
            }

            if (! $server) {
                throw new RuntimeException('No hosting server currently has capacity for '.$package->name.'. Remove it or try again later.');
            }
            $allocations[$item->id] = $server;
        }

        return $allocations;
    }

    private function createOrderItem(
        ServiceOrder $order,
        CartItem $item,
        ?int $serverId,
        string $currency,
        ?float $exchangeRate,
        ?int $parentItemId = null
    ): ServiceOrderItem {
        $quote = $item->price_metadata ?? [];
        $snapshot = [
            'base_price' => data_get($quote, 'base_price_usd'),
            'base_currency' => 'USD',
            'selling_price' => (float) $item->unit_price,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'quoted_at' => data_get($quote, 'quoted_at'),
            'pricing' => $quote,
            'order_time_snapshot_at' => now()->toIso8601String(),
        ];

        return ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'item_type' => $item->item_type,
            'domain_name' => $item->domain_name,
            'hosting_package_id' => $item->hosting_package_id,
            'hosting_server_id' => $serverId,
            'parent_item_id' => $parentItemId,
            'provider' => $item->provider,
            'provider_product_id' => $item->provider_product_id,
            'quantity' => $item->quantity,
            'billing_period' => $item->billing_period,
            'unit_price' => $item->unit_price,
            'line_total' => $item->line_total,
            'currency' => $currency,
            'item_metadata' => $snapshot,
            'fulfillment_status' => 'awaiting_payment',
            'domain_reservation_key' => $item->item_type === 'domain_registration'
                ? hash('sha256', strtolower(trim((string) $item->domain_name)))
                : null,
        ]);
    }
}
