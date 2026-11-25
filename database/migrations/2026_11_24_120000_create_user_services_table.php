<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('service_order_item_id')->nullable()->unique()->constrained('service_order_items')->nullOnDelete();
            $table->string('service_type', 60)->index();
            $table->string('name');
            $table->string('identifier')->nullable()->index();
            $table->string('provider', 80)->nullable();
            $table->string('status', 50)->default('pending_fulfillment')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'service_type']);
        });

        // Backfill services for previously paid orders so existing customers are not left with an empty inventory.
        if (Schema::hasTable('service_order_items') && Schema::hasTable('service_orders')) {
            DB::table('service_order_items as items')
                ->join('service_orders as orders', 'orders.id', '=', 'items.service_order_id')
                ->leftJoin('hosting_packages as packages', 'packages.id', '=', 'items.hosting_package_id')
                ->where('orders.status', 'paid')
                ->select([
                    'items.id as service_item_id', 'items.service_order_id', 'items.item_type',
                    'items.domain_name', 'items.provider', 'items.item_metadata',
                    'orders.user_id', 'packages.name as hosting_package_name',
                ])
                ->orderBy('items.id')
                ->chunkById(500, function ($items): void {
                    foreach ($items as $item) {
                        $metadata = is_array($item->item_metadata)
                            ? $item->item_metadata
                            : (json_decode((string) $item->item_metadata, true) ?: []);
                        $type = match ($item->item_type) {
                            'domain_registration', 'domain_renewal' => 'domain',
                            'hosting' => 'hosting',
                            'email_marketing', 'marketing_package' => 'email_marketing',
                            'email_delivery', 'smtp_credits' => 'email_delivery',
                            default => $item->item_type,
                        };
                        $name = match ($type) {
                            'domain' => $item->domain_name ?: 'Domain service',
                            'hosting' => $item->hosting_package_name ?: ($metadata['package_name'] ?? 'Web hosting'),
                            'email_marketing' => $metadata['package_name'] ?? 'Email marketing package',
                            'email_delivery' => $metadata['package_name'] ?? 'Email delivery credits',
                            default => $metadata['name'] ?? str_replace('_', ' ', ucfirst($item->item_type)),
                        };
                        $status = match ($type) {
                            'domain' => 'pending_registration',
                            'hosting' => 'pending_provisioning',
                            default => 'pending_fulfillment',
                        };
                        DB::table('user_services')->updateOrInsert(
                            ['service_order_item_id' => $item->service_item_id],
                            [
                                'user_id' => $item->user_id,
                                'service_order_id' => $item->service_order_id,
                                'service_type' => $type,
                                'name' => $name,
                                'identifier' => $item->domain_name,
                                'provider' => $item->provider,
                                'status' => $status,
                                'metadata' => json_encode(['item_type' => $item->item_type, 'order_snapshot' => $metadata]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }, 'items.id', 'service_item_id');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_services');
    }
};
