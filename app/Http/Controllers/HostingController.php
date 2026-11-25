<?php

namespace App\Http\Controllers;

use App\Models\HostingAccount;
use App\Models\HostingPackage;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostingController extends Controller
{
    public function index()
    {
        $packages = HostingPackage::where('is_active', true)->orderBy('price_usd')->orderBy('price_ngn')->get();
        $accounts = HostingAccount::with(['package','server'])->where('user_id', auth()->id())->latest()->get();
        $orders = ServiceOrder::with('hostingPackage')->where('user_id', auth()->id())->latest()->take(10)->get();
        return view('hosting.index', compact('packages','accounts','orders'));
    }

    public function order(Request $request, HostingPackage $hostingPackage)
    {
        abort_unless($hostingPackage->is_active, 404);

        $validated = $request->validate([
            'domain_name' => ['required','string','max:253','regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
        ]);

        $domain = strtolower(trim($validated['domain_name']));
        $existing = HostingAccount::where('domain_name', $domain)
            ->whereIn('status', ['pending_provisioning','active','suspended'])->exists();

        if ($existing) {
            return back()->withErrors(['domain_name' => 'This domain is already attached to a Drypp hosting account.'])->withInput();
        }

        $selectedServer = null;
        foreach (HostingServer::where('is_active', true)->orderBy('id')->get() as $server) {
            $accountCount = $server->accounts()->whereIn('status', ['pending_provisioning','active','suspended'])->count();
            $unpaidReservations = ServiceOrder::where('hosting_server_id', $server->id)->where('status', 'awaiting_payment')->count();
            $packageLimit = (int) data_get($server->package_capacity, $hostingPackage->slug, $hostingPackage->max_accounts_per_server);
            $packageAccounts = $server->accounts()->where('hosting_package_id', $hostingPackage->id)->whereIn('status', ['pending_provisioning','active','suspended'])->count();
            $packageReservations = ServiceOrder::where('hosting_server_id', $server->id)->where('hosting_package_id', $hostingPackage->id)->where('status', 'awaiting_payment')->count();

            if (($accountCount + $unpaidReservations) < $server->max_accounts
                && ($packageAccounts + $packageReservations) < $packageLimit) {
                $selectedServer = $server;
                break;
            }
        }

        if (!$selectedServer) {
            return back()->withErrors(['domain_name' => 'No hosting server currently has capacity for this package. Please try again later or contact support.'])->withInput();
        }

        $amountUsd = (float) $hostingPackage->price_usd;
        if ($amountUsd <= 0) {
            return back()->withErrors(['domain_name' => 'This package has no valid USD price configured. Please contact support.'])->withInput();
        }

        $order = ServiceOrder::create([
            'user_id' => auth()->id(),
            'hosting_server_id' => $selectedServer->id,
            'hosting_package_id' => $hostingPackage->id,
            'order_number' => 'DRY-'.strtoupper(Str::random(8)),
            'service_type' => 'hosting',
            'domain_name' => $domain,
            'amount_ngn' => 0, // Legacy field; USD amount is stored in amount/currency.
            'amount' => $amountUsd,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'metadata' => [
                'free_website_included' => (bool) $hostingPackage->free_website_enabled,
                'domain_check' => 'not_checked_live',
                'price_snapshot' => ['amount' => number_format($amountUsd, 2, '.', ''), 'currency' => 'USD'],
            ],
        ]);

        return redirect()->route('checkout.show', $order)->with('status', 'Your order has been created. USD payment processing is not enabled until the payment gateway configuration is completed.');
    }
}
