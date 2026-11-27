<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        if ($request->filled('package')) $request->session()->put('hosting_selected_package', (int) $request->query('package'));
        return view('auth.login');
    }

    public function store(LoginRequest $request, CartService $carts): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $cart = $carts->mergeGuestCart($request, $request->user());
        if ($request->user()->role === 'admin') return redirect()->intended(route('admin.dashboard'));
        if ($request->session()->has('drypp_pending_checkout')) return redirect()->route('checkout.start');
        if ($cart->items->isNotEmpty()) return redirect()->route('cart.index');
        $packageId = $request->session()->pull('hosting_selected_package');
        if ($packageId) return redirect()->route('hosting.index', ['package' => $packageId]).'#plans';
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
