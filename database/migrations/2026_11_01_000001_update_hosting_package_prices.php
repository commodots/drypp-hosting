<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('hosting_packages')->where('slug', 'standard-hosting')->update([
            'price_usd' => 29.99,
            'free_website_enabled' => false,
            'updated_at' => $now,
        ]);

        DB::table('hosting_packages')->where('slug', 'business-hosting-usd')->update([
            'price_usd' => 49.99,
            'free_website_enabled' => true,
            'updated_at' => $now,
        ]);

        DB::table('hosting_packages')->where('slug', 'professional-hosting')->update([
            'price_usd' => 99.99,
            'free_website_enabled' => true,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('hosting_packages')->where('slug', 'standard-hosting')->update([
            'price_usd' => 30.00,
            'free_website_enabled' => false,
        ]);
        DB::table('hosting_packages')->where('slug', 'business-hosting-usd')->update([
            'price_usd' => 50.00,
            'free_website_enabled' => false,
        ]);
        DB::table('hosting_packages')->where('slug', 'professional-hosting')->update([
            'price_usd' => 100.00,
            'free_website_enabled' => false,
        ]);
    }
};
