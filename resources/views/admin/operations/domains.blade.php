@extends('layouts.app')
@section('title', 'Domain Management')
@section('content')
<div class="admin-page-heading">
    <div><h1>Domain Management</h1><p>Review domain services created from paid and legacy orders.</p></div>
    <a class="admin-button" href="{{ route('admin.domain-pricing.index') }}">Domain pricing</a>
</div>
<section class="admin-panel">
    <h2>Customer domain services</h2>
    @if($domains->isEmpty())
        <div class="admin-empty">No domain services have been recorded. Domain registrar provisioning is not yet available.</div>
    @else
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>Domain</th><th>Customer</th><th>Provider</th><th>Order</th><th>Status</th><th>Expires</th></tr></thead>
            <tbody>@foreach($domains as $domain)
                <tr>
                    <td><strong>{{ $domain->identifier ?: $domain->name }}</strong></td>
                    <td>{{ $domain->user?->name }}<br><span>{{ $domain->user?->email }}</span></td>
                    <td>{{ $domain->provider ?: 'Not assigned' }}</td>
                    <td>{{ $domain->order?->order_number ?: 'Legacy record' }}</td>
                    <td><span class="admin-status" data-state="{{ str_contains($domain->status, 'failed') ? 'failed' : ($domain->status === 'active' ? 'active' : 'pending') }}">{{ str_replace('_', ' ', ucfirst($domain->status)) }}</span></td>
                    <td>{{ $domain->expires_at?->format('M j, Y') ?: 'Not recorded' }}</td>
                </tr>
            @endforeach</tbody>
        </table></div>
        <div style="margin-top:16px">{{ $domains->links() }}</div>
    @endif
</section>
<div class="admin-alert admin-alert-info" role="note">Registrar registration, renewal, DNS and transfer actions are not connected yet. This page is an inventory view; it does not change registrar records.</div>
@endsection
