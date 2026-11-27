<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\HostingPackage;
use App\Models\HostingServer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MarketplaceCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_merges_on_login_and_creates_one_multi_item_order_at_server_prices(): void
    {
        config()->set('services.paystack.currencies', ['USD', 'NGN']);
        Http::fake([
            'https://api.porkbun.com/api/json/v3/domain/checkDomain/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['avail' => 'yes', 'price' => 10],
            ]),
            'https://api.porkbun.com/api/json/v3/pricing/get' => Http::response([
                'status' => 'SUCCESS',
                'pricing' => ['com' => ['registration' => ['price' => 10]]],
            ]),
        ]);

        $package = HostingPackage::create([
            'name' => 'Standard',
            'slug' => 'standard-hosting-cart-test',
            'description' => 'Standard hosting',
            'price_ngn' => 45000,
            'price_usd' => 30,
            'billing_cycle' => 'yearly',
            'disk_space_gb' => 3,
            'bandwidth_gb' => 30,
            'email_accounts' => 10,
            'addon_domains' => 1,
            'databases' => 2,
            'max_accounts_per_server' => 10,
            'is_active' => true,
        ]);
        HostingServer::create(['name' => 'Shared 01', 'max_accounts' => 10]);

        $this->post(route('cart.domains.store'), ['domain' => 'first.test', 'price' => 0.01])
            ->assertRedirect(route('cart.index'));
        $this->post(route('cart.domains.store'), ['domain' => 'second.test'])
            ->assertRedirect(route('cart.index'));

        $this->patch(route('cart.currency'), ['currency' => 'NGN'])->assertRedirect();
        $guestCart = Cart::query()->whereNull('user_id')->where('status', 'active')->firstOrFail();
        $this->assertSame('NGN', $guestCart->currency);
        $this->assertSame('21000.00', number_format((float) $guestCart->items()->first()->unit_price, 2, '.', ''));
        $this->assertSame(1500.0, (float) data_get($guestCart->items()->first()->price_metadata, 'exchange_rate'));

        $this->patch(route('cart.currency'), ['currency' => 'USD'])->assertRedirect();
        $guestCart = Cart::query()->whereNull('user_id')->where('status', 'active')->firstOrFail();
        $this->assertSame(64, strlen($guestCart->token));
        $this->assertSame(2, $guestCart->items()->where('item_type', 'domain_registration')->count());
        $this->assertSame('14.00', number_format((float) $guestCart->items()->first()->unit_price, 2, '.', ''));

        $customer = User::factory()->create(['password' => 'password']);
        $this->post(route('login'), ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('cart.index'));

        $userCart = Cart::query()->where('user_id', $customer->id)->where('status', 'active')->firstOrFail();
        $this->assertSame(2, $userCart->items()->where('item_type', 'domain_registration')->count());
        $firstDomain = $userCart->items()->where('item_type', 'domain_registration')
            ->where('domain_name', 'first.test')->firstOrFail();

        $this->post(route('cart.hosting.store'), [
            'domain_item_id' => $firstDomain->id,
            'hosting_package_id' => $package->id,
        ])->assertRedirect(route('cart.index'));

        $this->post(route('cart.checkout'))
            ->assertRedirect(route('checkout.show', ServiceOrder::query()->firstOrFail()));

        $order = ServiceOrder::query()->firstOrFail();
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertSame('USD', $order->currency);
        $this->assertSame('58.00', number_format((float) $order->amount, 2, '.', ''));
        $this->assertSame(3, $order->items()->count());
        $this->assertSame(2, $order->items()->where('item_type', 'domain_registration')->count());

        $hostingItem = $order->items()->where('item_type', 'hosting')->firstOrFail();
        $domainOrderItem = $order->items()->where('item_type', 'domain_registration')->firstOrFail();
        $this->assertSame($domainOrderItem->id, $hostingItem->parent_item_id);
        $this->assertSame('first.test', $hostingItem->domain_name);
        $this->assertNotNull($hostingItem->hosting_server_id);
        $this->assertSame('USD', data_get($hostingItem->item_metadata, 'base_currency'));
        $this->assertSame('checkout_pending', $userCart->fresh()->status);
        $this->assertSame($order->id, $userCart->fresh()->service_order_id);
    }

    public function test_removing_a_domain_removes_its_attached_hosting_item(): void
    {
        $customer = User::factory()->create();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'token' => str_repeat('t', 64),
            'currency' => 'USD',
            'status' => 'active',
        ]);
        $domain = CartItem::create([
            'cart_id' => $cart->id,
            'item_type' => 'domain_registration',
            'domain_name' => 'example.test',
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 14,
            'line_total' => 14,
            'currency' => 'USD',
            'price_metadata' => ['base_price_usd' => 14, 'base_currency' => 'USD'],
        ]);
        $package = HostingPackage::create([
            'name' => 'Standard',
            'slug' => 'standard-hosting-removal-test',
            'price_ngn' => 45000,
            'price_usd' => 30,
            'billing_cycle' => 'yearly',
            'disk_space_gb' => 3,
            'bandwidth_gb' => 30,
            'email_accounts' => 10,
            'addon_domains' => 1,
            'databases' => 2,
            'max_accounts_per_server' => 10,
            'is_active' => true,
        ]);
        $hosting = CartItem::create([
            'cart_id' => $cart->id,
            'item_type' => 'hosting',
            'domain_name' => 'example.test',
            'hosting_package_id' => $package->id,
            'parent_item_id' => $domain->id,
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 30,
            'line_total' => 30,
            'currency' => 'USD',
            'price_metadata' => ['base_price_usd' => 30, 'base_currency' => 'USD'],
        ]);

        $this->actingAs($customer)->withSession(['drypp_cart_token' => $cart->token])
            ->delete(route('cart.items.destroy', $domain))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', ['id' => $domain->id]);
        $this->assertDatabaseMissing('cart_items', ['id' => $hosting->id]);
    }

    public function test_guest_cart_merges_when_the_visitor_registers(): void
    {
        config()->set('services.paystack.currencies', ['USD', 'NGN']);
        Notification::fake();
        Http::fake([
            'https://api.porkbun.com/api/json/v3/domain/checkDomain/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['avail' => 'yes', 'price' => 12],
            ]),
            'https://api.porkbun.com/api/json/v3/pricing/get' => Http::response([
                'status' => 'SUCCESS',
                'pricing' => ['com' => ['registration' => ['price' => 12]]],
            ]),
        ]);

        $this->post(route('cart.domains.store'), ['domain' => 'register.test'])
            ->assertRedirect(route('cart.index'));
        $this->post(route('register'), [
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
            'password' => 'Secure!Password123',
            'password_confirmation' => 'Secure!Password123',
        ])->assertRedirect(route('cart.index'));

        $user = User::query()->where('email', 'new-customer@example.test')->firstOrFail();
        $this->assertSame(1, Cart::query()->where('user_id', $user->id)->where('status', 'active')->firstOrFail()
            ->items()->where('item_type', 'domain_registration')->count());
    }

    public function test_successful_payment_converts_the_linked_cart(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        config()->set('services.paystack.currencies', ['USD', 'NGN']);
        Http::fake([
            'https://api.paystack.co/transaction/verify/DRYPP-TEST-REFERENCE' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 1400,
                    'currency' => 'USD',
                    'reference' => 'DRYPP-TEST-REFERENCE',
                    'customer' => ['email' => 'paid-customer@example.test'],
                ],
            ]),
        ]);
        Mail::fake();

        $user = User::factory()->create(['email' => 'paid-customer@example.test']);
        $order = ServiceOrder::create([
            'user_id' => $user->id,
            'order_number' => 'DRY-CART-PAID',
            'service_type' => 'marketplace',
            'domain_name' => 'paid.test',
            'amount_ngn' => 0,
            'amount' => 14,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'payment_reference' => 'DRYPP-TEST-REFERENCE',
            'metadata' => [],
        ]);
        $cart = Cart::create([
            'user_id' => $user->id,
            'token' => str_repeat('c', 64),
            'currency' => 'USD',
            'status' => 'checkout_pending',
            'service_order_id' => $order->id,
        ]);
        $order->update(['metadata' => ['cart_id' => $cart->id]]);
        ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'item_type' => 'domain_registration',
            'domain_name' => 'paid.test',
            'provider' => 'porkbun',
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 14,
            'line_total' => 14,
            'currency' => 'USD',
            'item_metadata' => [
                'base_price' => 14,
                'base_currency' => 'USD',
                'selling_price' => 14,
                'currency' => 'USD',
            ],
        ]);

        $this->actingAs($user)->get(route('checkout.callback', ['reference' => 'DRYPP-TEST-REFERENCE']))
            ->assertRedirect(route('my-services.index'));

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(
            'pending_registration',
            $order->items()->where('item_type', 'domain_registration')->value('fulfillment_status')
        );
        $this->assertSame('converted', $cart->fresh()->status);
    }
}
