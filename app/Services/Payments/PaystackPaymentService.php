<?php

namespace App\Services\Payments;

use App\Models\HostingAccount;
use App\Models\Cart;
use App\Models\PaymentTransaction;
use App\Models\ServiceOrder;
use App\Models\UserService;
use App\Jobs\ProvisionHostingAccount;
use App\Services\ServiceOrderExpiryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Illuminate\Support\Str;

class PaystackPaymentService
{
    public function __construct(private readonly ServiceOrderExpiryService $orderExpiry)
    {
    }

    public function initializeForOrder(ServiceOrder $order, string $email, string $callbackUrl): string
    {
        $secret = config('services.paystack.secret_key');
        if (! $secret) {
            throw $this->customerSafeFailure(
                'Paystack secret key is missing.',
                'Payments are temporarily unavailable. Please try again later or contact support.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
        }

        $currency = strtoupper($order->currency ?: 'NGN');
        $allowedCurrencies = array_map('strtoupper', config('services.paystack.currencies', ['NGN']));
        if (! in_array($currency, $allowedCurrencies, true)) {
            throw $this->customerSafeFailure(
                'Order currency is not enabled for Paystack checkout.',
                'We can’t process this currency right now. Please choose another available currency or contact support.',
                [
                    'order_id' => $order->id,
                    'currency' => $currency,
                    'enabled_currencies' => $allowedCurrencies,
                ]
            );
        }

        $amountMinor = $this->amountInMinorUnits($order);
        if ($amountMinor < 1) {
            throw $this->customerSafeFailure(
                'Order amount is invalid for Paystack checkout.',
                'We couldn’t validate this order’s total. Please contact support before trying again.',
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );
        }

        $reservation = DB::transaction(function () use ($order, $currency, $amountMinor): array {
            $lockedOrder = ServiceOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'awaiting_payment') {
                throw $this->customerSafeFailure(
                    'Order is not awaiting payment during initialization.',
                    'This order is no longer available for payment. Return to your services or contact support.',
                    ['order_id' => $lockedOrder->id, 'order_status' => $lockedOrder->status]
                );
            }

            if ($lockedOrder->payment_reference) {
                $current = PaymentTransaction::query()
                    ->where('reference', $lockedOrder->payment_reference)
                    ->lockForUpdate()
                    ->first();

                if ($current && $current->status === 'initialized' && $current->authorization_url) {
                    return ['attempt' => $current, 'authorization_url' => $current->authorization_url];
                }

                $legacyUrl = data_get($lockedOrder->metadata, 'payment.authorization_url');
                if (! $current && is_string($legacyUrl)
                    && parse_url($legacyUrl, PHP_URL_SCHEME) === 'https'
                    && parse_url($legacyUrl, PHP_URL_HOST) === 'checkout.paystack.com') {
                    $current = PaymentTransaction::create([
                        'service_order_id' => $lockedOrder->id,
                        'reference' => $lockedOrder->payment_reference,
                        'amount_minor' => $amountMinor,
                        'currency' => $currency,
                        'status' => 'initialized',
                        'authorization_url' => $legacyUrl,
                    ]);

                    return ['attempt' => $current, 'authorization_url' => $legacyUrl];
                }

                if ($current && $current->status === 'initializing'
                    && $current->updated_at?->greaterThan(now()->subMinutes(2))) {
                    throw $this->customerSafeFailure(
                        'Paystack initialization is already in progress.',
                        'Payment setup is still processing. Please wait a moment, then try again.',
                        ['order_id' => $lockedOrder->id, 'reference' => $current->reference]
                    );
                }

                if ($current && in_array($current->status, ['initializing', 'initialization_unknown', 'verification_pending'], true)) {
                    throw $this->customerSafeFailure(
                        'Previous Paystack payment attempt requires reconciliation.',
                        'We couldn’t confirm the result of your previous payment attempt. Please don’t pay again; check My Services shortly or contact support with your order number.',
                        [
                            'order_id' => $lockedOrder->id,
                            'reference' => $current->reference,
                            'transaction_status' => $current->status,
                        ]
                    );
                }
            }

            $reference = 'DRYPP-'.Str::upper(Str::random(24));
            $attempt = PaymentTransaction::create([
                'service_order_id' => $lockedOrder->id,
                'reference' => $reference,
                'amount_minor' => $amountMinor,
                'currency' => $currency,
                'status' => 'initializing',
            ]);
            $lockedOrder->update(['payment_reference' => $reference]);

            return ['attempt' => $attempt, 'authorization_url' => null];
        });

        if ($reservation['authorization_url']) {
            return $reservation['authorization_url'];
        }

        /** @var PaymentTransaction $attempt */
        $attempt = $reservation['attempt'];
        try {
            $response = Http::withToken($secret)->acceptJson()->timeout(20)->post(
                'https://api.paystack.co/transaction/initialize',
                [
                    'email' => $email,
                    'amount' => $attempt->amount_minor,
                    'currency' => $attempt->currency,
                    'reference' => $attempt->reference,
                    'callback_url' => $callbackUrl,
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id' => $order->user_id,
                    ],
                ]
            );
        } catch (\Throwable $exception) {
            $this->recordInitializationFailure($attempt, 'initialization_unknown', 'Paystack connection failed; provider acceptance is unknown.');
            report($exception);
            Log::error('Paystack initialization result is unknown.', [
                'order_id' => $order->id,
                'reference' => $attempt->reference,
                'exception' => $exception->getMessage(),
            ]);
            throw new RuntimeException(
                'We couldn’t confirm whether payment setup completed. Please don’t pay again; check My Services shortly or contact support.',
                0,
                $exception
            );
        }

        $authorizationUrl = (string) $response->json('data.authorization_url', '');
        $providerReference = (string) $response->json('data.reference', '');
        if (! $response->successful() || $response->json('status') !== true
            || $providerReference !== $attempt->reference
            || parse_url($authorizationUrl, PHP_URL_SCHEME) !== 'https'
            || parse_url($authorizationUrl, PHP_URL_HOST) !== 'checkout.paystack.com') {
            $message = (string) $response->json('message', 'Paystack returned an invalid checkout response.');
            $definitiveRejection = ! $response->successful() || $response->json('status') !== true;
            $this->recordInitializationFailure(
                $attempt,
                $definitiveRejection ? 'initialization_failed' : 'initialization_unknown',
                Str::limit($message, 300)
            );
            Log::error('Paystack initialization failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reference' => $attempt->reference,
                'http_status' => $response->status(),
                'provider_message' => Str::limit($message, 300),
            ]);
            throw new RuntimeException($definitiveRejection
                ? 'Paystack couldn’t start your payment. Please try again later or contact support. If you see a charge, don’t pay again.'
                : 'We couldn’t confirm whether payment setup completed. Please don’t pay again; check My Services shortly or contact support.');
        }

        $attempt->update([
            'status' => 'initialized',
            'authorization_url' => $authorizationUrl,
            'metadata' => ['initialized_at' => now()->toIso8601String()],
        ]);

        $freshOrder = $order->fresh();
        $metadata = $freshOrder->metadata ?? [];
        $metadata['payment'] = [
            'provider' => 'paystack',
            'currency' => $attempt->currency,
            'amount_minor' => $attempt->amount_minor,
            'authorization_url' => $authorizationUrl,
            'initialized_at' => now()->toIso8601String(),
        ];
        $freshOrder->update(['metadata' => $metadata]);

        return $authorizationUrl;
    }

    public function verifyAndMarkPaid(string $reference): ServiceOrder
    {
        $secret = config('services.paystack.secret_key');
        if (! $secret) {
            throw $this->customerSafeFailure(
                'Paystack secret key is missing during verification.',
                'We couldn’t verify your payment right now. Please don’t pay again; check My Services shortly or contact support.',
                ['reference' => $reference]
            );
        }

        $attempt = PaymentTransaction::query()->with('order.user')->where('reference', $reference)->first();
        $order = $attempt?->order;
        if (! $order) {
            $order = ServiceOrder::with('user')->where('payment_reference', $reference)->firstOrFail();
            $attempt = PaymentTransaction::firstOrCreate(
                ['reference' => $reference],
                [
                    'service_order_id' => $order->id,
                    'amount_minor' => $this->amountInMinorUnits($order),
                    'currency' => strtoupper($order->currency ?: 'NGN'),
                    'status' => 'initializing',
                ]
            );
        }

        if (in_array($order->status, ['paid', 'processing', 'partially_fulfilled', 'fulfilled'], true)) {
            if ($attempt->status !== 'paid') {
                $attempt->update(['status' => 'paid', 'verified_at' => $attempt->verified_at ?: now()]);
            }

            return $order;
        }
        $expiredPaymentCanBeReconciled = $order->status === 'cancelled'
            && data_get($order->metadata, 'cancellation_reason') === 'payment_expired';
        if ($order->status !== 'awaiting_payment' && ! $expiredPaymentCanBeReconciled) {
            throw $this->customerSafeFailure(
                'Order is not awaiting payment during transaction verification.',
                'This order can’t be verified for payment. Please check My Services or contact support.',
                ['order_id' => $order->id, 'reference' => $reference, 'order_status' => $order->status]
            );
        }

        try {
            $response = Http::withToken($secret)->acceptJson()->timeout(20)->retry(2, 250)
                ->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));
        } catch (\Throwable $exception) {
            $attempt->update([
                'status' => 'verification_pending',
                'metadata' => array_merge($attempt->metadata ?? [], [
                    'verification_error' => 'Paystack verification service was unavailable.',
                ]),
            ]);
            report($exception);
            Log::error('Paystack transaction verification request failed.', [
                'order_id' => $order->id,
                'reference' => $reference,
                'exception' => $exception->getMessage(),
            ]);
            throw new RuntimeException(
                'We couldn’t verify your payment right now. Please don’t pay again; check My Services shortly or contact support.',
                0,
                $exception
            );
        }
        $data = $response->json('data');
        $expectedAmount = (int) $attempt->amount_minor;
        $expectedCurrency = strtoupper($attempt->currency);
        $allowedCurrencies = array_map('strtoupper', config('services.paystack.currencies', ['NGN']));
        if ($response->successful() && $response->json('status') === true
            && in_array(data_get($data, 'status'), ['failed', 'abandoned'], true)
            && (string) data_get($data, 'reference') === $reference
            && strtolower((string) data_get($data, 'customer.email')) === strtolower((string) $order->user->email)) {
            $attempt->update([
                'status' => 'failed',
                'provider_status' => (string) data_get($data, 'status'),
                'verified_at' => now(),
                'metadata' => $this->safeProviderMetadata($data),
            ]);
            $this->orderExpiry->markPaymentFailed($order->id);
            throw $this->customerSafeFailure(
                'Paystack confirmed the transaction failed.',
                'Paystack did not complete your payment. You can try checkout again; if you see a charge, contact support.',
                ['order_id' => $order->id, 'reference' => $reference, 'provider_status' => data_get($data, 'status')]
            );
        }

        $valid = $response->successful() && $response->json('status') === true
            && data_get($data, 'status') === 'success'
            && (int) data_get($data, 'amount', -1) === $expectedAmount
            && strtoupper((string) data_get($data, 'currency')) === $expectedCurrency
            && in_array($expectedCurrency, $allowedCurrencies, true)
            && strtolower((string) data_get($data, 'customer.email')) === strtolower((string) $order->user->email)
            && (string) data_get($data, 'reference') === $reference;
        if (! $valid) {
            $attempt->update([
                'status' => 'verification_pending',
                'provider_status' => (string) data_get($data, 'status', 'unverified'),
                'metadata' => $this->safeProviderMetadata(is_array($data) ? $data : []),
            ]);
            Log::error('Paystack transaction verification did not match the order.', [
                'order_id' => $order->id,
                'reference' => $reference,
                'expected_amount_minor' => $expectedAmount,
                'actual_amount_minor' => data_get($data, 'amount'),
                'expected_currency' => $expectedCurrency,
                'actual_currency' => data_get($data, 'currency'),
                'provider_status' => data_get($data, 'status'),
                'reference_matches' => (string) data_get($data, 'reference') === $reference,
                'customer_email_matches' => strtolower((string) data_get($data, 'customer.email')) === strtolower((string) $order->user->email),
            ]);
            throw new RuntimeException(
                'We couldn’t confirm your payment yet. Please don’t pay again; check My Services shortly or contact support with your order number.'
            );
        }

        $newlyPaid = false;
        $hostingAccountIds = [];
        $paidOrder = DB::transaction(function () use ($order, $attempt, $data, &$newlyPaid, &$hostingAccountIds) {
            $lockedOrder = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedAttempt = PaymentTransaction::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedOrder->status, ['paid', 'processing', 'partially_fulfilled', 'fulfilled'], true)) {
                $lockedAttempt->update([
                    'status' => 'paid',
                    'provider_status' => 'success',
                    'verified_at' => $lockedAttempt->verified_at ?: now(),
                    'metadata' => $this->safeProviderMetadata($data),
                ]);

                return $lockedOrder->fresh(['user', 'hostingPackage', 'hostingAccount', 'items']);
            }
            $expiredPaymentCanBeReconciled = $lockedOrder->status === 'cancelled'
                && data_get($lockedOrder->metadata, 'cancellation_reason') === 'payment_expired';
            if ($lockedOrder->status !== 'awaiting_payment' && ! $expiredPaymentCanBeReconciled) {
                throw $this->customerSafeFailure(
                    'Order status changed before Paystack verification completed.',
                    'We couldn’t confirm your payment. Please check My Services or contact support before trying again.',
                    ['order_id' => $lockedOrder->id, 'reference' => $attempt->reference, 'order_status' => $lockedOrder->status]
                );
            }
            $lockedOrder->update(['status' => 'paid', 'paid_at' => now()]);
            $lockedAttempt->update([
                'status' => 'paid',
                'provider_status' => 'success',
                'verified_at' => now(),
                'metadata' => $this->safeProviderMetadata($data),
            ]);
            $newlyPaid = true;

            // Create one customer-facing service record per purchased line item.
            // The unique order-item key makes this safe against duplicate webhook delivery.
            $orderItems = $lockedOrder->items()->with('hostingPackage')->get();
            foreach ($orderItems as $item) {
                if ($item->item_type === 'hosting' && $item->hosting_package_id && $item->domain_name) {
                    $hostingAccount = HostingAccount::firstOrCreate(
                        ['service_order_item_id' => $item->id],
                        [
                            'user_id' => $lockedOrder->user_id,
                            'hosting_server_id' => $item->hosting_server_id ?: $lockedOrder->hosting_server_id,
                            'hosting_package_id' => $item->hosting_package_id,
                            'service_order_id' => $lockedOrder->id,
                            'domain_name' => strtolower($item->domain_name),
                            'status' => 'pending_provisioning',
                            'provisioning_mode' => 'automatic',
                            'provider_metadata' => ['note' => 'Payment verified. Hosting account is queued for Verpex WHM provisioning.'],
                        ]
                    );
                    if ($hostingAccount->status !== 'active') {
                        $hostingAccountIds[] = $hostingAccount->id;
                    }
                }

                $type = match ($item->item_type) {
                    'domain_registration', 'domain_renewal' => 'domain',
                    'hosting' => 'hosting',
                    'email_marketing', 'marketing_package' => 'email_marketing',
                    'email_delivery', 'smtp_credits' => 'email_delivery',
                    default => $item->item_type,
                };
                $name = match ($type) {
                    'domain' => $item->domain_name ?: 'Domain service',
                    'hosting' => $item->hostingPackage?->name ?: data_get($item->item_metadata, 'package_name', 'Web hosting'),
                    'email_marketing' => data_get($item->item_metadata, 'package_name', 'Email marketing package'),
                    'email_delivery' => data_get($item->item_metadata, 'package_name', 'Email delivery credits'),
                    default => data_get($item->item_metadata, 'name', str($item->item_type)->replace('_', ' ')->title()->toString()),
                };
                $status = match ($type) {
                    'domain' => 'pending_registration',
                    'hosting' => 'pending_provisioning',
                    default => 'pending_fulfillment',
                };
                UserService::updateOrCreate(
                    ['service_order_item_id' => $item->id],
                    [
                        'user_id' => $lockedOrder->user_id,
                        'service_order_id' => $lockedOrder->id,
                        'service_type' => $type,
                        'name' => $name,
                        'identifier' => $item->domain_name,
                        'provider' => $item->provider,
                        'status' => $status,
                        'metadata' => [
                            'item_type' => $item->item_type,
                            'provider_product_id' => $item->provider_product_id,
                            'billing_period' => $item->billing_period,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'line_total' => $item->line_total,
                            'currency' => $item->currency,
                            'order_snapshot' => $item->item_metadata,
                        ],
                    ]
                );

                $itemStatus = match ($item->item_type) {
                    'domain_registration', 'domain_renewal' => 'pending_registration',
                    'hosting' => 'provisioning',
                    default => 'pending_fulfillment',
                };
                $item->setFulfillmentStatus($itemStatus);
            }

            // Backward compatibility for older hosting orders that predate order items.
            if ($orderItems->isEmpty() && $lockedOrder->hosting_package_id && $lockedOrder->domain_name) {
                $hostingAccount = HostingAccount::firstOrCreate(['service_order_id' => $lockedOrder->id], [
                    'user_id' => $lockedOrder->user_id,
                    'hosting_server_id' => $lockedOrder->hosting_server_id,
                    'hosting_package_id' => $lockedOrder->hosting_package_id,
                    'domain_name' => strtolower($lockedOrder->domain_name),
                    'status' => 'pending_provisioning',
                    'provisioning_mode' => 'automatic',
                    'provider_metadata' => ['note' => 'Payment verified. Hosting account is queued for Verpex WHM provisioning.'],
                ]);
                if ($hostingAccount->status !== 'active') {
                    $hostingAccountIds[] = $hostingAccount->id;
                }
            }

            $cartId = data_get($lockedOrder->metadata, 'cart_id');
            if ($cartId) {
                $cart = Cart::query()->whereKey($cartId)->lockForUpdate()->first();
                if ($cart && $cart->service_order_id === $lockedOrder->id && $cart->status === 'checkout_pending') {
                    $cart->update(['status' => 'converted']);
                }
            }

            return $lockedOrder->fresh(['user', 'hostingPackage', 'hostingAccount', 'items']);
        });

        if ($newlyPaid) {
            foreach (array_unique($hostingAccountIds) as $hostingAccountId) {
                try {
                    ProvisionHostingAccount::dispatch($hostingAccountId)->afterCommit();
                } catch (\Throwable $exception) {
                    // Payment remains confirmed even if the queue/provider is temporarily unavailable.
                    report($exception);
                    $failedAccount = HostingAccount::find($hostingAccountId);
                    if ($failedAccount) {
                        $metadata = $failedAccount->provider_metadata ?? [];
                        $metadata['dispatch_error'] = 'Provisioning job could not be queued.';
                        $failedAccount->update(['status' => 'provisioning_failed', 'provider_metadata' => $metadata]);
                        $serviceQuery = UserService::query()
                            ->where('user_id', $failedAccount->user_id)
                            ->where('service_order_id', $failedAccount->service_order_id)
                            ->where('service_type', 'hosting');
                        if ($failedAccount->service_order_item_id) {
                            $serviceQuery->where('service_order_item_id', $failedAccount->service_order_item_id);
                        } else {
                            $serviceQuery->where('identifier', $failedAccount->domain_name);
                        }
                        $serviceQuery->update(['status' => 'provisioning_failed', 'updated_at' => now()]);
                        if ($failedAccount->service_order_item_id) {
                            ServiceOrderItem::find($failedAccount->service_order_item_id)
                                ?->setFulfillmentStatus('provisioning_failed');
                        }
                        $attempt = PaymentTransaction::where('reference', $reference)->first();
                        if ($attempt) {
                            $metadata = $attempt->metadata ?? [];
                            $metadata['fulfillment_dispatch_error'] = 'Provisioning job could not be queued.';
                            $attempt->update(['metadata' => $metadata]);
                        }
                    }
                }
            }
            try {
                $items = $paidOrder->items;
                $lines = $items->map(fn($item) => strtoupper(str_replace('_', ' ', $item->item_type)) . ': ' . ($item->domain_name ?: $item->hostingPackage?->name ?: 'Service') . ' — ' . strtoupper($item->currency) . ' ' . number_format((float) $item->line_total, 2))->implode("\n");
                Mail::raw("Hello {$paidOrder->user->name},\n\nWe have confirmed payment for your Drypp order {$paidOrder->order_number}.\n\n{$lines}\n\nTotal paid: {$paidOrder->formattedAmount()}\n\nYour services are now listed in My Services and queued for fulfilment. We will notify you when activation is complete.\n\nThank you,\nDrypp", function ($message) use ($paidOrder) {
                    $message->to($paidOrder->user->email)->subject('Payment confirmed — Drypp order ' . $paidOrder->order_number);
                });
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        return $paidOrder;
    }

    public function amountInMinorUnits(ServiceOrder $order): int
    {
        $majorAmount = (float) ($order->amount ?? 0);
        if ($majorAmount <= 0) $majorAmount = (float) ($order->amount_ngn ?? 0);
        return (int) round($majorAmount * 100);
    }

    private function recordInitializationFailure(PaymentTransaction $attempt, string $status, string $reason): void
    {
        $metadata = $attempt->metadata ?? [];
        $metadata['initialization_error'] = $reason;
        $attempt->update(['status' => $status, 'metadata' => $metadata]);
    }

    private function customerSafeFailure(string $diagnostic, string $customerMessage, array $context = []): RuntimeException
    {
        Log::error($diagnostic, $context);

        return new RuntimeException($customerMessage);
    }

    private function safeProviderMetadata(array $data): array
    {
        return array_filter([
            'reference' => data_get($data, 'reference'),
            'status' => data_get($data, 'status'),
            'amount' => data_get($data, 'amount'),
            'currency' => data_get($data, 'currency'),
            'customer_email' => data_get($data, 'customer.email'),
            'gateway_response' => data_get($data, 'gateway_response'),
            'paid_at' => data_get($data, 'paid_at'),
            'channel' => data_get($data, 'channel'),
        ], fn ($value) => $value !== null);
    }
}
