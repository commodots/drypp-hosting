@extends('layouts.app')

@section('title', 'Domain Markup Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Domain Markup Settings</h1>
        <p class="mt-1 text-sm text-gray-600">
            Set the default percentage added to the registrar’s domain registration cost.
            This default is used when a TLD does not have its own percentage markup configured.
        </p>
    </div>

    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.domain-markup.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="default_markup_percent" class="mb-2 block text-sm font-semibold text-gray-700">
                    Default markup (%)
                </label>
                <div class="flex items-center gap-3">
                    <input
                        id="default_markup_percent"
                        name="default_markup_percent"
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        required
                        value="{{ old('default_markup_percent', $markup->default_markup_percent) }}"
                        class="w-full max-w-xs rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                    <span class="text-gray-600">%</span>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Initial default: 40%. A cost of ₦10,000 with 40% markup is displayed at ₦14,000
                    before any fixed markup or applicable taxes.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">
                    Save markup
                </button>
                <a href="{{ route('admin.hosting.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                    Back to Hosting Admin
                </a>
            </div>
        </form>
    </section>
</div>
@endsection
