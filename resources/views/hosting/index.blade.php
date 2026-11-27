@extends('layouts.app')
@section('title', 'Marketplace / Add Service')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-900 p-6 text-white shadow md:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-blue-200">Customer marketplace</p>
                    <h1 class="mt-2 text-2xl font-bold md:text-3xl">Marketplace / Add Service</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Add domains and hosting packages to one cart. Prices and domain availability are checked again before checkout.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('cart.index') }}"
                        class="rounded-lg bg-white px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-blue-50">
                        Cart <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-blue-800">{{ $cartCount }}</span>
                    </a>
                    <a href="{{ route('domains.search') }}"
                        class="rounded-lg border border-white/30 px-4 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        Search domains
                    </a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Hosting accounts</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $accounts->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Purchased hosting services</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Recent orders</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $orders->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Latest orders on your account</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Available packages</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $packages->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Hosting plans ready to add</p>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">Your hosting accounts</h2>
                <p class="mt-1 text-sm text-slate-500">Check the package, server allocation, and provisioning status for your services.</p>
            </div>
            @if ($accounts->isEmpty())
                <div class="rounded-lg bg-slate-50 p-5 text-sm text-slate-500">
                    No hosting accounts yet. Choose a package below to get started.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Domain</th>
                                <th class="px-4 py-3 font-semibold">Package</th>
                                <th class="px-4 py-3 font-semibold">Server</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($accounts as $account)
                                @php
                                    $accountState = strtolower($account->status);
                                    $accountStatusClass = $accountState === 'active'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : (in_array($accountState, ['provisioning_failed', 'suspended'], true)
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-amber-100 text-amber-800');
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $account->domain_name }}</td>
                                    <td class="px-4 py-3">{{ $account->package?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $account->server?->name ?? 'Awaiting allocation' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $accountStatusClass }}">
                                            {{ str_replace('_', ' ', ucfirst($account->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent orders</h2>
                    <p class="mt-1 text-sm text-slate-500">Review payment and fulfilment progress for your latest orders.</p>
                </div>
                <a href="{{ route('billing.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Billing &amp; orders</a>
            </div>
            @if ($orders->isEmpty())
                <div class="rounded-lg bg-slate-50 p-5 text-sm text-slate-500">Your orders will appear here.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Order</th>
                                <th class="px-4 py-3 font-semibold">Service</th>
                                <th class="px-4 py-3 font-semibold">Amount</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold"><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($orders as $order)
                                @php
                                    $orderState = strtolower($order->status);
                                    $orderStatusClass = $orderState === 'paid'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : (in_array($orderState, ['failed', 'cancelled'], true)
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-amber-100 text-amber-800');
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $order->order_number }}</td>
                                    <td class="px-4 py-3">{{ $order->domain_name ?? 'Multiple services' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $order->formattedAmount() }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $orderStatusClass }}">
                                            {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a class="font-semibold text-blue-700 hover:text-blue-900" href="{{ route('checkout.show', $order) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section id="plans" class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Hosting packages</h2>
                    <p class="mt-1 text-sm text-slate-500">Prices are shown in USD. Package billing period is shown on each plan.</p>
                </div>
                <a href="{{ route('cart.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Review cart →</a>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($packages as $package)
                    <article class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Web hosting</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $package->name }}</h3>
                        <p class="mt-1 min-h-10 text-sm leading-5 text-slate-500">{{ $package->description }}</p>
                        <p class="mt-4 text-2xl font-bold text-slate-900">
                            ${{ number_format((float) $package->price_usd, 2) }}
                            <span class="text-sm font-medium text-slate-500">/ {{ $package->billing_cycle }}</span>
                        </p>
                        <ul class="mt-4 flex-1 space-y-2 border-t border-slate-100 pt-4 text-sm text-slate-600">
                            <li>{{ $package->disk_space_gb }} GB storage</li>
                            <li>{{ $package->bandwidth_gb }} GB bandwidth</li>
                            <li>{{ $package->email_accounts }} email accounts</li>
                            <li>{{ $package->addon_domains }} addon domains</li>
                            <li>{{ $package->databases }} databases</li>
                            @if ($package->free_website_enabled)
                                <li>Free standard website</li>
                            @endif
                        </ul>
                        <form method="POST" action="{{ route('hosting.order', $package) }}" class="mt-5 space-y-3">
                            @csrf
                            <div>
                                <label for="domain-{{ $package->id }}" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Domain name (existing domain)
                                </label>
                                <input id="domain-{{ $package->id }}" name="domain_name" type="text"
                                    placeholder="example.com" value="{{ old('domain_name') }}" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <button type="submit"
                                class="w-full rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add hosting to cart
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">
                        No active hosting packages are configured.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
