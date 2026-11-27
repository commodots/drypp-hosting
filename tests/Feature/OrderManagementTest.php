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
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_tracks_each_item_and_only_completes_when_every_item_is_fulfilled(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder($user, 'DRY-ITEM-STATUS');

        $domain = $this->createOrderItem($order, [
            'item_type' => 'domain_registration',
            'domain_name' => 'first.test',
            'fulfillment_status' => 'pending_registration',
            'domain_reservation_key' => hash('sha256', 'first.test'),
        ]);
        $hosting = $this->createOrderItem($order, [
            'item_type' => 'hosting',
            'domain_name' => 'first.test',
            'parent_item_id' => $domain->id,
            'fulfillment_status' => 'provisioning',
        ]);
        $secondDomain = $this->createOrderItem($order, [
            'item_type' => 'domain_registration',
            'domain_name' => 'second.test',
            'fulfillment_status' => 'pending_registration',
            'domain_reservation_key' => hash('sha256', 'second.test'),
        ]);

        $domain->setFulfillmentStatus('fulfilled');
        $secondDomain->setFulfillmentStatus('registration_failed');

        $this->assertSame('partially_fulfilled', $order->fresh()->status);
        $this->assertSame('fulfilled', $domain->fresh()->fulfillment_status);
        $this->assertSame('provisioning', $hosting->fresh()->fulfillment_status);
        $this->assertSame('registration_failed', $secondDomain->fresh()->fulfillment_status);
        $this->assertNull($secondDomain->fresh()->domain_reservation_key);

        $hosting->setFulfillmentStatus('fulfilled');
        $this->assertSame('partially_fulfilled', $order->fresh()->status);

        $secondDomain->setFulfillmentStatus('fulfilled');
        $this->assertSame('fulfilled', $order->fresh()->status);
    }

    public function test_order_item_pricing_snapshot_cannot_be_changed_after_checkout(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder($user, 'DRY-IMMUTABLE-ITEM');
        $item = $this->createOrderItem($order, [
            'item_type' => 'domain_registration',
            'domain_name' => 'fixed.test',
            'unit_price' => 14,
            'line_total' => 14,
            'item_metadata' => [
                'base_price' => 10,
                'base_currency' => 'USD',
                'selling_price' => 14,
                'currency' => 'USD',
            ],
        ]);

        try {
            $item->update(['unit_price' => 1]);
            $this->fail('The checked-out item price should be immutable.');
        } catch (LogicException $exception) {
            $this->assertSame(
                'Order item details and pricing snapshots cannot be changed after checkout.',
                $exception->getMessage()
            );
        }

        $this->assertSame('14.00', $item->fresh()->unit_price);
        $this->assertSame(10, data_get($item->fresh()->item_metadata, 'base_price'));
    }

    public function test_expired_order_releases_domain_and_hosting_reservations_for_cart_recheckout(): void
    {
        config()->set('services.paystack.currencies', ['USD']);
        Http::fake([
            'https://api.porkbun.com/api/json/v3/domain/checkDomain/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['avail' => 'yes', 'price' => 10],
            ]),
            'https://api.porkbun.com/api/json/v3/pricing/get' => Http::response([
                'status' => 'SUCCESS',
                'pricing' => ['test' => ['registration' => ['price' => 10]]],
            ]),
        ]);

        $user = User::factory()->create();
        $package = $this->createHostingPackage('expiry-hosting-package');
        $server = HostingServer::create([
            'name' => 'Single-slot server',
            'max_accounts' => 1,
            'is_active' => true,
        ]);
        $order = $this->createOrder($user, 'DRY-EXPIRED-ORDER', [
            'status' => 'awaiting_payment',
            'expires_at' => now()->subMinute(),
        ]);
        $cart = Cart::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'currency' => 'USD',
            'status' => 'checkout_pending',
            'service_order_id' => $order->id,
        ]);

        $domainCartItem = $cart->items()->create([
            'item_type' => 'domain_registration',
            'domain_name' => 'reserved.test',
            'provider' => 'porkbun',
            'provider_product_id' => 'test',
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 14,
            'line_total' => 14,
            'currency' => 'USD',
            'price_metadata' => ['base_price_usd' => 10, 'base_currency' => 'USD'],
        ]);
        $cart->items()->create([
            'item_type' => 'hosting',
            'domain_name' => 'reserved.test',
            'hosting_package_id' => $package->id,
            'parent_item_id' => $domainCartItem->id,
            'provider' => 'verpex',
            'provider_product_id' => $package->slug,
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 30,
            'line_total' => 30,
            'currency' => 'USD',
            'price_metadata' => ['base_price_usd' => 30, 'base_currency' => 'USD'],
        ]);

        $domainOrderItem = $this->createOrderItem($order, [
            'item_type' => 'domain_registration',
            'domain_name' => 'reserved.test',
            'fulfillment_status' => 'awaiting_payment',
            'domain_reservation_key' => hash('sha256', 'reserved.test'),
        ]);
        $this->createOrderItem($order, [
            'item_type' => 'hosting',
            'domain_name' => 'reserved.test',
            'hosting_package_id' => $package->id,
            'hosting_server_id' => $server->id,
            'parent_item_id' => $domainOrderItem->id,
            'fulfillment_status' => 'awaiting_payment',
        ]);

        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('active', $cart->fresh()->status);
        $this->assertNull($cart->fresh()->service_order_id);
        $this->assertSame('cancelled', $domainOrderItem->fresh()->fulfillment_status);
        $this->assertNull($domainOrderItem->fresh()->domain_reservation_key);

        $response = $this->actingAs($user)
            ->withSession(['drypp_cart_token' => $cart->token])
            ->post(route('cart.checkout'));

        $newOrder = ServiceOrder::query()->where('status', 'awaiting_payment')
            ->where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('checkout.show', $newOrder));
        $this->assertSame($server->id, $newOrder->items()->where('item_type', 'hosting')->value('hosting_server_id'));
        $this->assertSame(
            hash('sha256', 'reserved.test'),
            $newOrder->items()->where('item_type', 'domain_registration')->value('domain_reservation_key')
        );
        $this->assertNotNull($newOrder->expires_at);
        $this->assertSame('checkout_pending', $cart->fresh()->status);
    }

    public function test_duplicate_domain_reservation_is_rejected_during_checkout(): void
    {
        config()->set('services.paystack.currencies', ['USD']);
        Http::fake([
            'https://api.porkbun.com/api/json/v3/domain/checkDomain/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['avail' => 'yes', 'price' => 10],
            ]),
            'https://api.porkbun.com/api/json/v3/pricing/get' => Http::response([
                'status' => 'SUCCESS',
                'pricing' => ['test' => ['registration' => ['price' => 10]]],
            ]),
        ]);

        $existingOrder = $this->createOrder(User::factory()->create(), 'DRY-EXISTING-RESERVATION', [
            'status' => 'awaiting_payment',
            'expires_at' => now()->addMinutes(30),
        ]);
        $this->createOrderItem($existingOrder, [
            'item_type' => 'domain_registration',
            'domain_name' => 'claimed.test',
            'domain_reservation_key' => hash('sha256', 'claimed.test'),
        ]);

        $customer = User::factory()->create();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'token' => Str::random(64),
            'currency' => 'USD',
            'status' => 'active',
        ]);
        $cart->items()->create([
            'item_type' => 'domain_registration',
            'domain_name' => 'claimed.test',
            'provider' => 'porkbun',
            'provider_product_id' => 'test',
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 14,
            'line_total' => 14,
            'currency' => 'USD',
            'price_metadata' => ['base_price_usd' => 10, 'base_currency' => 'USD'],
        ]);

        $this->actingAs($customer)
            ->withSession(['drypp_cart_token' => $cart->token])
            ->post(route('cart.checkout'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('checkout');

        $this->assertSame(1, ServiceOrder::query()->count());
        $this->assertSame('active', $cart->fresh()->status);
    }

    public function test_customer_cannot_view_or_pay_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->createOrder($owner, 'DRY-PRIVATE-ORDER');
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer)
            ->get(route('checkout.show', $order))
            ->assertForbidden();
        $this->post(route('checkout.pay', $order))->assertForbidden();
    }

    private function createOrder(User $user, string $orderNumber, array $attributes = []): ServiceOrder
    {
        return ServiceOrder::create(array_merge([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'service_type' => 'marketplace',
            'amount_ngn' => 0,
            'amount' => 44,
            'currency' => 'USD',
            'status' => 'paid',
            'metadata' => [],
        ], $attributes));
    }

    private function createOrderItem(ServiceOrder $order, array $attributes): ServiceOrderItem
    {
        return ServiceOrderItem::create(array_merge([
            'service_order_id' => $order->id,
            'item_type' => 'domain_registration',
            'quantity' => 1,
            'billing_period' => 'yearly',
            'unit_price' => 14,
            'line_total' => 14,
            'currency' => 'USD',
            'fulfillment_status' => 'pending_registration',
            'item_metadata' => [
                'base_price' => 10,
                'base_currency' => 'USD',
                'selling_price' => 14,
                'currency' => 'USD',
            ],
        ], $attributes));
    }

    private function createHostingPackage(string $slug): HostingPackage
    {
        return HostingPackage::create([
            'name' => 'Standard Hosting',
            'slug' => $slug,
            'description' => 'Hosting used by order management tests.',
            'price_ngn' => 45000,
            'price_usd' => 30,
            'billing_cycle' => 'yearly',
            'disk_space_gb' => 3,
            'bandwidth_gb' => 30,
            'email_accounts' => 10,
            'addon_domains' => 1,
            'databases' => 2,
            'max_accounts_per_server' => 1,
            'is_active' => true,
        ]);
    }
}
