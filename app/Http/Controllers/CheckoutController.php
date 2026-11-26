<?php

namespace App\Http\Controllers;

use App\Models\HostingPackage;
use App\Models\ServiceOrder;
use App\Services\CartService;
use App\Services\ServiceOrderExpiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function start(Request $request, CartService $carts)
    {
        $pending = $request->session()->get('drypp_pending_checkout');
        if (! is_array($pending) || empty($pending['domain'])) {
            return redirect()->route('cart.index');
        }

        $domain = strtolower(trim((string) $pending['domain']));
        try {
            $domainItem = $carts->addDomain($request, $domain);
            $packageId = (int) ($pending['hosting_package_id'] ?? 0);
            if ($packageId > 0) {
                $package = HostingPackage::query()->whereKey($packageId)->where('is_active', true)->firstOrFail();
                $carts->addHosting($request, $package, $domain, $domainItem->id);
            }
        } catch (RuntimeException $exception) {
            return redirect()->route('domains.search', ['domain' => $domain])
                ->withErrors(['checkout' => $exception->getMessage()]);
        }

        $request->session()->forget('drypp_pending_checkout');
        return redirect()->route('cart.index')->with('status', 'Your previous selection has been added to the cart. Review it before checkout.');
    }

    public function show(ServiceOrder $order, ServiceOrderExpiryService $expiry)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $expiry->expireIfDue($order->id);
        $order->refresh();
        $order->load(['hostingPackage', 'items.hostingPackage']);
        return view('checkout.show', compact('order'));
    }

    public function pay(Request $request, ServiceOrder $order, ServiceOrderExpiryService $expiry)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        if ($expiry->expireIfDue($order->id)) {
            return back()->withErrors(['payment' => 'This order expired before payment. Its reservations were released; return to your cart to place a new order.']);
        }
        $order->refresh();
        if ($order->status === 'paid') {
            return redirect()->route('my-services.index')->with('status', 'This order is already paid.');
        }
        if ($order->status !== 'awaiting_payment') {
            return back()->withErrors(['payment' => 'This order is not currently available for payment.']);
        }

        $currency = strtoupper($order->currency ?: 'NGN');
        $allowedCurrencies = array_map('strtoupper', config('services.paystack.currencies', ['NGN', 'USD']));
        if (! in_array($currency, $allowedCurrencies, true)) {
            return back()->withErrors(['payment' => 'This currency is not enabled for Paystack checkout.']);
        }
        $secret = config('services.paystack.secret_key');
        if (! $secret) {
            return back()->withErrors(['payment' => 'Paystack is not configured. Add PAYSTACK_SECRET_KEY to the server environment.']);
        }

        $majorAmount = (float) ($order->amount ?? 0);
        if ($majorAmount <= 0) {
            $majorAmount = (float) ($order->amount_ngn ?? 0);
        }
        $minorAmount = (int) round($majorAmount * 100);
        if ($minorAmount < 1) {
            return back()->withErrors(['payment' => 'This order has an invalid payment amount. Please contact support.']);
        }

        $existingAuthorizationUrl = data_get($order->metadata, 'payment.authorization_url');
        if ($order->payment_reference && is_string($existingAuthorizationUrl)
            && parse_url($existingAuthorizationUrl, PHP_URL_SCHEME) === 'https'
            && parse_url($existingAuthorizationUrl, PHP_URL_HOST) === 'checkout.paystack.com') {
            return redirect()->away($existingAuthorizationUrl);
        }

        $reference = $order->payment_reference ?: 'DRYPP-'.Str::upper(Str::random(20));
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($secret)->acceptJson()->timeout(20)->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->user()->email,
                'amount' => $minorAmount,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => route('checkout.callback'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $request->user()->id,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['payment' => 'We could not connect to Paystack. Please try again shortly.']);
        }

        $authorizationUrl = (string) $response->json('data.authorization_url', '');
        if (! $response->successful() || $response->json('status') !== true
            || parse_url($authorizationUrl, PHP_URL_SCHEME) !== 'https'
            || parse_url($authorizationUrl, PHP_URL_HOST) !== 'checkout.paystack.com') {
            report(new RuntimeException('Paystack initialization failed for order '.$order->order_number.': '.(string) $response->json('message', 'No checkout URL.')));
            return back()->withErrors(['payment' => 'Paystack could not start this payment. Confirm that '.$currency.' is enabled on your Paystack account, then try again.']);
        }

        $order->update([
            'payment_reference' => $reference,
            'metadata' => array_merge($order->metadata ?? [], [
                'payment' => [
                    'provider' => 'paystack',
                    'currency' => $currency,
                    'amount' => number_format($majorAmount, 2, '.', ''),
                    'amount_minor' => $minorAmount,
                    'authorization_url' => $authorizationUrl,
                    'initialized_at' => now()->toIso8601String(),
                ],
            ]),
        ]);

        return redirect()->away($authorizationUrl);
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
            return redirect()->route('checkout.show', $order)
                ->withErrors(['payment' => 'We could not verify this payment yet. If you were charged, do not pay again; contact support with your order number.']);
        }

        return redirect()->route('my-services.index')
            ->with('status', 'Payment verified. Your services are listed below and hosting provisioning has been queued. An order confirmation has been sent to your email address.');
    }
}
