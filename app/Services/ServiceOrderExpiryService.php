<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

class ServiceOrderExpiryService
{
    public function expireIfDue(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId): bool {
            $order = ServiceOrder::query()->whereKey($orderId)->lockForUpdate()->first();
            if (! $order || $order->status !== 'awaiting_payment'
                || ! $order->expires_at || $order->expires_at->isFuture()) {
                return false;
            }

            $this->releaseOrder($order, 'cancelled', 'payment_expired');

            return true;
        });
    }

    public function markPaymentFailed(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId): bool {
            $order = ServiceOrder::query()->whereKey($orderId)->lockForUpdate()->first();
            if (! $order || $order->status !== 'awaiting_payment') {
                return false;
            }

            $this->releaseOrder($order, 'payment_failed', 'provider_payment_failed');

            return true;
        });
    }

    private function releaseOrder(ServiceOrder $order, string $status, string $reason): void
    {
        $metadata = $order->metadata ?? [];
        $metadata['cancellation_reason'] = $reason;
        $metadata['cancelled_at'] = now()->toIso8601String();
        $order->update([
            'status' => $status,
            'metadata' => $metadata,
        ]);

        $order->items()->update([
            'fulfillment_status' => 'cancelled',
            'domain_reservation_key' => null,
        ]);

        Cart::query()->where('service_order_id', $order->id)
            ->where('status', 'checkout_pending')
            ->update([
                'status' => 'active',
                'service_order_id' => null,
            ]);
    }
}
