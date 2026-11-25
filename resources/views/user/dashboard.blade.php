@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <section class="rounded-2xl bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-900 text-white p-6 md:p-8 shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
      <div><p class="text-blue-200 text-sm font-medium">DRYPP CUSTOMER PORTAL</p>
        <h2 class="text-2xl md:text-3xl font-bold mt-2">Welcome, {{ $user->name ?? 'there' }}.</h2>
        <p class="text-slate-300 mt-2 max-w-xl">Your email marketing, hosting, domains and other digital services in one place.</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="{{ route('my-services.index') }}" class="rounded-lg bg-white text-slate-900 px-4 py-3 font-semibold hover:bg-blue-50">My Services</a>
        <a href="{{ route('hosting.index') }}" class="rounded-lg border border-white/30 px-4 py-3 font-semibold hover:bg-white/10">Browse Services</a>
      </div>
    </div>
  </section>

  @if ($errors->any())
    <div class="rounded-lg bg-red-50 text-red-700 p-4">{{ $errors->first() }}</div>
  @endif

  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Hosting services</div><div class="text-3xl font-bold text-slate-900 mt-2">{{ $hostingCount ?? 0 }}</div><div class="text-xs text-slate-500 mt-1">Purchased hosting services</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Domains</div><div class="text-3xl font-bold text-slate-900 mt-2">{{ $domainCount ?? 0 }}</div><div class="text-xs text-slate-500 mt-1">Purchased domain services</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Orders awaiting payment</div><div class="text-3xl font-bold text-slate-900 mt-2">{{ $pendingOrders ?? 0 }}</div><div class="text-xs text-slate-500 mt-1">Orders to complete</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-sm text-slate-500">Email marketing plan</div><div class="text-xl font-bold text-slate-900 mt-2">{{ $activePackage ?? 'No Plan' }}</div><div class="text-xs text-slate-500 mt-1">{{ number_format((int) ($emailsRemaining ?? 0)) }} emails remaining</div></div>
  </section>

  <section class="rounded-xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
      <div><h3 class="font-bold text-slate-900 text-lg">My running services</h3><p class="text-sm text-slate-500 mt-1">Track activation and fulfilment for the services you have purchased.</p></div>
      <a href="{{ route('my-services.index') }}" class="text-sm font-semibold text-blue-700">View all →</a>
    </div>
    @if(isset($activeServices) && $activeServices->isNotEmpty())
      <div class="mt-4 divide-y divide-slate-100">
        @foreach($activeServices as $service)
          <div class="flex items-center justify-between gap-3 py-3">
            <div><p class="font-semibold text-slate-900">{{ $service->name }}</p><p class="text-xs text-slate-500 mt-1">{{ str($service->service_type)->replace('_', ' ')->title() }} @if($service->identifier) · {{ $service->identifier }} @endif</p></div>
            <span class="rounded-full bg-amber-100 text-amber-800 px-3 py-1 text-xs font-semibold">{{ $service->status_label }}</span>
          </div>
        @endforeach
      </div>
    @else
      <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">Your purchased services will appear here after payment is verified.</p>
    @endif
  </section>

  <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <a href="{{ route('campaigns.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-indigo-300 transition">
      <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xl">✉</div>
      <h3 class="font-bold text-lg mt-4 text-slate-900">Email Marketing</h3><p class="text-sm text-slate-500 mt-1">Create campaigns, manage contacts and automate follow-ups.</p><span class="inline-block mt-4 text-sm font-semibold text-indigo-700">Open campaigns →</span>
    </a>
    <a href="{{ route('hosting.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
      <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl">▣</div>
      <h3 class="font-bold text-lg mt-4 text-slate-900">Domains & Hosting</h3><p class="text-sm text-slate-500 mt-1">Find a domain and choose a hosting package.</p><span class="inline-block mt-4 text-sm font-semibold text-blue-700">Browse services →</span>
    </a>
    <a href="{{ route('billing.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-teal-300 transition">
      <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-xl">▤</div>
      <h3 class="font-bold text-lg mt-4 text-slate-900">Billing & Orders</h3><p class="text-sm text-slate-500 mt-1">Review payments and your order history.</p><span class="inline-block mt-4 text-sm font-semibold text-teal-700">View billing →</span>
    </a>
  </section>
</div>
@endsection
