@extends('layouts.app')
@section('title', 'Support and Renewals')
@section('content')
    <div class="admin-page-heading">
        <div>
            <h1>Support and Renewals</h1>
            <p>Identify services needing fulfilment attention and services approaching their recorded expiry date.</p>
        </div>
    </div>
    <div class="admin-grid">
        <section class="admin-panel">
            <h2>Needs attention</h2>
            @if ($supportItems->isEmpty())
                <div class="admin-empty">No pending fulfilment or provisioning issues.</div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Customer</th>
                                <th>Order</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supportItems as $service)
                                <tr>
                                    <td><strong>{{ $service->name }}</strong><br><span>{{ $service->identifier ?: ucfirst($service->service_type) }}</span>
                                    </td>
                                    <td>{{ $service->user?->name }}<br><span>{{ $service->user?->email }}</span></td>
                                    <td>{{ $service->order?->order_number ?: '—' }}</td>
                                    <td><span class="admin-status"
                                            data-state="{{ str_contains($service->status, 'failed') ? 'failed' : 'pending' }}">{{ str_replace('_', ' ', ucfirst($service->status)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px">{{ $supportItems->links() }}</div>
            @endif
        </section>
        <section class="admin-panel">
            <h2>Expiring in 30 days or overdue</h2>
            @if ($renewals->isEmpty())
                <div class="admin-empty">No service expiry dates are recorded in the next 30 days.</div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Customer</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($renewals as $service)
                                <tr>
                                    <td><strong>{{ $service->name }}</strong><br><span>{{ $service->identifier ?: ucfirst($service->service_type) }}</span>
                                    </td>
                                    <td>{{ $service->user?->name }}<br><span>{{ $service->user?->email }}</span></td>
                                    <td>{{ $service->expires_at?->format('M j, Y') }}</td>
                                    <td><span class="admin-status"
                                            data-state="{{ $service->expires_at?->isPast() ? 'failed' : 'pending' }}">{{ $service->expires_at?->isPast() ? 'Overdue' : 'Upcoming' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px">{{ $renewals->links() }}</div>
            @endif
        </section>
    </div>
    <div class="admin-alert admin-alert-info" role="note">Support tickets and automated renewal billing are not connected
        in this release. Use these lists to identify follow-up work; no charges or provider changes are made here.</div>
@endsection
