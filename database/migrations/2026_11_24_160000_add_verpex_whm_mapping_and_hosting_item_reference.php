<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('hosting_packages', 'whm_package_name')) {
            Schema::table('hosting_packages', function (Blueprint $table) {
                $table->string('whm_package_name')->nullable()->after('slug');
            });
        }

        if (! Schema::hasColumn('hosting_accounts', 'service_order_item_id')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->foreignId('service_order_item_id')->nullable()->unique()
                    ->after('service_order_id')->constrained('service_order_items')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hosting_accounts', 'service_order_item_id')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->dropForeign(['service_order_item_id']);
                $table->dropUnique(['service_order_item_id']);
                $table->dropColumn('service_order_item_id');
            });
        }

        if (Schema::hasColumn('hosting_packages', 'whm_package_name')) {
            Schema::table('hosting_packages', function (Blueprint $table) {
                $table->dropColumn('whm_package_name');
            });
        }
    }
};
