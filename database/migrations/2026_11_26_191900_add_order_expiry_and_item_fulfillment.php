<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_orders', 'expires_at')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->index();
            });
        }

        if (! Schema::hasColumn('service_order_items', 'fulfillment_status')) {
            Schema::table('service_order_items', function (Blueprint $table) {
                $table->string('fulfillment_status', 40)->default('awaiting_payment');
            });
        }

        if (! Schema::hasColumn('service_order_items', 'domain_reservation_key')) {
            Schema::table('service_order_items', function (Blueprint $table) {
                $table->char('domain_reservation_key', 64)->nullable();
                $table->unique('domain_reservation_key', 'service_order_items_domain_reservation_unique');
            });
        }

        $expiryMinutes = max(1, (int) config('services.paystack.order_expiry_minutes', 30));
        DB::table('service_orders')
            ->where('status', 'awaiting_payment')
            ->whereNull('expires_at')
            ->orderBy('id')
            ->select(['id', 'created_at'])
            ->chunkById(500, function ($orders) use ($expiryMinutes): void {
                foreach ($orders as $order) {
                    DB::table('service_orders')->where('id', $order->id)->update([
                        'expires_at' => Carbon::parse($order->created_at)->addMinutes($expiryMinutes),
                    ]);
                }
            });

        $reservedDomains = [];
        DB::table('service_order_items as items')
            ->join('service_orders as orders', 'orders.id', '=', 'items.service_order_id')
            ->where('items.item_type', 'domain_registration')
            ->whereNotNull('items.domain_name')
            ->whereNotIn('orders.status', ['payment_failed', 'cancelled', 'refunded'])
            ->orderBy('items.id')
            ->select(['items.id as id', 'items.domain_name'])
            ->chunkById(500, function ($items) use (&$reservedDomains): void {
                foreach ($items as $item) {
                    $key = hash('sha256', strtolower(trim((string) $item->domain_name)));
                    if (isset($reservedDomains[$key])) {
                        continue;
                    }

                    DB::table('service_order_items')->where('id', $item->id)
                        ->update(['domain_reservation_key' => $key]);
                    $reservedDomains[$key] = true;
                }
            }, 'items.id', 'id');

        if (Schema::hasTable('user_services')) {
            DB::table('service_order_items')
                ->whereIn('service_order_id', function ($query) {
                    $query->select('id')->from('service_orders')
                        ->whereIn('status', ['paid', 'processing', 'partially_fulfilled', 'fulfilled']);
                })
                ->orderBy('id')
                ->chunkById(500, function ($items): void {
                    foreach ($items as $item) {
                        $serviceStatus = DB::table('user_services')
                            ->where('service_order_item_id', $item->id)
                            ->value('status');
                        $status = match ($serviceStatus) {
                            'active' => 'fulfilled',
                            'pending_registration' => 'pending_registration',
                            'pending_provisioning' => 'provisioning',
                            'provisioning_failed' => 'provisioning_failed',
                            default => match ($item->item_type) {
                                'domain_registration', 'domain_renewal' => 'pending_registration',
                                'hosting' => 'provisioning',
                                default => 'pending_fulfillment',
                            },
                        };

                        DB::table('service_order_items')
                            ->where('id', $item->id)
                            ->update(['fulfillment_status' => $status]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_order_items', 'domain_reservation_key')) {
            Schema::table('service_order_items', function (Blueprint $table) {
                $table->dropUnique('service_order_items_domain_reservation_unique');
                $table->dropColumn('domain_reservation_key');
            });
        }

        if (Schema::hasColumn('service_order_items', 'fulfillment_status')) {
            Schema::table('service_order_items', function (Blueprint $table) {
                $table->dropColumn('fulfillment_status');
            });
        }

        if (Schema::hasColumn('service_orders', 'expires_at')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->dropIndex(['expires_at']);
                $table->dropColumn('expires_at');
            });
        }
    }
};
