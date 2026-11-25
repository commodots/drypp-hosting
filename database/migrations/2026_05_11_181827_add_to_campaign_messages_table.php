<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('campaign_messages')
            ->where(function ($query) {
                $query->whereNull('message_uuid')->orWhere('message_uuid', '');
            })
            ->orderBy('id')
            ->chunkById(500, function ($messages): void {
                foreach ($messages as $message) {
                    DB::table('campaign_messages')->where('id', $message->id)
                        ->update(['message_uuid' => (string) Str::uuid()]);
                }
            });

        if (! Schema::hasIndex('campaign_messages', 'campaign_messages_message_uuid_unique')) {
            Schema::table('campaign_messages', function (Blueprint $table) {
                $table->unique('message_uuid', 'campaign_messages_message_uuid_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('campaign_messages', 'campaign_messages_message_uuid_unique')) {
            Schema::table('campaign_messages', function (Blueprint $table) {
                $table->dropUnique('campaign_messages_message_uuid_unique');
            });
        }
    }
};
