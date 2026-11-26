<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('smtp_servers', 'type')) {
            Schema::table('smtp_servers', function (Blueprint $table) {
                $table->string('type')->default('smtp');
            });
        }

        if (!Schema::hasColumn('smtp_servers', 'api_key')) {
            Schema::table('smtp_servers', function (Blueprint $table) {
                $table->text('api_key')->nullable();
            });
        }

        if (!Schema::hasColumn('smtp_servers', 'region')) {
            Schema::table('smtp_servers', function (Blueprint $table) {
                $table->string('region')->nullable();
            });
        }

        if (!Schema::hasColumn('smtp_servers', 'access_key')) {
            Schema::table('smtp_servers', function (Blueprint $table) {
                $table->string('access_key')->nullable();
            });
        }

        if (!Schema::hasColumn('smtp_servers', 'secret_key')) {
            Schema::table('smtp_servers', function (Blueprint $table) {
                $table->text('secret_key')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['type', 'api_key', 'region', 'access_key', 'secret_key'],
            fn (string $column): bool => Schema::hasColumn('smtp_servers', $column)
        ));

        if ($columns !== []) {
            Schema::table('smtp_servers', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
