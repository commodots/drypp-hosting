@extends('layouts.app')
@section('title', 'Hosting Accounts')
@section('content')
    <div class="admin-page-heading">
        <div>
            <h1>Hosting Accounts</h1>
            <p>Customer accounts, package assignments, provider references and lifecycle status.</p>
        </div>
        <a class="admin-button admin-button-primary" href="{{ route('admin.provisioning.index') }}">Provisioning queue</a>
    </div>
    <section class="admin-panel">
        <h2>All hosting accounts</h2>
        @if ($accounts->isEmpty())
            <div class="admin-empty">No hosting accounts have been created yet.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Domain / account</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Server</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->domain_name }}</strong>
                                    @if ($account->cpanel_username)
                                        <br><span>cPanel: {{ $account->cpanel_username }}</span>
                                    @endif
                                </td>
                                <td>{{ $account->user?->name }}<br><span>{{ $account->user?->email }}</span></td>
                                <td>{{ $account->package?->name ?: '—' }}</td>
                                <td>{{ $account->server?->name ?: 'Unassigned' }}</td>
                                <td>{{ $account->order?->order_number ?: '—' }}</td>
                                <td><span class="admin-status"
                                        data-state="{{ str_contains($account->status, 'failed') || $account->status === 'suspended' ? 'failed' : ($account->status === 'active' ? 'active' : 'pending') }}">{{ str_replace('_', ' ', ucfirst($account->status)) }}</span>
                                </td>
                                <td>{{ $account->expires_at?->format('M j, Y') ?: 'Not recorded' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $accounts->links() }}</div>
        @endif
    </section>
@endsection
