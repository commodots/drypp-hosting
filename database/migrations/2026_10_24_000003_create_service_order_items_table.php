<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->string('item_type'); // domain_registration, domain_renewal, hosting
            $table->string('domain_name')->nullable();
            $table->foreignId('hosting_package_id')->nullable()
                ->constrained('hosting_packages')->nullOnDelete();
            $table->unsignedBigInteger('parent_item_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_product_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('billing_period')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('item_metadata')->nullable(); // immutable order-time snapshot
            $table->timestamps();
            $table->index(['service_order_id', 'item_type']);
            $table->index('domain_name');
        });

        Schema::table('service_order_items', function (Blueprint $table) {
            $table->foreign('parent_item_id')->references('id')->on('service_order_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
