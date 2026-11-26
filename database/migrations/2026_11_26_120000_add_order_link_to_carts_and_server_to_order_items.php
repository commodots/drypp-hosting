<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('service_order_id')->nullable()->unique()
                ->constrained('service_orders')->nullOnDelete();
        });

        Schema::table('service_order_items', function (Blueprint $table) {
            $table->foreignId('hosting_server_id')->nullable()
                ->constrained('hosting_servers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hosting_server_id');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_order_id');
        });
    }
};
