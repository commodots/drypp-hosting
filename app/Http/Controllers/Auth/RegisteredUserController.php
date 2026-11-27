<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\CartService;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        if ($request->filled('package')) $request->session()->put('hosting_selected_package', (int) $request->query('package'));
        return view('auth.register');
    }

    public function store(Request $request, CartService $carts): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            'pending_domain' => ['nullable', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
            'pending_hosting_package_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->filled('pending_domain')) {
            $request->session()->put('drypp_pending_checkout', [
                'domain' => strtolower($request->input('pending_domain')),
                'hosting_package_id' => $request->filled('pending_hosting_package_id') ? (int) $request->input('pending_hosting_package_id') : null,
            ]);
        }

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $cart = $carts->mergeGuestCart($request, $user);

        if ($request->session()->has('drypp_pending_checkout')) return redirect()->route('checkout.start');
        if ($cart->items->isNotEmpty()) return redirect()->route('cart.index');
        $packageId = $request->session()->pull('hosting_selected_package');
        if ($packageId) return redirect()->route('hosting.index', ['package' => $packageId]).'#plans';
        return redirect()->route('dashboard');
    }
}
