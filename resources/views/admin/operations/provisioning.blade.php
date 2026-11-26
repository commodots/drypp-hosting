@extends('layouts.app')
@section('title', 'Provisioning Queue')
@section('content')
    <div class="admin-page-heading">
        <div>
            <h1>Provisioning Queue</h1>
            <p>Track pending and failed hosting account jobs. Retries are available only for paid orders.</p>
        </div>
        <a class="admin-button" href="{{ route('admin.hosting.accounts') }}">All hosting accounts</a>
    </div>
    <section class="admin-panel">
        <h2>Pending and failed provisioning</h2>
        @if ($accounts->isEmpty())
            <div class="admin-empty">The provisioning queue is clear.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Customer</th>
                            <th>Package / server</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Last error</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->domain_name }}</strong></td>
                                <td>{{ $account->user?->name }}<br><span>{{ $account->user?->email }}</span></td>
                                <td>{{ $account->package?->name ?: '—' }}<br><span>{{ $account->server?->name ?: 'Unassigned' }}</span>
                                </td>
                                <td>{{ $account->order?->order_number ?: '—' }}<br><span>{{ $account->order?->status ?: 'Order unavailable' }}</span>
                                </td>
                                <td><span class="admin-status"
                                        data-state="{{ $account->status === 'provisioning_failed' ? 'failed' : 'pending' }}">{{ str_replace('_', ' ', ucfirst($account->status)) }}</span>
                                </td>
                                <td>{{ $account->status === 'provisioning_failed' ? \Illuminate\Support\Str::limit(data_get($account->provider_metadata, 'last_error', data_get($account->provider_metadata, 'dispatch_error', 'No error detail recorded.')), 90) : '—' }}
                                </td>
                                <td>
                                    @if ($account->status === 'provisioning_failed' && $account->order?->status === 'paid')
                                        <form method="POST" action="{{ route('admin.provisioning.retry', $account) }}"
                                            onsubmit="return confirm('Queue this hosting provisioning job again?')">
                                            @csrf<button class="admin-button admin-button-primary"
                                                type="submit">Retry</button></form>
                                    @elseif($account->status === 'provisioning_failed')
                                        <span>Paid order required</span>
                                    @else
                                        <span>Queued</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $accounts->links() }}</div>
        @endif
    </section>
@endsection
