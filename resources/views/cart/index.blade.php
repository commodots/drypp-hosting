@extends('layouts.app')
@section('title', 'Your Cart')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Marketplace</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 md:text-3xl">Your cart</h1>
                <p class="mt-2 text-sm text-slate-500">Review domains and hosting together before checkout.</p>
            </div>
            <a href="{{ route('domains.search') }}"
                class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Add a domain
            </a>
        </div>

        @if ($cart->items->isEmpty())
            <section class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm md:p-12">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-blue-50 text-2xl text-blue-700" aria-hidden="true">&#x1F6D2;</div>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Your cart is empty</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                    Search for a domain, or explore available services and add what you need to your cart.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('domains.search') }}"
                        class="rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                        Search domains
                    </a>
                    <a href="{{ route('services') }}"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Explore services
                    </a>
                </div>
            </section>
        @else
            <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(300px,0.8fr)]">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Cart items</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $cart->items->count() }} item(s)</p>
                        </div>
                        @if ($cart->status === 'checkout_pending' && $cart->service_order_id)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Checkout pending</span>
                        @endif
                    </div>

                    @if ($cart->status === 'checkout_pending' && $cart->service_order_id)
                        <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900" role="status">
                            This cart is linked to an unpaid order. Resume checkout to pay, or contact support if you need to change it.
                        </div>
                    @endif

                    <div class="space-y-4">
                        @foreach ($cart->items->where('item_type', 'domain_registration') as $item)
                            <article class="rounded-lg border border-slate-200 p-4">
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Domain registration · first year</p>
                                        <h3 class="mt-1 text-base font-bold text-slate-900">{{ $item->domain_name }}</h3>
                                    </div>
                                    <strong class="whitespace-nowrap text-base text-slate-900">
                                        {{ strtoupper($cart->currency) }} {{ number_format((float) $item->line_total, 2) }}
                                    </strong>
                                </div>

                                @if ($cart->status === 'active')
                                    <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
                                        <form method="POST" action="{{ route('cart.hosting.store') }}" class="flex flex-1 flex-wrap items-end gap-2">
                                            @csrf
                                            <input type="hidden" name="domain_item_id" value="{{ $item->id }}">
                                            <div class="min-w-[220px] flex-1">
                                                <label for="package-domain-{{ $item->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Add hosting for this domain</label>
                                                <select id="package-domain-{{ $item->id }}" name="hosting_package_id" required
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                                    <option value="">Select a package</option>
                                                    @foreach ($packages as $package)
                                                        <option value="{{ $package->id }}">{{ $package->name }} — ${{ number_format((float) $package->price_usd, 2) }} / {{ $package->billing_cycle }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit" @disabled($packages->isEmpty())
                                                class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                                                Add hosting
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('cart.items.destroy', $item) }}"
                                            onsubmit="return confirm('Remove this domain and its associated hosting from your cart?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 bg-white px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">
                                                Remove domain
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @endforeach

                        @foreach ($cart->items->where('item_type', 'hosting') as $item)
                            <article class="rounded-lg border border-slate-200 p-4">
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                                            Hosting · {{ $item->billing_period }}{{ $item->parent_item_id ? ' · linked to domain registration' : ' · existing domain' }}
                                        </p>
                                        <h3 class="mt-1 text-base font-bold text-slate-900">{{ $item->hostingPackage?->name ?? 'Hosting package unavailable' }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $item->domain_name }}</p>
                                    </div>
                                    <strong class="whitespace-nowrap text-base text-slate-900">
                                        {{ strtoupper($cart->currency) }} {{ number_format((float) $item->line_total, 2) }}
                                    </strong>
                                </div>

                                @if ($cart->status === 'active')
                                    <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
                                        <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex flex-1 flex-wrap items-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="min-w-[220px] flex-1">
                                                <label for="package-hosting-{{ $item->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Package / supported billing period</label>
                                                <select id="package-hosting-{{ $item->id }}" name="hosting_package_id" required
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                                    @foreach ($packages as $package)
                                                        <option value="{{ $package->id }}" @selected($item->hosting_package_id === $package->id)>{{ $package->name }} — ${{ number_format((float) $package->price_usd, 2) }} / {{ $package->billing_cycle }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit"
                                                class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                Update
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('cart.items.destroy', $item) }}"
                                            onsubmit="return confirm('Remove this hosting item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 bg-white px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">
                                                Remove hosting
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    @if ($cart->status === 'active')
                        <form method="POST" action="{{ route('cart.hosting.store') }}" class="mt-5 rounded-lg bg-slate-50 p-4">
                            @csrf
                            <h3 class="text-sm font-bold text-slate-900">Add hosting for a domain you already own</h3>
                            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                                <div>
                                    <label for="existing-domain" class="mb-1 block text-xs font-semibold text-slate-700">Domain name</label>
                                    <input id="existing-domain" name="domain" type="text" placeholder="example.com" required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label for="existing-package" class="mb-1 block text-xs font-semibold text-slate-700">Hosting package</label>
                                    <select id="existing-package" name="hosting_package_id" required
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">Select a package</option>
                                        @foreach ($packages as $package)
                                            <option value="{{ $package->id }}">{{ $package->name }} — ${{ number_format((float) $package->price_usd, 2) }} / {{ $package->billing_cycle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" @disabled($packages->isEmpty())
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">
                                    Add hosting
                                </button>
                            </div>
                        </form>
                    @endif
                </section>

                <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-24 md:p-6">
                    <h2 class="text-lg font-bold text-slate-900">Order summary</h2>

                    @if ($cart->status === 'active')
                        <form method="POST" action="{{ route('cart.currency') }}" class="mt-4 border-b border-slate-100 pb-4">
                            @csrf
                            @method('PATCH')
                            <label for="cart-currency" class="mb-1 block text-sm font-semibold text-slate-700">Payment currency</label>
                            <div class="flex gap-2">
                                <select id="cart-currency" name="currency"
                                    class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    @foreach ($supportedCurrencies as $currency)
                                        <option value="{{ $currency }}" @selected($cart->currency === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Update
                                </button>
                            </div>
                        </form>
                    @endif

                    <dl class="mt-4 space-y-3">
                        <div class="flex justify-between gap-3 text-sm text-slate-600">
                            <dt>Subtotal</dt>
                            <dd class="font-semibold text-slate-900">{{ strtoupper($cart->currency) }} {{ number_format($totals['subtotal'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 text-base font-bold text-slate-900">
                            <dt>Total</dt>
                            <dd>{{ strtoupper($cart->currency) }} {{ number_format($totals['total'], 2) }}</dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-xs leading-5 text-slate-500">
                        @if (strtoupper($cart->currency) === 'NGN')
                            Converted from ${{ number_format($totals['base_subtotal_usd'], 2) }} USD using the configured exchange rate. Prices and domain availability are checked again before an order is created.
                        @else
                            Prices are in USD. Live domain availability and prices are checked again before an order is created.
                        @endif
                    </p>

                    @if ($cart->status === 'checkout_pending' && $cart->service_order_id)
                        <a href="{{ route('checkout.show', $cart->service_order_id) }}"
                            class="mt-5 block w-full rounded-lg bg-blue-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-800">
                            Resume checkout
                        </a>
                    @else
                        @auth
                            <form method="POST" action="{{ route('cart.checkout') }}" class="mt-5">
                                @csrf
                                <button type="submit"
                                    class="w-full rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Continue to checkout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                                class="mt-5 block w-full rounded-lg bg-blue-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-800">
                                Log in to checkout
                            </a>
                            <a href="{{ route('register') }}"
                                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Create account
                            </a>
                            <p class="mt-3 text-xs leading-5 text-slate-500">Your cart is saved and will be merged into your account after sign-in.</p>
                        @endauth
                    @endif

                    <a href="{{ route('hosting.index') }}"
                        class="mt-5 block text-center text-sm font-semibold text-blue-700 hover:text-blue-900">
                        Marketplace / Add Service →
                    </a>
                </aside>
            </div>
        @endif
    </div>
@endsection
