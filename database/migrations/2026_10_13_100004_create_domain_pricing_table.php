<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domain_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 30)->unique();
            $table->unsignedInteger('registration_cost_ngn')->default(0);
            $table->unsignedInteger('renewal_cost_ngn')->default(0);
            $table->unsignedInteger('markup_ngn')->default(0);
            $table->unsignedTinyInteger('markup_percent')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('domain_pricing'); }
};
