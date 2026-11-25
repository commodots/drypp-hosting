<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryCurrencySetting;
use App\Models\CurrencyDisplaySetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryCurrencyController extends Controller
{
    public function index()
    {
        $countries = CountryCurrencySetting::orderBy('country_name')->get();
        $display = CurrencyDisplaySetting::current();

        return view('admin.currencies.index', compact('countries', 'display'));
    }

    public function saveCountry(Request $request)
    {
        $data = $request->validate([
            'country_code' => ['required','string','size:2','alpha', Rule::unique('country_currency_settings','country_code')],
            'country_name' => ['required','string','max:100'],
            'currency_code' => ['required','string','size:3','alpha'],
            'currency_symbol' => ['required','string','max:8'],
        ]);
        $data['country_code'] = strtoupper($data['country_code']);
        $data['currency_code'] = strtoupper($data['currency_code']);
        $data['is_active'] = $request->boolean('is_active');
        CountryCurrencySetting::create($data);

        return back()->with('status', 'Country currency added.');
    }

    public function updateCountry(Request $request, CountryCurrencySetting $countryCurrencySetting)
    {
        $data = $request->validate([
            'country_name' => ['required','string','max:100'],
            'currency_code' => ['required','string','size:3','alpha'],
            'currency_symbol' => ['required','string','max:8'],
        ]);
        $data['currency_code'] = strtoupper($data['currency_code']);
        $data['is_active'] = $request->boolean('is_active');
        $countryCurrencySetting->update($data);

        return back()->with('status', 'Country currency updated.');
    }

    public function saveDisplay(Request $request)
    {
        $data = $request->validate([
            'ngn_per_usd' => ['required','numeric','gt:0','max:10000000'],
        ]);
        $settings = CurrencyDisplaySetting::current();
        $settings->update([
            'development_usd_override' => $request->boolean('development_usd_override'),
            'ngn_per_usd' => $data['ngn_per_usd'],
        ]);

        return back()->with('status', 'Currency display settings saved.');
    }
}
