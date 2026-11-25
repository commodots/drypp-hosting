<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainMarkupSetting extends Model
{
    protected $fillable = ['default_markup_percent'];

    public static function defaultPercent(): int
    {
        return (int) (static::query()->value('default_markup_percent') ?? 40);
    }
}
