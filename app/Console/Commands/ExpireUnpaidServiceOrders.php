<?php

namespace App\Console\Commands;

use App\Models\ServiceOrder;
use App\Services\ServiceOrderExpiryService;
use Illuminate\Console\Command;

class ExpireUnpaidServiceOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Cancel expired unpaid service orders and release their reservations.';

    public function handle(ServiceOrderExpiryService $expiry): int
    {
        $expiredCount = 0;

        ServiceOrder::query()
            ->where('status', 'awaiting_payment')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($orders) use ($expiry, &$expiredCount): void {
                foreach ($orders as $order) {
                    if ($expiry->expireIfDue((int) $order->id)) {
                        $expiredCount++;
                    }
                }
            });

        $this->info("Expired {$expiredCount} unpaid service order(s).");

        return self::SUCCESS;
    }
}
