@extends('public.layout')
@section('title', 'Choose Domain & Hosting — Drypp')
@section('body')
    <header class="site-header">
        <div class="wrap nav">
            <a class="brand" href="{{ route('home') }}"><span class="drop">◒</span>drypp<span
                    style="color:var(--blue)">.</span></a>
            <div class="actions">
                <a class="btn btn-light" href="{{ route('cart.index') }}">Cart</a>
                @auth
                    <a class="btn btn-light" href="{{ route('my-services.index') }}">My Services</a>
                @else
                    <a class="btn btn-light" href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Create account ↗</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="wrap domain-page">
        <div class="checkout-steps">
            <div class="step active"><strong>1. Search</strong><small>Check domains</small></div>
            <div class="step"><strong>2. Build your cart</strong><small>Add domains and hosting</small></div>
            <div class="step"><strong>3. Checkout</strong><small>One order, one payment</small></div>
        </div>
        <span class="eyebrow">Domain search</span>
        <h1>Find your domain</h1>
        <form action="{{ route('domains.search') }}" method="get" class="domain-search-form">
            <input class="form-control" name="domain" value="{{ $domain }}" required aria-label="Domain name">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        @if ($errors->any())
            <div class="notice" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="card domain-results">
            <div class="section-heading">
                <strong>Domain options for “{{ $domain }}”</strong>
                <p class="muted">USD prices include configured Drypp markup. Prices and availability are checked again
                    before an order is created.</p>
            </div>
            @forelse($results as $result)
                <article class="domain-row">
                    <div>
                        <strong class="domain-name">{{ $result['domain'] }}</strong>
                        <div class="domain-state">
                            @if ($result['available'])
                            <span class="available">Available</span>@else<span class="taken">Taken</span>
                            @endif
                            @if ($result['premium'])
                                <span class="muted"> · Premium domain</span>
                            @endif
                            @if ($result['sandbox'])
                                <span class="muted"> · Sandbox result (cannot be ordered)</span>
                            @endif
                        </div>
                        @if ($result['renewal_usd'] !== null)
                            <small class="muted">Estimated renewal:
                                ${{ number_format($result['renewal_usd'], 2) }}/year</small>
                        @endif
                    </div>
                    <div class="domain-actions">
                        @if ($result['drypp_price_usd'] !== null && (float) $result['drypp_price_usd'] > 0)
                            <strong class="domain-price">${{ number_format((float) $result['drypp_price_usd'], 2) }}
                                <small>/ first year USD</small></strong>
                        @else
                            <strong class="domain-price">Price unavailable</strong>
                        @endif
                        @if (
                            $result['available'] &&
                                !$result['sandbox'] &&
                                $result['drypp_price_usd'] !== null &&
                                (float) $result['drypp_price_usd'] > 0)
                            <form method="POST" action="{{ route('cart.domains.store') }}">
                                @csrf
                                <input type="hidden" name="domain" value="{{ $result['domain'] }}">
                                <button class="btn btn-primary" type="submit">Add domain to cart</button>
                            </form>
                        @elseif($result['available'])
                            <button class="btn btn-light" type="button" disabled>Pricing unavailable</button>
                        @else
                            <button class="btn btn-light" type="button" disabled>Unavailable</button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-results"><strong>No verified domain options found.</strong>
                    <p class="muted">The registrar did not confirm availability. Try another name or check again later.</p>
                </div>
            @endforelse
        </section>
        <p class="muted domain-footnote">You can add several domains, then select a supported hosting package for each one
            in your cart. Hosting for an existing domain can also be added from the Marketplace.</p>
    </main>

    <style>
        .domain-page {
            max-width: 1120px;
            padding: 36px 20px 80px
        }

        .checkout-steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 28px
        }

        .checkout-steps .step {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 13px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .checkout-steps .step.active {
            border-color: var(--blue)
        }

        .checkout-steps small {
            font-size: 12px
        }

        .domain-page h1 {
            margin: 8px 0 12px
        }

        .domain-search-form {
            display: flex;
            gap: 10px;
            margin: 22px 0
        }

        .domain-search-form .form-control {
            flex: 1;
            min-width: 0
        }

        .domain-results {
            padding: 0;
            overflow: hidden
        }

        .section-heading {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border)
        }

        .section-heading p {
            margin: 6px 0 0;
            font-size: 13px
        }

        .domain-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border)
        }

        .domain-name {
            font-size: 18px
        }

        .domain-state {
            font-size: 13px;
            margin: 5px 0
        }

        .available {
            color: #22c55e;
            font-weight: 700
        }

        .taken {
            color: #f87171;
            font-weight: 700
        }

        .domain-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap
        }

        .domain-price {
            font-size: 19px
        }

        .domain-price small {
            font-size: 12px;
            font-weight: 500
        }

        .empty-results {
            padding: 24px
        }

        .domain-footnote {
            font-size: 13px;
            margin-top: 18px
        }

        @media(max-width:760px) {
            .checkout-steps {
                gap: 7px
            }

            .checkout-steps .step {
                padding: 10px;
                font-size: 13px
            }

            .domain-search-form {
                flex-direction: column
            }

            .domain-search-form button {
                width: 100%
            }

            .domain-actions {
                width: 100%;
                justify-content: space-between
            }
        }
    </style>
@endsection
