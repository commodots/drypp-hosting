<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = UserService::query()
            ->where('user_id', $request->user()->id)
            ->with(['order:id,order_number,status,currency,amount'])
            ->latest()
            ->paginate(15);

        $counts = UserService::query()
            ->where('user_id', $request->user()->id)
            ->selectRaw("service_type, COUNT(*) as total")
            ->groupBy('service_type')
            ->pluck('total', 'service_type');

        return view('user.services.index', compact('services', 'counts'));
    }
}
