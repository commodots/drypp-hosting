@extends('layouts.app')
@section('title', 'My Services')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-blue-700">DRYPP CUSTOMER PORTAL</p>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mt-1">My Services</h1>
            <p class="text-sm text-slate-500 mt-1">View the services purchased through your Drypp account and their fulfilment status.</p>
        </div>
        <a href="{{ route('hosting.index') }}" class="inline-flex justify-center rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800">Browse services</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach(['domain' => 'Domains', 'hosting' => 'Hosting', 'email_marketing' => 'Email marketing', 'email_delivery' => 'Email delivery'] as $type => $label)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ (int) ($counts[$type] ?? 0) }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Service inventory</h2>
        </div>
        @if($services->count())
            <div class="divide-y divide-slate-100">
                @foreach($services as $service)
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900">{{ $service->name }}</p>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ str($service->service_type)->replace('_', ' ')->title() }}
                                @if($service->identifier) · {{ $service->identifier }} @endif
                                @if($service->provider) · Provider: {{ strtoupper($service->provider) }} @endif
                            </p>
                            @if($service->order)
                                <p class="text-xs text-slate-400 mt-1">Order {{ $service->order->order_number }}</p>
                            @endif
                            @if($service->expires_at)
                                <p class="text-xs text-slate-500 mt-1">Renewal / expiry: {{ $service->expires_at->format('d M Y') }}</p>
                            @endif
                        </div>
                        @php
                            $statusClasses = match($service->status) {
                                'active' => 'bg-green-100 text-green-800',
                                'suspended', 'failed', 'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-amber-100 text-amber-800',
                            };
                        @endphp
                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $service->status_label }}</span>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $services->links() }}</div>
        @else
            <div class="px-5 py-12 text-center">
                <p class="font-semibold text-slate-800">Your service inventory is empty</p>
                <p class="text-sm text-slate-500 mt-1">Services will appear here after an order is paid and recorded for fulfilment.</p>
                <a href="{{ route('hosting.index') }}" class="inline-flex mt-4 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white">Explore hosting and domains</a>
            </div>
        @endif
    </div>
</div>
@endsection
