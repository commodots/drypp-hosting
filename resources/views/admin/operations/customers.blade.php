@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="admin-page-heading">
    <div><h1>Customers</h1><p>Customer accounts, current marketing subscriptions and purchased services.</p></div>
</div>
<section class="admin-panel">
    <h2>Customer directory</h2>
    @if($customers->isEmpty())
        <div class="admin-empty">No customer accounts found.</div>
    @else
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>Customer</th><th>Email</th><th>Joined</th><th>Marketing plan</th><th>Plan status</th><th>Services</th></tr></thead>
            <tbody>@foreach($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->created_at?->format('M j, Y') ?: '—' }}</td>
                    <td>{{ $customer->subscription?->package?->name ?: 'No plan' }}</td>
                    <td><span class="admin-status" data-state="{{ $customer->subscription?->status === 'active' ? 'active' : 'inactive' }}">{{ $customer->subscription?->status ? ucfirst($customer->subscription->status) : 'No plan' }}</span></td>
                    <td>{{ $customer->user_services_count }}</td>
                </tr>
            @endforeach</tbody>
        </table></div>
        <div style="margin-top:16px">{{ $customers->links() }}</div>
    @endif
</section>
@endsection
