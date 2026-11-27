<?php

namespace Tests\Feature;

use App\Jobs\ProvisionHostingAccount;
use App\Models\HostingAccount;
use App\Models\HostingPackage;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_admin_pages_render_the_shared_administration_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pages = [
            'admin.dashboard',
            'admin.hosting.index',
            'admin.hosting.packages',
            'admin.hosting.servers',
            'admin.domain-pricing.index',
            'admin.payments.index',
            'admin.customers.index',
            'admin.domains.index',
            'admin.hosting.accounts',
            'admin.provisioning.index',
            'admin.support-renewals.index',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)
                ->get(route($page))
                ->assertOk()
                ->assertSee('id="sidebar"', false)
                ->assertSee('class="admin-sidebar"', false)
                ->assertSee('aria-label="Admin navigation"', false)
                ->assertDontSee('aria-label="Customer navigation"', false);
        }
    }

    public function test_customer_pages_show_customer_navigation_instead_of_admin_navigation(): void
    {
        $customer = User::factory()->create(['role' => 'user']);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Customer navigation"', false)
            ->assertSee('Email campaigns')
            ->assertSee('My Services')
            ->assertDontSee('aria-label="Admin navigation"', false);
    }

    public function test_admin_can_retry_failed_provisioning_for_a_paid_order(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $package = HostingPackage::create([
            'name' => 'Standard',
            'slug' => 'standard-hosting-admin-paid-test',
            'price_ngn' => 0,
            'price_usd' => 30,
            'billing_cycle' => 'yearly',
            'disk_space_gb' => 3,
            'bandwidth_gb' => 30,
            'email_accounts' => 10,
            'addon_domains' => 1,
            'databases' => 2,
            'max_accounts_per_server' => 50,
        ]);
        $server = HostingServer::create(['name' => 'Shared 01']);
        $order = ServiceOrder::create([
            'user_id' => $customer->id,
            'hosting_server_id' => $server->id,
            'hosting_package_id' => $package->id,
            'order_number' => 'DRY-TEST-001',
            'service_type' => 'hosting',
            'domain_name' => 'example.test',
            'amount_ngn' => 0,
            'amount' => 30,
            'currency' => 'USD',
            'status' => 'paid',
        ]);
        $account = HostingAccount::create([
            'user_id' => $customer->id,
            'hosting_server_id' => $server->id,
            'hosting_package_id' => $package->id,
            'service_order_id' => $order->id,
            'domain_name' => 'example.test',
            'status' => 'provisioning_failed',
            'provider_metadata' => ['last_error' => 'Provider timeout'],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.provisioning.retry', $account))
            ->assertRedirect();

        $this->assertDatabaseHas('hosting_accounts', [
            'id' => $account->id,
            'status' => 'pending_provisioning',
        ]);
        Queue::assertPushed(ProvisionHostingAccount::class, fn ($job) => $job->hostingAccountId === $account->id);
    }

    public function test_admin_cannot_retry_provisioning_for_an_unpaid_order(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $package = HostingPackage::create([
            'name' => 'Standard',
            'slug' => 'standard-hosting-admin-unpaid-test',
            'price_ngn' => 0,
            'price_usd' => 30,
        ]);
        $server = HostingServer::create(['name' => 'Shared 01']);
        $order = ServiceOrder::create([
            'user_id' => $customer->id,
            'hosting_server_id' => $server->id,
            'hosting_package_id' => $package->id,
            'order_number' => 'DRY-TEST-002',
            'amount_ngn' => 0,
            'amount' => 30,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
        ]);
        $account = HostingAccount::create([
            'user_id' => $customer->id,
            'hosting_server_id' => $server->id,
            'hosting_package_id' => $package->id,
            'service_order_id' => $order->id,
            'domain_name' => 'unpaid.test',
            'status' => 'provisioning_failed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.provisioning.retry', $account))
            ->assertStatus(409);

        $this->assertDatabaseHas('hosting_accounts', [
            'id' => $account->id,
            'status' => 'provisioning_failed',
        ]);
        Queue::assertNothingPushed();
    }
}
