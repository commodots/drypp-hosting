<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hosting_server_id')->nullable()->constrained('hosting_servers')->nullOnDelete();
            $table->foreignId('hosting_package_id')->nullable()->constrained('hosting_packages')->nullOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->string('domain_name');
            $table->string('cpanel_username')->nullable();
            $table->string('external_account_id')->nullable();
            $table->string('status')->default('pending_provisioning');
            $table->string('provisioning_mode')->default('manual');
            $table->timestamp('expires_at')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('hosting_accounts'); }
};
