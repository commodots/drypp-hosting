<?php

namespace App\Console\Commands;

use App\Services\Payments\PaystackPaymentService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePaystackTransaction extends Command
{
    protected $signature = 'paystack:reconcile {reference : The Paystack transaction reference to verify}';

    protected $description = 'Safely verify and reconcile a Paystack payment by transaction reference.';

    public function handle(PaystackPaymentService $payments): int
    {
        try {
            $order = $payments->verifyAndMarkPaid((string) $this->argument('reference'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Order {$order->order_number} is {$order->status}.");

        return self::SUCCESS;
    }
}
