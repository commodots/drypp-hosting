<?php

namespace App\Http\Controllers;

use App\Models\HostingAccount;
use App\Models\HostingPackage;
use App\Models\ServiceOrder;
use App\Services\CartService;
use Illuminate\Http\Request;
use RuntimeException;

class HostingController extends Controller
{
    public function index(Request $request, CartService $carts)
    {
        $packages = HostingPackage::where('is_active', true)->orderBy('price_usd')->orderBy('price_ngn')->get();
        $accounts = HostingAccount::with(['package','server'])->where('user_id', auth()->id())->latest()->get();
        $orders = ServiceOrder::with('hostingPackage')->where('user_id', auth()->id())->latest()->take(10)->get();
        $cartCount = $carts->current($request)->items()->count();
        return view('hosting.index', compact('packages','accounts','orders','cartCount'));
    }

    public function order(Request $request, HostingPackage $hostingPackage, CartService $carts)
    {
        abort_unless($hostingPackage->is_active, 404);

        $validated = $request->validate([
            'domain_name' => ['required','string','max:253','regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
        ]);

        try {
            $carts->addHosting($request, $hostingPackage, strtolower(trim($validated['domain_name'])));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['domain_name' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('cart.index')->with('status', 'Hosting was added to your cart.');
    }
}
