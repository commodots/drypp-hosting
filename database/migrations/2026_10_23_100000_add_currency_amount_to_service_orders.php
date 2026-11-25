<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('service_orders', 'currency')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->char('currency', 3)->default('NGN')->after('amount_ngn');
            });
        }

        if (!Schema::hasColumn('service_orders', 'amount')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->decimal('amount', 12, 2)->default(0)->after('currency');
            });
        }

        // Preserve the legacy NGN amount for existing orders; amount is the normalized major-unit value.
        DB::table('service_orders')->where('currency', 'NGN')->where('amount', 0)
            ->update(['amount' => DB::raw('amount_ngn')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_orders', 'amount')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
        if (Schema::hasColumn('service_orders', 'currency')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
