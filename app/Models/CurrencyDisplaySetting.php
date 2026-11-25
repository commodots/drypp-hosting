<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyDisplaySetting extends Model
{
    protected $fillable = ['development_usd_override','ngn_per_usd'];

    protected function casts(): array
    {
        return ['development_usd_override' => 'boolean', 'ngn_per_usd' => 'decimal:4'];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'development_usd_override' => app()->environment('local', 'development'),
            'ngn_per_usd' => 1500,
        ]);
    }
}
