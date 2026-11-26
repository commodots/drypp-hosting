<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campaign;
use App\Models\SmtpServer;
use App\Models\HostingAccount;
use App\Models\ServiceOrder;

class DashboardController extends Controller
{
  public function index()
  {
    return view('admin.dashboard', [
      'totalUsers' => User::where('role', 'user')->count(),
      'activeCampaigns' => Campaign::whereIn('status', ['queued', 'sending'])->count(),
      'emailsToday' => SmtpServer::sum('sent_today'),
      'smtpHealth' => SmtpServer::where('active', true)->count(),
      'smtps' => SmtpServer::all(),
      'hostingAccounts' => HostingAccount::count(),
      'provisioningFailures' => HostingAccount::where('status', 'provisioning_failed')->count(),
      'awaitingServiceOrders' => ServiceOrder::where('status', 'awaiting_payment')->count(),
    ]);
  }
}
