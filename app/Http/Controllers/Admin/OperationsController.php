<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostingAccount;
use App\Models\User;
use App\Models\UserService;

class OperationsController extends Controller
{
    public function customers()
    {
        $customers = User::query()
            ->where('role', 'user')
            ->with(['subscription.package'])
            ->withCount('userServices')
            ->latest()
            ->paginate(20);

        return view('admin.operations.customers', compact('customers'));
    }

    public function domains()
    {
        $domains = UserService::query()
            ->with(['user:id,name,email', 'order:id,order_number,status'])
            ->where('service_type', 'domain')
            ->latest()
            ->paginate(20);

        return view('admin.operations.domains', compact('domains'));
    }

    public function hostingAccounts()
    {
        $accounts = HostingAccount::query()
            ->with(['user:id,name,email', 'package:id,name', 'server:id,name', 'order:id,order_number,status'])
            ->latest()
            ->paginate(20);

        return view('admin.operations.hosting-accounts', compact('accounts'));
    }

    public function provisioningQueue()
    {
        $accounts = HostingAccount::query()
            ->with(['user:id,name,email', 'package:id,name', 'server:id,name', 'order:id,order_number,status'])
            ->whereIn('status', ['pending_provisioning', 'provisioning_failed'])
            ->orderByRaw("CASE WHEN status = 'provisioning_failed' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20);

        return view('admin.operations.provisioning', compact('accounts'));
    }

    public function supportAndRenewals()
    {
        $supportItems = UserService::query()
            ->with(['user:id,name,email', 'order:id,order_number,status'])
            ->whereIn('status', ['pending_registration', 'pending_fulfillment', 'provisioning_failed'])
            ->latest()
            ->paginate(15, ['*'], 'support_page');

        $renewals = UserService::query()
            ->with(['user:id,name,email', 'order:id,order_number,status'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at')
            ->paginate(15, ['*'], 'renewals_page');

        return view('admin.operations.support-renewals', compact('supportItems', 'renewals'));
    }
}
