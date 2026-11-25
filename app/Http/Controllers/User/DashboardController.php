<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['subscription.package', 'campaigns']);
        $hostingCount = 0;
        $domainCount = 0;
        $pendingOrders = 0;
        $activeServices = collect();

        if (Schema::hasTable('user_services')) {
            $serviceQuery = $user->userServices();
            $hostingCount = (clone $serviceQuery)->where('service_type', 'hosting')->count();
            $domainCount = (clone $serviceQuery)->where('service_type', 'domain')->count();
            $activeServices = (clone $serviceQuery)->latest()->take(5)->get();
        } elseif (Schema::hasTable('service_orders')) {
            $orders = DB::table('service_orders')->where('user_id', $user->id);
            $pendingOrders = (clone $orders)->whereIn('status', ['pending', 'unpaid', 'awaiting_payment'])->count();
            $hostingCount = (clone $orders)->whereIn('status', ['active', 'completed'])->count();
        }

        if (Schema::hasTable('service_orders')) {
            $pendingOrders = DB::table('service_orders')->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'unpaid', 'awaiting_payment'])->count();
        }

        $chartData = Campaign::where('user_id', $user->id)
            ->whereIn('status', ['sending', 'completed'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('SUM(sent) as total_sent'))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        return view('user.dashboard', [
            'user' => $user,
            'activePackage' => $user->subscription->package->name ?? 'No Plan',
            'emailsRemaining' => $user->emailsRemaining(),
            'leadsRemaining' => $user->leadsRemaining(),
            'runningCampaigns' => $user->campaigns()->whereIn('status', ['queued', 'sending'])->count(),
            'recentCampaigns' => $user->campaigns()->latest()->take(5)->get(),
            'chartData' => $chartData,
            'hostingCount' => $hostingCount,
            'domainCount' => $domainCount,
            'pendingOrders' => $pendingOrders,
            'activeServices' => $activeServices,
        ]);
    }
}
