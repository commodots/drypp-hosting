<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryCurrencySetting extends Model
{
    protected $fillable = ['country_code','country_name','currency_code','currency_symbol','is_active'];
}
