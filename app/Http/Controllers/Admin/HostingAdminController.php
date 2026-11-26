<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainPricing;
use App\Models\HostingPackage;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use App\Models\HostingAccount;
use App\Models\ServiceOrderItem;
use App\Models\UserService;
use App\Jobs\ProvisionHostingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class HostingAdminController extends Controller
{
    public function index()
    {
        $servers = HostingServer::withCount([
            'accounts as active_accounts_count' => fn($query) => $query->whereIn('status', ['pending_provisioning', 'active', 'suspended']),
            'orders as awaiting_orders_count' => fn($query) => $query->where('status', 'awaiting_payment'),
        ])->orderBy('name')->get();
        $packages = HostingPackage::orderBy('price_usd')->orderBy('price_ngn')->get();
        $orders = ServiceOrder::with(['user', 'hostingPackage'])->latest()->take(20)->get();
        $domainPricing = DomainPricing::orderBy('extension')->get();
        $failedAccounts = HostingAccount::with(['user', 'package', 'server', 'order'])
            ->where('status', 'provisioning_failed')
            ->latest()
            ->take(10)
            ->get();
        $provisioningAccounts = HostingAccount::with(['user', 'package', 'server'])
            ->whereIn('status', ['pending_provisioning', 'active', 'provisioning_failed'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.hosting.index', compact('servers', 'packages', 'orders', 'domainPricing', 'failedAccounts', 'provisioningAccounts'));
    }

    public function packages()
    {
        $packages = HostingPackage::withCount('accounts')->orderBy('name')->paginate(20);

        return view('admin.hosting.packages', compact('packages'));
    }

    public function servers()
    {
        $servers = HostingServer::withCount([
            'accounts as active_accounts_count' => fn($query) => $query->whereIn('status', ['pending_provisioning', 'active', 'suspended']),
            'orders as awaiting_orders_count' => fn($query) => $query->where('status', 'awaiting_payment'),
        ])->orderBy('name')->paginate(20);

        return view('admin.hosting.servers', compact('servers'));
    }

    public function domainPricing()
    {
        $domainPricing = DomainPricing::orderBy('extension')->paginate(30);

        return view('admin.hosting.domain-pricing', compact('domainPricing'));
    }

    public function storeServer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'provider' => ['required', 'in:verpex,manual,other'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'max_accounts' => ['required', 'integer', 'min:1', 'max:100000'],
            'disk_limit_gb' => ['required', 'integer', 'min:1'],
            'bandwidth_limit_gb' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'package_capacity' => ['nullable', 'json'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['package_capacity'] = $request->filled('package_capacity') ? json_decode($request->input('package_capacity'), true) : null;
        if ($request->filled('package_capacity') && !is_array($data['package_capacity'])) {
            return back()->withErrors(['package_capacity' => 'Package capacity must be a JSON object mapping package slugs to account limits.'])->withInput();
        }
        if (is_array($data['package_capacity'])) {
            foreach ($data['package_capacity'] as $slug => $limit) {
                if (!is_string($slug) || !is_numeric($limit) || (int) $limit < 0) {
                    return back()->withErrors(['package_capacity' => 'Package capacity must be JSON mapping package slugs to non-negative account limits.'])->withInput();
                }
                $data['package_capacity'][$slug] = (int) $limit;
            }
        }
        HostingServer::create($data);

        return back()->with('status', 'Hosting server saved. Add API credentials through environment configuration, not this form.');
    }

    public function updateServer(Request $request, HostingServer $server)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'provider' => ['required', 'in:verpex,manual,other'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'max_accounts' => ['required', 'integer', 'min:1', 'max:100000'],
            'disk_limit_gb' => ['required', 'integer', 'min:1'],
            'bandwidth_limit_gb' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'package_capacity' => ['nullable', 'json'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['package_capacity'] = $request->filled('package_capacity') ? json_decode($request->input('package_capacity'), true) : null;
        if ($request->filled('package_capacity') && !is_array($data['package_capacity'])) {
            return back()->withErrors(['package_capacity' => 'Package capacity must be a JSON object mapping package slugs to account limits.'])->withInput();
        }
        if (is_array($data['package_capacity'])) {
            foreach ($data['package_capacity'] as $slug => $limit) {
                if (!is_string($slug) || !is_numeric($limit) || (int) $limit < 0) {
                    return back()->withErrors(['package_capacity' => 'Package capacity must map package slugs to non-negative account limits.'])->withInput();
                }
                $data['package_capacity'][$slug] = (int) $limit;
            }
        }
        $server->update($data);

        return back()->with('status', 'Hosting server configuration updated.');
    }

    public function toggleServer(HostingServer $server)
    {
        $server->update(['is_active' => !$server->is_active]);
        return back()->with('status', 'Server status updated.');
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'whm_package_name' => ['nullable', 'string', 'max:100'],
            'price_usd' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'disk_space_gb' => ['required', 'integer', 'min:1'],
            'bandwidth_gb' => ['required', 'integer', 'min:1'],
            'email_accounts' => ['required', 'integer', 'min:0'],
            'addon_domains' => ['required', 'integer', 'min:0'],
            'databases' => ['required', 'integer', 'min:0'],
            'max_accounts_per_server' => ['required', 'integer', 'min:1'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        $data['whm_package_name'] = trim((string) ($data['whm_package_name'] ?? '')) ?: $data['slug'];
        $data['price_ngn'] = 0; // Retained for legacy NGN records; catalogue packages are USD.
        $data['features'] = ['Free SSL subject to provider support', 'cPanel access', 'Email account management', 'DNS management'];
        $data['free_website_enabled'] = $request->boolean('free_website_enabled');
        $data['is_active'] = $request->boolean('is_active');
        HostingPackage::create($data);

        return back()->with('status', 'Hosting package created.');
    }

    public function togglePackage(HostingPackage $hostingPackage)
    {
        $hostingPackage->update(['is_active' => !$hostingPackage->is_active]);
        return back()->with('status', 'Hosting package status updated.');
    }

    public function updatePackage(Request $request, HostingPackage $hostingPackage)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_usd' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'disk_space_gb' => ['required', 'integer', 'min:1'],
            'bandwidth_gb' => ['required', 'integer', 'min:1'],
            'email_accounts' => ['required', 'integer', 'min:0'],
            'addon_domains' => ['required', 'integer', 'min:0'],
            'databases' => ['required', 'integer', 'min:0'],
            'max_accounts_per_server' => ['required', 'integer', 'min:1'],
        ]);
        $data['free_website_enabled'] = $request->boolean('free_website_enabled');
        $data['is_active'] = $request->boolean('is_active');
        $hostingPackage->update($data);

        return back()->with('status', 'Hosting package updated.');
    }

    public function updateWhmMapping(Request $request, HostingPackage $hostingPackage)
    {
        $data = $request->validate([
            'whm_package_name' => ['required', 'string', 'max:100'],
        ]);

        $hostingPackage->update(['whm_package_name' => trim($data['whm_package_name'])]);

        return back()->with('status', 'WHM package mapping updated for ' . $hostingPackage->name . '.');
    }

    public function storeDomainPricing(Request $request)
    {
        $data = $request->validate([
            'extension' => ['required', 'regex:/^\.[a-zA-Z0-9.-]{2,29}$/'],
            'registration_cost_ngn' => ['required', 'integer', 'min:0'],
            'renewal_cost_ngn' => ['required', 'integer', 'min:0'],
            'markup_ngn' => ['required', 'integer', 'min:0'],
            'markup_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        $data['extension'] = strtolower($data['extension']);
        $data['is_active'] = $request->boolean('is_active');
        DomainPricing::updateOrCreate(['extension' => $data['extension']], $data);

        return back()->with('status', 'Domain pricing saved. Live Porkbun availability and pricing are checked during domain search and checkout.');
    }

    public function retryProvisioning(HostingAccount $account)
    {
        abort_unless($account->status === 'provisioning_failed', 409, 'Only failed provisioning requests can be retried.');

        $account->load('order');
        abort_unless(
            in_array($account->order?->status, ['paid', 'processing', 'partially_fulfilled'], true),
            409,
            'Only paid hosting orders can be provisioned.'
        );

        DB::transaction(function () use ($account) {
            $lockedAccount = HostingAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedAccount->status === 'provisioning_failed', 409, 'This provisioning request has already changed.');

            $metadata = $lockedAccount->provider_metadata ?? [];
            unset($metadata['last_error'], $metadata['failed_at'], $metadata['dispatch_error']);
            $lockedAccount->update([
                'status' => 'pending_provisioning',
                'provider_metadata' => $metadata,
            ]);

            UserService::query()
                ->where('user_id', $lockedAccount->user_id)
                ->where('service_order_id', $lockedAccount->service_order_id)
                ->where('service_type', 'hosting')
                ->when(
                    $lockedAccount->service_order_item_id,
                    fn($query) => $query->where('service_order_item_id', $lockedAccount->service_order_item_id),
                    fn($query) => $query->where('identifier', $lockedAccount->domain_name)
                )
                ->update(['status' => 'pending_provisioning', 'updated_at' => now()]);

            if ($lockedAccount->service_order_item_id) {
                ServiceOrderItem::find($lockedAccount->service_order_item_id)?->setFulfillmentStatus('provisioning');
            }
        });

        try {
            ProvisionHostingAccount::dispatch($account->id);
        } catch (Throwable $exception) {
            report($exception);
            $account->refresh();
            $metadata = $account->provider_metadata ?? [];
            $metadata['dispatch_error'] = 'Provisioning retry could not be queued.';
            $account->update(['status' => 'provisioning_failed', 'provider_metadata' => $metadata]);
            UserService::query()
                ->where('user_id', $account->user_id)
                ->where('service_order_id', $account->service_order_id)
                ->where('service_type', 'hosting')
                ->when(
                    $account->service_order_item_id,
                    fn($query) => $query->where('service_order_item_id', $account->service_order_item_id),
                    fn($query) => $query->where('identifier', $account->domain_name)
                )
                ->update(['status' => 'provisioning_failed', 'updated_at' => now()]);
            if ($account->service_order_item_id) {
                ServiceOrderItem::find($account->service_order_item_id)?->setFulfillmentStatus('provisioning_failed');
            }

            return back()->withErrors(['provisioning' => 'The retry could not be queued. Please try again or contact support.']);
        }

        return back()->with('status', 'Hosting provisioning retry has been queued.');
    }
}
