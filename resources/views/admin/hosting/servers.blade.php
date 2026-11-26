@extends('layouts.app')
@section('title', 'Server Configuration')
@section('content')
<div class="admin-page-heading">
    <div><h1>Server Configuration</h1><p>Monitor account allocation and enable or pause servers for new orders.</p></div>
    <a class="admin-button admin-button-primary" href="{{ route('admin.hosting.index') }}">Add a server</a>
</div>
<section class="admin-panel">
    <h2>Servers and capacity</h2>
    @if($servers->isEmpty())
        <div class="admin-empty">No servers are configured. Add your first server in the Hosting Control Centre.</div>
    @else
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>Server</th><th>Provider</th><th>Hostname / IP</th><th>Accounts</th><th>Capacity</th><th>Package capacity</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @foreach($servers as $server)
                <tr>
                    <td><strong>{{ $server->name }}</strong></td>
                    <td>{{ ucfirst($server->provider) }}</td>
                    <td>{{ $server->hostname ?: $server->ip_address ?: '—' }}</td>
                    <td>{{ $server->active_accounts_count }}</td>
                    <td>{{ $server->active_accounts_count }} active + {{ $server->awaiting_orders_count }} reserved / {{ $server->max_accounts }}</td>
                    <td>{{ $server->package_capacity ? json_encode($server->package_capacity) : 'Package defaults' }}</td>
                    <td><span class="admin-status" data-state="{{ $server->is_active ? 'active' : 'inactive' }}">{{ $server->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><form method="POST" action="{{ route('admin.hosting.servers.toggle', $server) }}" onsubmit="return confirm('{{ $server->is_active ? 'Disable' : 'Enable' }} this server?')">
                        @csrf @method('PATCH')
                        <button class="admin-button" type="submit">{{ $server->is_active ? 'Disable' : 'Enable' }}</button>
                    </form></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $servers->links() }}</div>
        <h2 style="margin-top:24px">Edit server configuration</h2>
        @foreach($servers as $server)
            <details style="padding:14px 0;border-top:1px solid #e4eaf4">
                <summary style="cursor:pointer;font-weight:750;color:#1b2b49">{{ $server->name }} — edit limits and allocation</summary>
                <form method="POST" action="{{ route('admin.hosting.servers.update', $server) }}" style="margin-top:14px" data-admin-loading onsubmit="return confirm('Save these server settings? They affect order allocation.')">
                    @csrf @method('PATCH')
                    <div class="admin-grid">
                        <div><label for="server-name-{{ $server->id }}">Name</label><input id="server-name-{{ $server->id }}" name="name" value="{{ $server->name }}" maxlength="120" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-provider-{{ $server->id }}">Provider</label><select id="server-provider-{{ $server->id }}" name="provider" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px">@foreach(['verpex', 'manual', 'other'] as $provider)<option value="{{ $provider }}" @selected($server->provider === $provider)>{{ ucfirst($provider) }}</option>@endforeach</select></div>
                        <div><label for="server-hostname-{{ $server->id }}">Hostname</label><input id="server-hostname-{{ $server->id }}" name="hostname" value="{{ $server->hostname }}" maxlength="255" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-ip-{{ $server->id }}">IP address</label><input id="server-ip-{{ $server->id }}" name="ip_address" value="{{ $server->ip_address }}" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-accounts-{{ $server->id }}">Maximum accounts</label><input id="server-accounts-{{ $server->id }}" name="max_accounts" type="number" min="1" max="100000" value="{{ $server->max_accounts }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-disk-{{ $server->id }}">Disk limit (GB)</label><input id="server-disk-{{ $server->id }}" name="disk_limit_gb" type="number" min="1" value="{{ $server->disk_limit_gb }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-bandwidth-{{ $server->id }}">Bandwidth limit (GB)</label><input id="server-bandwidth-{{ $server->id }}" name="bandwidth_limit_gb" type="number" min="1" value="{{ $server->bandwidth_limit_gb }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="server-capacity-{{ $server->id }}">Per-package capacity JSON</label><input id="server-capacity-{{ $server->id }}" name="package_capacity" value="{{ $server->package_capacity ? json_encode($server->package_capacity) : '' }}" placeholder='{"package-slug":50}' style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div style="grid-column:1/-1"><label for="server-notes-{{ $server->id }}">Internal notes</label><textarea id="server-notes-{{ $server->id }}" name="notes" maxlength="2000" rows="2" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px">{{ $server->notes }}</textarea></div>
                        <div><label><input type="checkbox" name="is_active" value="1" @checked($server->is_active)> Active for new allocations</label></div>
                        <div><button class="admin-button admin-button-primary" type="submit">Save server configuration</button></div>
                    </div>
                </form>
            </details>
        @endforeach
    @endif
</section>
<section class="admin-panel">
    <h2>Server provisioning configuration</h2>
    <p style="margin:0;color:#71809b;font-size:13px;line-height:1.6">Provider credentials are held in deployment environment configuration and are never entered or displayed here. Use the Hosting Control Centre to add a server or update its capacity, limits and internal notes.</p>
    <a class="admin-button admin-button-primary" style="margin-top:14px" href="{{ route('admin.hosting.index') }}">Go to Hosting Control Centre</a>
</section>
@endsection
