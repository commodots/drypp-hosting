<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hosting_server_id')->nullable()->constrained('hosting_servers')->nullOnDelete();
            $table->foreignId('hosting_package_id')->nullable()->constrained('hosting_packages')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('service_type')->default('hosting');
            $table->string('domain_name')->nullable();
            $table->unsignedInteger('amount_ngn');
            $table->string('status')->default('awaiting_payment');
            $table->string('payment_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('service_orders'); }
};
