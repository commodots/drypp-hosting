@extends('layouts.app')
@section('title', 'Country Currency Settings')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Country Currency Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Set the normal display currency for each country. Nigeria defaults to NGN. The USD override is intended for development comparison.</p>
    </div>

    @if(session('status')) <div class="rounded-lg bg-green-50 p-3 text-green-800">{{ session('status') }}</div> @endif
    @if($errors->any()) <div class="rounded-lg bg-red-50 p-3 text-red-700">{{ $errors->first() }}</div> @endif

    <section class="rounded-xl border bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Development display and conversion</h3>
        <form method="POST" action="{{ route('admin.currencies.display') }}" class="flex flex-wrap items-end gap-4">
            @csrf @method('PUT')
            <div class="min-w-52">
                <label class="mb-1 block text-sm font-medium">NGN per USD</label>
                <input class="w-full rounded-lg border-gray-300" type="number" name="ngn_per_usd" min="0.0001" step="0.0001" value="{{ old('ngn_per_usd', $display->ngn_per_usd) }}" required>
                <p class="mt-1 text-xs text-gray-500">Set a rate for development comparison. It is not a live FX quote.</p>
            </div>
            <label class="flex items-center gap-2 pb-2">
                <input type="checkbox" name="development_usd_override" value="1" @checked(old('development_usd_override', $display->development_usd_override))>
                <span class="text-sm font-medium">Force USD display during development</span>
            </label>
            <button class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white">Save display settings</button>
        </form>
    </section>

    <section class="rounded-xl border bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Country currencies</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead><tr class="border-b text-gray-500"><th class="p-2">Country</th><th class="p-2">Country code</th><th class="p-2">Currency</th><th class="p-2">Symbol</th><th class="p-2">Active</th><th class="p-2">Save</th></tr></thead>
                <tbody>
                @foreach($countries as $country)
                    <tr class="border-b">
                        <form method="POST" action="{{ route('admin.currencies.country.update', $country) }}">
                            @csrf @method('PUT')
                            <td class="p-2"><input class="w-full rounded border-gray-300" name="country_name" value="{{ $country->country_name }}" required></td>
                            <td class="p-2 font-mono">{{ $country->country_code }}</td>
                            <td class="p-2"><input class="w-20 rounded border-gray-300 uppercase" name="currency_code" value="{{ $country->currency_code }}" required></td>
                            <td class="p-2"><input class="w-20 rounded border-gray-300" name="currency_symbol" value="{{ $country->currency_symbol }}" required></td>
                            <td class="p-2"><input type="checkbox" name="is_active" value="1" @checked($country->is_active)></td>
                            <td class="p-2"><button class="rounded bg-gray-800 px-3 py-2 text-white">Save</button></td>
                        </form>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold">Add country</h3>
        <form method="POST" action="{{ route('admin.currencies.country.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            @csrf
            <input class="rounded-lg border-gray-300" name="country_code" placeholder="ISO code (NG)" maxlength="2" required>
            <input class="rounded-lg border-gray-300" name="country_name" placeholder="Country name" required>
            <input class="rounded-lg border-gray-300" name="currency_code" placeholder="Currency (NGN)" maxlength="3" required>
            <input class="rounded-lg border-gray-300" name="currency_symbol" placeholder="Symbol (₦)" maxlength="8" required>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <div class="md:col-span-5"><button class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white">Add country currency</button></div>
        </form>
    </section>
</div>
@endsection
