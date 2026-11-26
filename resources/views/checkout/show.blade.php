@extends('public.layout')
@section('title', 'Checkout ' . $order->order_number . ' — Drypp')
@section('body')
    <div class="dashboard-shell">
        <header class="appbar">
            <div class="wrap appnav"><a class="brand" href="{{ route('home') }}"><span class="drop">◒</span>drypp</a><a
                    class="btn btn-light" href="{{ route('cart.index') }}">← Cart</a></div>
        </header>
        <main class="wrap content checkout-page">
            <h1>Review your order</h1>
            <p class="muted">One combined order and one secure payment.</p>
            @if (session('status'))
                <div class="notice" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="checkout-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <section class="panel order-panel">
                <div class="order-heading">
                    <div><span class="pill">{{ $order->order_number }}</span>
                        <h2>{{ $order->items->count() > 1 ? 'Marketplace order' : ($order->items->first()?->domain_name ?: $order->items->first()?->hostingPackage?->name ?: 'Drypp service') }}
                        </h2>
                        <p class="muted">Order items below are priced and recorded in
                            {{ strtoupper($order->currency ?: 'USD') }}.</p>
                    </div>
                    <span class="status">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
                </div>
                <div class="order-lines">
                    @foreach ($order->items as $item)
                        <div class="order-line">
                            <div>
                                <strong>{{ ucwords(str_replace('_', ' ', $item->item_type)) }}</strong>
                                <div>{{ $item->domain_name ?: $item->hostingPackage?->name ?: 'Service' }}</div>
                                <small
                                    class="muted">{{ $item->billing_period ? 'Billing period: ' . $item->billing_period : '' }}</small>
                            </div>
                            <strong>{{ strtoupper($item->currency) }}
                                {{ number_format((float) $item->line_total, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="order-total"><span>Total due</span><strong>{{ $order->formattedAmount() }}</strong></div>
                <p class="muted payment-note">Payment is processed securely by Paystack in
                    {{ strtoupper($order->currency ?: 'USD') }}. Domains and hosting appear in My Services after payment
                    confirmation; domain registration and hosting provisioning follow their provider workflows.</p>
                @if ($order->status === 'paid')
                    <a class="btn btn-primary" href="{{ route('my-services.index') }}">View My Services</a>
                @else
                    <form method="POST" action="{{ route('checkout.pay', $order) }}">@csrf<button class="btn btn-primary"
                            type="submit">Pay once with Paystack</button></form>
                @endif
            </section>
        </main>
    </div>
    <style>
        .checkout-page {
            max-width: 820px;
            padding-top: 36px;
            padding-bottom: 70px
        }

        .order-panel {
            padding: 24px
        }

        .order-heading {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 14px
        }

        .order-heading h2 {
            margin: 12px 0 5px
        }

        .order-heading p {
            margin: 0
        }

        .order-lines {
            margin-top: 22px;
            border-top: 1px solid var(--border)
        }

        .order-line {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border)
        }

        .order-line small {
            display: block;
            margin-top: 5px
        }

        .order-line>strong {
            white-space: nowrap
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 18px 0;
            font-size: 19px
        }

        .payment-note {
            font-size: 13px;
            line-height: 1.6;
            margin: 0 0 20px
        }

        .checkout-error {
            padding: 12px 14px;
            background: #fff0f0;
            color: #a21c1c;
            border-radius: 10px;
            margin: 18px 0
        }
    </style>
@endsection
