<?php

namespace App\Services\Payments;

use App\Models\HostingAccount;
use App\Models\ServiceOrder;
use App\Models\UserService;
use App\Jobs\ProvisionHostingAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class PaystackPaymentService
{
    public function verifyAndMarkPaid(string $reference): ServiceOrder
    {
        $secret = config('services.paystack.secret_key');
        if (!$secret) throw new RuntimeException('Paystack is not configured.');
        $order = ServiceOrder::with('user')->where('payment_reference', $reference)->firstOrFail();
        $response = Http::withToken($secret)->acceptJson()->timeout(20)->retry(2, 250)->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));
        $data = $response->json('data');
        $expectedAmount = $this->amountInMinorUnits($order);
        $expectedCurrency = strtoupper($order->currency ?: 'NGN');
        $allowedCurrencies = array_map('strtoupper', config('services.paystack.currencies', ['NGN', 'USD']));
        $valid = $response->successful() && $response->json('status') === true
            && data_get($data, 'status') === 'success'
            && (int) data_get($data, 'amount', -1) === $expectedAmount
            && strtoupper((string) data_get($data, 'currency')) === $expectedCurrency
            && in_array($expectedCurrency, $allowedCurrencies, true)
            && strtolower((string) data_get($data, 'customer.email')) === strtolower((string) $order->user->email)
            && (string) data_get($data, 'reference') === $reference;
        if (!$valid) throw new RuntimeException('Paystack transaction could not be verified against the order amount, currency, customer and reference.');

        $newlyPaid = false;
        $hostingAccountIds = [];
        $paidOrder = DB::transaction(function () use ($order, &$newlyPaid, &$hostingAccountIds) {
            $lockedOrder = ServiceOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status === 'paid') return $lockedOrder->fresh(['user', 'hostingPackage', 'hostingAccount', 'items']);
            if ($lockedOrder->status !== 'awaiting_payment') throw new RuntimeException('This order is not awaiting payment.');
            $lockedOrder->update(['status' => 'paid', 'paid_at' => now()]);
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
                            'hosting_server_id' => $lockedOrder->hosting_server_id,
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
}
