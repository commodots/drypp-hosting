<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_currency_settings', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2)->unique();
            $table->string('country_name', 100);
            $table->char('currency_code', 3);
            $table->string('currency_symbol', 8);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('country_currency_settings')->insert([
            ['country_code'=>'NG','country_name'=>'Nigeria','currency_code'=>'NGN','currency_symbol'=>'₦','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['country_code'=>'US','country_name'=>'United States','currency_code'=>'USD','currency_symbol'=>'$','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['country_code'=>'GB','country_name'=>'United Kingdom','currency_code'=>'GBP','currency_symbol'=>'£','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['country_code'=>'CA','country_name'=>'Canada','currency_code'=>'CAD','currency_symbol'=>'C$','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
        ]);

        Schema::create('currency_display_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('development_usd_override')->default(true);
            $table->decimal('ngn_per_usd', 12, 4)->default(1500);
            $table->timestamps();
        });

        DB::table('currency_display_settings')->insert([
            'development_usd_override'=>true,
            'ngn_per_usd'=>1500,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_display_settings');
        Schema::dropIfExists('country_currency_settings');
    }
};
