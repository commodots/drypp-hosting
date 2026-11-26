<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\HostingPackage;
use App\Services\CartCheckoutService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, CartService $carts): View
    {
        $cart = $carts->current($request)->load('items.hostingPackage', 'items.parentItem');
        $packages = HostingPackage::query()->where('is_active', true)->orderBy('price_usd')->get();

        return view('cart.index', [
            'cart' => $cart,
            'packages' => $packages,
            'totals' => $carts->totals($cart),
            'supportedCurrencies' => $carts->supportedCurrencies(),
        ]);
    }

    public function storeDomain(Request $request, CartService $carts): RedirectResponse
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
        ]);

        try {
            $carts->addDomain($request, $data['domain']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['cart' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('cart.index')->with('status', 'The domain was added to your cart.');
    }

    public function storeHosting(Request $request, CartService $carts): RedirectResponse
    {
        $data = $request->validate([
            'hosting_package_id' => ['required', 'integer', Rule::exists('hosting_packages', 'id')->where('is_active', true)],
            'domain' => ['nullable', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
            'domain_item_id' => ['nullable', 'integer', 'exists:cart_items,id'],
        ]);
        if (empty($data['domain']) && empty($data['domain_item_id'])) {
            return back()->withErrors(['domain' => 'Enter a domain name or select a domain already in your cart.']);
        }

        try {
            $package = HostingPackage::findOrFail($data['hosting_package_id']);
            $carts->addHosting(
                $request,
                $package,
                (string) ($data['domain'] ?? ''),
                isset($data['domain_item_id']) ? (int) $data['domain_item_id'] : null
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['cart' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('cart.index')->with('status', 'The hosting package was added to your cart.');
    }

    public function update(Request $request, CartItem $cartItem, CartService $carts): RedirectResponse
    {
        $data = $request->validate([
            'hosting_package_id' => ['required', 'integer', Rule::exists('hosting_packages', 'id')->where('is_active', true)],
        ]);

        try {
            $carts->updateHosting($request, $cartItem, HostingPackage::findOrFail($data['hosting_package_id']));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['cart' => $exception->getMessage()]);
        }

        return back()->with('status', 'Your hosting selection was updated.');
    }

    public function destroy(Request $request, CartItem $cartItem, CartService $carts): RedirectResponse
    {
        $carts->removeItem($request, $cartItem);
        return back()->with('status', 'The item was removed from your cart.');
    }

    public function currency(Request $request, CartService $carts): RedirectResponse
    {
        $data = $request->validate(['currency' => ['required', 'string', Rule::in($carts->supportedCurrencies())]]);

        try {
            $carts->changeCurrency($request, $data['currency']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['currency' => $exception->getMessage()]);
        }

        return back()->with('status', 'Cart currency updated. Prices are converted from USD using the configured rate.');
    }

    public function checkout(Request $request, CartService $carts, CartCheckoutService $checkout): RedirectResponse
    {
        $cart = $carts->current($request);
        if ($cart->service_order_id) {
            return redirect()->route('checkout.show', $cart->service_order_id);
        }

        try {
            $result = $checkout->createOrder($request, $cart);
        } catch (RuntimeException $exception) {
            return redirect()->route('cart.index')->withErrors(['checkout' => $exception->getMessage()]);
        }

        if ($result['prices_changed']) {
            return redirect()->route('cart.index')
                ->with('status', 'Prices or billing details changed after a live recheck. Review the updated total before continuing.');
        }

        return redirect()->route('checkout.show', $result['order'])
            ->with('status', 'Your order is ready. Review it before paying.');
    }
}
