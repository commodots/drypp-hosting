@extends('layouts.app')
@section('title', 'Manage Hosting Packages')
@section('content')
<div class="admin-page-heading">
    <div><h1>Manage Hosting Packages</h1><p>Review published plans, capacity and WHM package mappings.</p></div>
    <a class="admin-button admin-button-primary" href="{{ route('admin.hosting.index') }}">Open Hosting Control Centre</a>
</div>
<section class="admin-panel">
    <h2>Hosting packages</h2>
    @if($packages->isEmpty())
        <div class="admin-empty">No hosting packages are configured yet. Add one from the Hosting Control Centre.</div>
    @else
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>Package</th><th>Price</th><th>Accounts</th><th>Capacity limit</th><th>WHM package mapping</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($packages as $package)
                <tr>
                    <td><strong>{{ $package->name }}</strong><br><span>{{ $package->billing_cycle }} · {{ $package->disk_space_gb }} GB storage</span></td>
                    <td>USD {{ number_format((float) $package->price_usd, 2) }}</td>
                    <td>{{ $package->accounts_count }}</td>
                    <td>{{ $package->max_accounts_per_server }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.hosting.packages.whm-mapping', $package) }}" data-admin-loading>
                            @csrf @method('PATCH')
                            <label class="sr-only" for="whm-package-{{ $package->id }}">WHM package mapping for {{ $package->name }}</label>
                            <input id="whm-package-{{ $package->id }}" name="whm_package_name" value="{{ $package->whm_package_name }}" required maxlength="100" style="min-width:170px;padding:8px;border:1px solid #dce4f1;border-radius:8px">
                            <button class="admin-button" type="submit">Save mapping</button>
                        </form>
                    </td>
                    <td><span class="admin-status" data-state="{{ $package->is_active ? 'active' : 'inactive' }}">{{ $package->is_active ? 'Published' : 'Hidden' }}</span></td>
                    <td><form method="POST" action="{{ route('admin.hosting.packages.toggle', $package) }}" onsubmit="return confirm('{{ $package->is_active ? 'Hide' : 'Publish' }} this hosting package?')">
                        @csrf @method('PATCH')
                        <button class="admin-button" type="submit">{{ $package->is_active ? 'Hide' : 'Publish' }}</button>
                    </form></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $packages->links() }}</div>
        <h2 style="margin-top:24px">Edit package details</h2>
        @foreach($packages as $package)
            <details style="padding:14px 0;border-top:1px solid #e4eaf4">
                <summary style="cursor:pointer;font-weight:750;color:#1b2b49">{{ $package->name }} — edit price and limits</summary>
                <form method="POST" action="{{ route('admin.hosting.packages.update', $package) }}" style="margin-top:14px" data-admin-loading onsubmit="return confirm('Save these package details and prices?')">
                    @csrf @method('PATCH')
                    <div class="admin-grid">
                        <div><label for="package-name-{{ $package->id }}">Name</label><input id="package-name-{{ $package->id }}" name="name" value="{{ $package->name }}" required maxlength="100" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-price-{{ $package->id }}">Price (USD)</label><input id="package-price-{{ $package->id }}" name="price_usd" type="number" min="0.01" step="0.01" value="{{ $package->price_usd }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-cycle-{{ $package->id }}">Billing cycle</label><select id="package-cycle-{{ $package->id }}" name="billing_cycle" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px">@foreach(['monthly', 'quarterly', 'yearly'] as $cycle)<option value="{{ $cycle }}" @selected($package->billing_cycle === $cycle)>{{ ucfirst($cycle) }}</option>@endforeach</select></div>
                        <div><label for="package-storage-{{ $package->id }}">Storage (GB)</label><input id="package-storage-{{ $package->id }}" name="disk_space_gb" type="number" min="1" value="{{ $package->disk_space_gb }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-bandwidth-{{ $package->id }}">Bandwidth (GB)</label><input id="package-bandwidth-{{ $package->id }}" name="bandwidth_gb" type="number" min="1" value="{{ $package->bandwidth_gb }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-email-{{ $package->id }}">Email accounts</label><input id="package-email-{{ $package->id }}" name="email_accounts" type="number" min="0" value="{{ $package->email_accounts }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-domains-{{ $package->id }}">Addon domains</label><input id="package-domains-{{ $package->id }}" name="addon_domains" type="number" min="0" value="{{ $package->addon_domains }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-databases-{{ $package->id }}">Databases</label><input id="package-databases-{{ $package->id }}" name="databases" type="number" min="0" value="{{ $package->databases }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div><label for="package-capacity-{{ $package->id }}">Max accounts per server</label><input id="package-capacity-{{ $package->id }}" name="max_accounts_per_server" type="number" min="1" value="{{ $package->max_accounts_per_server }}" required style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px"></div>
                        <div style="grid-column:1/-1"><label for="package-description-{{ $package->id }}">Description</label><textarea id="package-description-{{ $package->id }}" name="description" maxlength="1000" rows="2" style="display:block;width:100%;margin-top:6px;padding:9px;border:1px solid #dce4f1;border-radius:8px">{{ $package->description }}</textarea></div>
                        <div style="display:flex;gap:18px;flex-wrap:wrap"><label><input type="checkbox" name="free_website_enabled" value="1" @checked($package->free_website_enabled)> Free website</label><label><input type="checkbox" name="is_active" value="1" @checked($package->is_active)> Published</label></div>
                        <div><button class="admin-button admin-button-primary" type="submit">Save package details</button></div>
                    </div>
                </form>
            </details>
        @endforeach
    @endif
</section>
@endsection
