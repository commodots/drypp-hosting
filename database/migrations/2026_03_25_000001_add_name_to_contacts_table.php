<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contacts', 'name')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('name')->nullable()->after('email');
            });
        }

        if (! Schema::hasIndex('contacts', 'contacts_user_id_email_unique')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->unique(
                    ['user_id', 'email'],
                    'contacts_user_id_email_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('contacts', 'contacts_user_id_email_unique')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropUnique('contacts_user_id_email_unique');
            });
        }

        if (Schema::hasColumn('contacts', 'name')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
