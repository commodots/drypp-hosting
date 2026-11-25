<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domain_markup_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('default_markup_percent')->default(40);
            $table->timestamps();
        });

        DB::table('domain_markup_settings')->insert([
            'default_markup_percent' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_markup_settings');
    }
};
