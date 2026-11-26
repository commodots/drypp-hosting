@extends('layouts.app')
@section('title', 'Domain Pricing')
@section('content')
    <div class="admin-page-heading">
        <div>
            <h1>Domain Pricing</h1>
            <p>Set fallback cost estimates and markup. Live Porkbun prices are confirmed during search and checkout.</p>
        </div>
        <a class="admin-button" href="{{ route('admin.hosting.index') }}">Hosting Control Centre</a>
    </div>
    <section class="admin-panel">
        <h2>Add or update a domain extension</h2>
        <form method="POST" action="{{ route('admin.hosting.domain-pricing.store') }}" class="admin-grid" data-admin-loading>
            @csrf
            <div><label for="extension">Extension</label><input id="extension" name="extension" value="{{ old('extension') }}"
                    placeholder=".com" required maxlength="30"
                    style="display:block;width:100%;margin-top:6px;padding:10px;border:1px solid #dce4f1;border-radius:9px">
            </div>
            <div><label for="registration_cost_ngn">Registration cost (NGN)</label><input id="registration_cost_ngn"
                    name="registration_cost_ngn" type="number" min="0" value="{{ old('registration_cost_ngn', 0) }}"
                    required
                    style="display:block;width:100%;margin-top:6px;padding:10px;border:1px solid #dce4f1;border-radius:9px">
            </div>
            <div><label for="renewal_cost_ngn">Renewal cost (NGN)</label><input id="renewal_cost_ngn"
                    name="renewal_cost_ngn" type="number" min="0" value="{{ old('renewal_cost_ngn', 0) }}" required
                    style="display:block;width:100%;margin-top:6px;padding:10px;border:1px solid #dce4f1;border-radius:9px">
            </div>
            <div><label for="markup_percent">Markup (%)</label><input id="markup_percent" name="markup_percent"
                    type="number" min="0" max="100" value="{{ old('markup_percent', 0) }}" required
                    style="display:block;width:100%;margin-top:6px;padding:10px;border:1px solid #dce4f1;border-radius:9px">
            </div>
            <div><label for="markup_ngn">Legacy fixed markup (NGN)</label><input id="markup_ngn" name="markup_ngn"
                    type="number" min="0" value="{{ old('markup_ngn', 0) }}" required
                    style="display:block;width:100%;margin-top:6px;padding:10px;border:1px solid #dce4f1;border-radius:9px">
            </div>
            <div style="align-self:end"><label><input type="checkbox" name="is_active" value="1" checked>
                    Active</label></div>
            <div><button class="admin-button admin-button-primary" type="submit">Save domain pricing</button></div>
        </form>
    </section>
    <section class="admin-panel">
        <h2>Configured extensions</h2>
        @if ($domainPricing->isEmpty())
            <div class="admin-empty">No domain pricing rules have been added.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Extension</th>
                            <th>Registration cost</th>
                            <th>Renewal cost</th>
                            <th>Markup</th>
                            <th>Indicative selling price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($domainPricing as $price)
                            <tr>
                                <td><strong>{{ $price->extension }}</strong></td>
                                <td>NGN {{ number_format($price->registration_cost_ngn) }}</td>
                                <td>NGN {{ number_format($price->renewal_cost_ngn) }}</td>
                                <td>{{ $price->markup_percent }}%</td>
                                <td>NGN {{ number_format($price->sellingPrice()) }}</td>
                                <td><span class="admin-status"
                                        data-state="{{ $price->is_active ? 'active' : 'inactive' }}">{{ $price->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px">{{ $domainPricing->links() }}</div>
        @endif
    </section>
@endsection
