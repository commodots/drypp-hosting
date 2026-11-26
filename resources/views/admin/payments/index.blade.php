@extends('layouts.app')
@section('title', 'Payments')
@section('content')
    <div class="admin-page-heading">
        <div>
            <h1>Payments</h1>
            <p>Review customer subscriptions and recent service orders.</p>
        </div>
        <span class="admin-status" data-state="{{ $gatewayStatus === 'Configured' ? 'active' : 'pending' }}">Paystack:
            {{ $gatewayStatus }}</span>
    </div>
    <section class="admin-panel">
        <h2>Marketing subscription access</h2>
        @if ($users->isEmpty())
            <div class="admin-empty">No customer subscriptions are available.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Current plan</th>
                            <th>Expiry date</th>
                            <th>Manual access override</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><br><span>{{ $user->email }}</span></td>
                                <td>{{ $user->subscription?->package?->name ?: 'No active plan' }}</td>
                                <td>{{ $user->subscription?->expires_at?->format('M j, Y') ?: '—' }}</td>
                                <td>
                                    <form action="{{ route('admin.payments.override', $user) }}" method="POST"
                                        style="display:flex;gap:8px;flex-wrap:wrap"
                                        onsubmit="return confirm('Grant a one-month plan to this customer?')">
                                        @csrf
                                        <label class="sr-only" for="package-{{ $user->id }}">Choose plan for
                                            {{ $user->name }}</label>
                                        <select id="package-{{ $user->id }}" name="package_id" required
                                            style="padding:8px;border:1px solid #dce4f1;border-radius:8px">
                                            @foreach ($packages as $package)
                                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="admin-button admin-button-primary" type="submit">Grant
                                            access</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $users->links() }}</div>
        @endif
    </section>
    <section class="admin-panel">
        <h2>Recent service orders</h2>
        @if ($serviceOrders->isEmpty())
            <div class="admin-empty">No service orders have been recorded.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Total</th>
                            <th>Payment reference</th>
                            <th>Status</th>
                            <th>Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceOrders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user?->name ?: 'Customer unavailable' }}<br><span>{{ $order->user?->email }}</span>
                                </td>
                                <td>{{ str_replace('_', ' ', ucfirst($order->service_type)) }}@if ($order->domain_name)
                                        <br><span>{{ $order->domain_name }}</span>
                                    @endif
                                </td>
                                <td>{{ $order->formattedAmount() }}</td>
                                <td>{{ $order->payment_reference ?: '—' }}</td>
                                <td><span class="admin-status"
                                        data-state="{{ $order->status === 'paid' ? 'paid' : ($order->status === 'failed' ? 'failed' : 'pending') }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
                                </td>
                                <td>{{ $order->paid_at?->format('M j, Y H:i') ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $serviceOrders->links() }}</div>
        @endif
    </section>
@endsection
