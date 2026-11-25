<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hosting_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->default('verpex');
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('max_accounts')->default(100);
            $table->unsignedInteger('disk_limit_gb')->default(500);
            $table->unsignedInteger('bandwidth_limit_gb')->default(5000);
            $table->json('package_capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hosting_servers'); }
};
