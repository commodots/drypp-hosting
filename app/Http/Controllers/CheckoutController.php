<?php

namespace App\Http\Controllers;

use App\Models\HostingAccount;
use App\Models\HostingPackage;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Services\Domain\PorkbunClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    /** Create the authenticated customer's combined domain + hosting order from server-side pending checkout data. */
    public function start(Request $request, PorkbunClient $porkbun)
    {
        $pending = $request->session()->get('drypp_pending_checkout');
        if (!is_array($pending) || empty($pending['domain'])) {
            return redirect()->route('domains.search')->withErrors([
                'checkout' => 'Your pending cart could not be found. Please search for a domain and select your services again.',
            ]);
        }

        $domain = strtolower(trim((string) $pending['domain']));
        if (!preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
            return redirect()->route('domains.search')->withErrors(['checkout' => 'The selected domain name is invalid. Please search again.']);
        }

        try {
            $availability = $porkbun->checkAvailability($domain);
            $response = data_get($availability, 'json.response', []);
            $avail = strtolower((string) data_get($response, 'avail', ''));
            if (!$availability['successful'] || $avail !== 'yes' || (bool) data_get($response, 'sandbox', false)) {
                throw new RuntimeException('The registrar did not confirm this domain as available in live mode.');
            }

            $pricing = $porkbun->pricing();
            if (!$pricing['successful']) {
                throw new RuntimeException('Live domain pricing could not be retrieved.');
            }
            $tld = strtolower(substr(strrchr($domain, '.'), 1));
            $catalogue = data_get($pricing, 'json.response', []);
            $tldPricing = $catalogue[$tld] ?? null;
            $registrarPrice = is_numeric(data_get($tldPricing, 'registration'))
                ? (float) data_get($tldPricing, 'registration')
                : (is_numeric(data_get($tldPricing, 'registration.price'))
                    ? (float) data_get($tldPricing, 'registration.price')
                    : (is_numeric(data_get($response, 'price')) ? (float) data_get($response, 'price') : null));
            if ($registrarPrice === null || $registrarPrice <= 0) {
                throw new RuntimeException('A valid live registration price was not returned for this domain extension.');
            }
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('domains.search', ['domain' => $domain])->withErrors([
                'checkout' => 'We could not confirm live availability and pricing for ' . $domain . '. Please search again or contact support.',
            ]);
        }

        $markup = max(0, (float) config('services.porkbun.markup_percent', 40));
        $domainPrice = round($registrarPrice * (1 + $markup / 100), 2);
        $package = null;
        $server = null;
        $hostingPrice = 0.0;
        $packageId = (int) ($pending['hosting_package_id'] ?? 0);

        if ($packageId > 0) {
            $package = HostingPackage::whereKey($packageId)->where('is_active', true)->first();
            if (!$package) {
                return redirect()->route('domains.search', ['domain' => $domain])->withErrors(['checkout' => 'The selected hosting package is no longer available. Please select another package.']);
            }
            $hostingPrice = (float) $package->price_usd;
            if ($hostingPrice <= 0) {
                return redirect()->route('domains.search', ['domain' => $domain])->withErrors(['checkout' => 'The selected hosting package does not have a valid USD price.']);
            }

            foreach (HostingServer::where('is_active', true)->orderBy('id')->get() as $candidateServer) {
                $accountCount = $candidateServer->accounts()->whereIn('status', ['pending_provisioning', 'active', 'suspended'])->count();
                $unpaidReservations = ServiceOrder::where('hosting_server_id', $candidateServer->id)->where('status', 'awaiting_payment')->count();
                $packageLimit = (int) data_get($candidateServer->package_capacity, $package->slug, $package->max_accounts_per_server);
                $packageAccounts = $candidateServer->accounts()->where('hosting_package_id', $package->id)->whereIn('status', ['pending_provisioning', 'active', 'suspended'])->count();
                $packageReservations = ServiceOrder::where('hosting_server_id', $candidateServer->id)->where('hosting_package_id', $package->id)->where('status', 'awaiting_payment')->count();
                if (($accountCount + $unpaidReservations) < $candidateServer->max_accounts && ($packageAccounts + $packageReservations) < $packageLimit) {
                    $server = $candidateServer;
                    break;
                }
            }
            if (!$server) {
                return redirect()->route('domains.search', ['domain' => $domain])->withErrors(['checkout' => 'No hosting server currently has capacity for this package. Please contact support or try again later.']);
            }
        }

        $total = round($domainPrice + $hostingPrice, 2);
        $order = DB::transaction(function () use ($domain, $domainPrice, $registrarPrice, $markup, $package, $hostingPrice, $server, $total) {
            $order = ServiceOrder::create([
                'user_id' => auth()->id(),
                'hosting_server_id' => $server?->id,
                'hosting_package_id' => $package?->id,
                'order_number' => 'DRY-' . Str::upper(Str::random(10)),
                'service_type' => $package ? 'domain_and_hosting' : 'domain_registration',
                'domain_name' => $domain,
                'amount_ngn' => 0,
                'amount' => $total,
                'currency' => 'USD',
                'status' => 'awaiting_payment',
                'metadata' => [
                    'checkout_source' => 'domain_search',
                    'price_snapshot' => ['domain_usd' => $domainPrice, 'hosting_usd' => $hostingPrice, 'total_usd' => $total, 'currency' => 'USD'],
                    'registrar' => ['provider' => 'porkbun', 'base_registration_usd' => $registrarPrice, 'markup_percent' => $markup, 'live_checked_at' => now()->toIso8601String()],
                    'hosting_provider' => $package ? 'verpex' : null,
                ],
            ]);

            $domainItem = ServiceOrderItem::create([
                'service_order_id' => $order->id,
                'item_type' => 'domain_registration',
                'domain_name' => $domain,
                'provider' => 'porkbun',
                'quantity' => 1,
                'billing_period' => 'yearly',
                'unit_price' => $domainPrice,
                'line_total' => $domainPrice,
                'currency' => 'USD',
                'item_metadata' => ['registrar_base_price_usd' => $registrarPrice, 'markup_percent' => $markup],
            ]);

            if ($package) {
                ServiceOrderItem::create([
                    'service_order_id' => $order->id,
                    'item_type' => 'hosting',
                    'domain_name' => $domain,
                    'hosting_package_id' => $package->id,
                    'parent_item_id' => $domainItem->id,
                    'provider' => 'verpex',
                    'provider_product_id' => $package->slug,
                    'quantity' => 1,
                    'billing_period' => $package->billing_cycle,
                    'unit_price' => $hostingPrice,
                    'line_total' => $hostingPrice,
                    'currency' => 'USD',
                    'item_metadata' => ['package_name' => $package->name],
                ]);
            }
            return $order;
        });

        $request->session()->forget('drypp_pending_checkout');
        return redirect()->route('checkout.show', $order)->with('status', 'Your order has been created. Review the details and continue to secure payment.');
    }

    public function show(ServiceOrder $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load(['hostingPackage', 'items']);
        return view('checkout.show', compact('order'));
    }

    public function pay(Request $request, ServiceOrder $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        if ($order->status === 'paid') 
            return redirect()->route('my-services.index')->with('status', 'This order is already paid.');
        if ($order->status !== 'awaiting_payment') 
            return back()->withErrors(['payment' => 'This order is not currently available for payment.']);
        $currency = strtoupper($order->currency ?: 'NGN');
        $allowedCurrencies = array_map('strtoupper', config('services.paystack.currencies', ['NGN', 'USD']));
        if (!in_array($currency, $allowedCurrencies, true)) 
            return back()->withErrors(['payment' => 'This currency is not enabled for Paystack checkout.']);
        $secret = config('services.paystack.secret_key');
        if (!$secret) 
            return back()->withErrors(['payment' => 'Paystack is not configured. Add PAYSTACK_SECRET_KEY to the server environment.']);
        $majorAmount = (float) ($order->amount ?? 0);
        if ($majorAmount <= 0) $majorAmount = (float) ($order->amount_ngn ?? 0);
        $minorAmount = (int) round($majorAmount * 100);
        if ($minorAmount < 1) 
            return back()->withErrors(['payment' => 'This order has an invalid payment amount. Please contact support.']);
        $reference = 'DRYPP-' . Str::upper(Str::random(20));
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($secret)->acceptJson()->timeout(20)->post('https://api.paystack.co/transaction/initialize', [
                'email' => auth()->user()->email,
                'amount' => $minorAmount,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => route('checkout.callback'),
                'metadata' => ['order_id' => $order->id, 'order_number' => $order->order_number, 'user_id' => auth()->id()],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['payment' => 'We could not connect to Paystack. Please try again shortly.']);
        }
        if (!$response->successful() || $response->json('status') !== true || !$response->json('data.authorization_url')) {
            report(new RuntimeException('Paystack initialization failed for order ' . $order->order_number . ': ' . (string) $response->json('message', 'No checkout URL.')));
            return back()->withErrors(['payment' => 'Paystack could not start this payment. Confirm that ' . $currency . ' is enabled on your Paystack account, then try again.']);
        }
        $order->update(['payment_reference' => $reference, 'metadata' => array_merge($order->metadata ?? [], ['payment' => ['provider' => 'paystack', 'currency' => $currency, 'amount' => number_format($majorAmount, 2, '.', ''), 'amount_minor' => $minorAmount, 'initialized_at' => now()->toIso8601String()]])]);
        return redirect()->away($response->json('data.authorization_url'));
    }

    public function callback(Request $request, \App\Services\Payments\PaystackPaymentService $payments)
    {
        $reference = (string) $request->query('reference', $request->query('trxref', ''));
        abort_if($reference === '', 400, 'Missing payment reference.');
        $order = ServiceOrder::where('payment_reference', $reference)->firstOrFail();
        try {
            $payments->verifyAndMarkPaid($reference);
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('checkout.show', $order)->withErrors(['payment' => 'We could not verify this payment yet. If you were charged, do not pay again; contact support with your order number.']);
        }
        return redirect()->route('my-services.index')->with('status', 'Payment verified. Your services are listed below and hosting provisioning has been queued. An order confirmation has been sent to your email address.');
    }
}
