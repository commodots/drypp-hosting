<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hosting_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_ngn');
            $table->string('billing_cycle')->default('yearly');
            $table->unsignedInteger('disk_space_gb')->default(10);
            $table->unsignedInteger('bandwidth_gb')->default(100);
            $table->unsignedInteger('email_accounts')->default(10);
            $table->unsignedInteger('addon_domains')->default(1);
            $table->unsignedInteger('databases')->default(5);
            $table->unsignedInteger('max_accounts_per_server')->default(100);
            $table->json('features')->nullable();
            $table->boolean('free_website_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hosting_packages'); }
};
