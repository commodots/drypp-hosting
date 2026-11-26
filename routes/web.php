<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DomainSearchController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\User\CampaignController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ServiceController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\ContactGroupController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\TrackingController;
use App\Http\Controllers\User\AutomationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\HostingAdminController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\PorkbunDiagnosticController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\WebhookController;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/services', [PublicSiteController::class, 'services'])->name('services');
Route::get('/domains/search', [DomainSearchController::class, 'search'])->name('domains.search');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/domains', [CartController::class, 'storeDomain'])->name('cart.domains.store');
Route::post('/cart/hosting', [CartController::class, 'storeHosting'])->name('cart.hosting.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::patch('/cart/currency', [CartController::class, 'currency'])->name('cart.currency');
Route::post('/webhooks/sendgrid', [WebhookController::class, 'sendgrid'])->name('webhooks.sendgrid');
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('webhooks.paystack');
Route::get('/checkout/callback', [CheckoutController::class, 'callback'])->middleware('throttle:10,1')->name('checkout.callback');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
    Route::post('/campaigns/{campaign}/assign', [AdminCampaignController::class, 'assignSmtp'])->name('campaigns.assign');
    Route::get('/campaigns/{campaign}/toggle', [AdminCampaignController::class, 'toggleStatus'])->name('campaigns.toggle');
    Route::resource('packages', AdminPackageController::class);
    Route::resource('smtps', SmtpController::class);
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/override/{user}', [PaymentController::class, 'override'])->name('payments.override');
    Route::get('/hosting', [HostingAdminController::class, 'index'])->name('hosting.index');
    Route::get('/hosting/packages', [HostingAdminController::class, 'packages'])->name('hosting.packages');
    Route::get('/hosting/servers', [HostingAdminController::class, 'servers'])->name('hosting.servers');
    Route::get('/hosting/accounts', [OperationsController::class, 'hostingAccounts'])->name('hosting.accounts');
    Route::get('/provisioning', [OperationsController::class, 'provisioningQueue'])->name('provisioning.index');
    Route::post('/provisioning/{account}/retry', [HostingAdminController::class, 'retryProvisioning'])
        ->middleware('throttle:10,1')
        ->name('provisioning.retry');
    Route::get('/domain-pricing', [HostingAdminController::class, 'domainPricing'])->name('domain-pricing.index');
    Route::get('/domains', [OperationsController::class, 'domains'])->name('domains.index');
    Route::get('/customers', [OperationsController::class, 'customers'])->name('customers.index');
    Route::get('/support-renewals', [OperationsController::class, 'supportAndRenewals'])->name('support-renewals.index');
    Route::post('/hosting/servers', [HostingAdminController::class, 'storeServer'])->name('hosting.servers.store');
    Route::patch('/hosting/servers/{server}', [HostingAdminController::class, 'updateServer'])->name('hosting.servers.update');
    Route::patch('/hosting/servers/{server}/toggle', [HostingAdminController::class, 'toggleServer'])->name('hosting.servers.toggle');
    Route::post('/hosting/packages', [HostingAdminController::class, 'storePackage'])->name('hosting.packages.store');
    Route::patch('/hosting/packages/{hostingPackage}', [HostingAdminController::class, 'updatePackage'])->name('hosting.packages.update');
    Route::patch('/hosting/packages/{hostingPackage}/toggle', [HostingAdminController::class, 'togglePackage'])->name('hosting.packages.toggle');
    Route::patch('/hosting/packages/{hostingPackage}/whm-mapping', [HostingAdminController::class, 'updateWhmMapping'])->name('hosting.packages.whm-mapping');
    Route::post('/hosting/domain-pricing', [HostingAdminController::class, 'storeDomainPricing'])->name('hosting.domain-pricing.store');
    Route::post('/porkbun/diagnostic', PorkbunDiagnosticController::class)->middleware('throttle:5,1')->name('porkbun.diagnostic');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-services', [ServiceController::class, 'index'])->name('my-services.index');
    Route::get('/hosting', [HostingController::class, 'index'])->name('hosting.index');
    Route::post('/hosting/packages/{hostingPackage}/order', [HostingController::class, 'order'])->name('hosting.order');
    Route::get('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');

    Route::post('/campaigns/step-two', [CampaignController::class, 'stepTwo'])->name('campaigns.stepTwo');
    Route::post('/campaigns/step-three', [CampaignController::class, 'stepThree'])->name('campaigns.stepThree');
    Route::get('/campaigns/step-two', fn () => redirect()->route('campaigns.create'));
    Route::get('/campaigns/step-three', fn () => redirect()->route('campaigns.create'));
    Route::resource('campaigns', CampaignController::class)->only(['index','create','store','show','edit','update','destroy']);
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    Route::post('/automation/{automation}/steps', [AutomationController::class, 'addStep'])->name('automation.addStep');
    Route::put('/automation/steps/{step}', [AutomationController::class, 'updateStep'])->name('automation.updateStep');
    Route::delete('/automation/steps/{step}', [AutomationController::class, 'destroyStep'])->name('automation.destroyStep');
    Route::resource('automation', AutomationController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/toggle', [ReportController::class, 'toggleAutoReport'])->name('reports.toggle');
    Route::get('/reports/export/{campaign}', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/leads', fn () => view('leads.index'))->name('leads.index');
    Route::get('/billing', fn () => view('user.billing.index'))->name('billing.index');
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');
    Route::resource('groups', ContactGroupController::class);
    Route::get('/track/open/{uuid}', [TrackingController::class, 'open'])->name('track.open');
    Route::get('/track/click/{id}', [TrackingController::class, 'click'])->name('track.click');
    Route::get('/settings', fn () => view('settings.index'))->name('settings.index');
});

require __DIR__.'/auth.php';
