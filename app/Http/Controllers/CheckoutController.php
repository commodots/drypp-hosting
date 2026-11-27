<?php

namespace App\Http\Controllers;

use App\Models\HostingPackage;
use App\Models\ServiceOrder;
use App\Services\CartService;
use App\Services\ServiceOrderExpiryService;
use Illuminate\Http\Request;
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

    public function pay(
        Request $request,
        ServiceOrder $order,
        ServiceOrderExpiryService $expiry,
        \App\Services\Payments\PaystackPaymentService $payments
    )
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

        try {
            $authorizationUrl = $payments->initializeForOrder(
                $order,
                $request->user()->email,
                route('checkout.callback')
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

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
