<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // domain_registration, domain_renewal, hosting
            $table->string('domain_name')->nullable();
            $table->foreignId('hosting_package_id')->nullable()
                ->constrained('hosting_packages')->nullOnDelete();
            // Used to associate a hosting item with its domain item in the same cart.
            $table->unsignedBigInteger('parent_item_id')->nullable();
            $table->string('provider')->nullable(); // e.g. porkbun, verpex
            $table->string('provider_product_id')->nullable(); // TLD/plan mapping, not secret
            $table->unsignedInteger('quantity')->default(1);
            $table->string('billing_period')->nullable(); // yearly, monthly, etc.
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('price_metadata')->nullable(); // registrar quote / markup breakdown snapshot
            $table->timestamps();
            $table->index(['cart_id', 'item_type']);
            $table->index('domain_name');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreign('parent_item_id')->references('id')->on('cart_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
