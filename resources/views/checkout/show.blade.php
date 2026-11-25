@extends('public.layout')
@section('title','Checkout '.$order->order_number.' — Drypp')
@section('body')
<div class="dashboard-shell"><header class="appbar"><div class="wrap appnav"><a class="brand" href="{{ route('home') }}"><span class="drop">◒</span>drypp</a><a class="btn btn-light" href="{{ route('hosting.index') }}">← Back to hosting</a></div></header><main class="wrap content" style="max-width:780px"><h1>Review your order</h1><p class="muted">Confirm the details before continuing to payment.</p>@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif @if($errors->any())<div style="padding:12px 14px;background:#fff0f0;color:#a21c1c;border-radius:10px;margin:18px 0">{{ $errors->first() }}</div>@endif<div class="panel"><div style="display:flex;justify-content:space-between;gap:20px;align-items:start"><div><span class="pill">{{ $order->order_number }}</span><h2 style="margin:14px 0 5px">{{ $order->hostingPackage?->name ?? 'Drypp service' }}</h2><div class="muted">Domain: {{ $order->domain_name }}</div><div class="muted" style="margin-top:6px">Billing cycle: {{ $order->hostingPackage?->billing_cycle ?? '—' }}</div>
<div style="margin-top:14px;font-size:13px;line-height:1.9">
@foreach($order->items as $item)
<div>{{ ucwords(str_replace('_',' ',$item->item_type)) }}: {{ $item->domain_name ?: $item->hostingPackage?->name }} — {{ strtoupper($item->currency) }} {{ number_format((float)$item->line_total,2) }}</div>
@endforeach
</div></div><div style="text-align:right"><div class="muted" style="font-size:12px">Total due</div><div class="price">{{ $order->formattedAmount() }}</div><span class="status">{{ str_replace('_',' ',ucfirst($order->status)) }}</span></div></div><hr style="border:0;border-top:1px solid var(--line);margin:24px 0"><div class="muted" style="line-height:1.7;font-size:13px">Payment is processed securely by Paystack in {{ strtoupper($order->currency ?: 'NGN') }}. Domain registration and automatic Verpex hosting provisioning are separate fulfilment steps and will be shown in your account when enabled.</div>@if($order->status==='paid')
<a class="btn btn-primary" style="margin-top:20px" href="{{ route('hosting.index') }}">View hosting dashboard →</a>
@else
<form method="POST" action="{{ route('checkout.pay',$order) }}" style="margin-top:22px">@csrf<button class="btn btn-primary" type="submit">Pay securely with Paystack →</button></form>
@endif</div></main></div>
@endsection
