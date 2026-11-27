<?php

namespace Tests\Feature;

use App\Models\HostingPackage;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;
use Tests\TestCase;

class HostingCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_three_expected_usd_hosting_packages(): void
    {
        $this->seed();

        $standard = HostingPackage::where('slug', 'standard-hosting')->firstOrFail();
        $business = HostingPackage::where('slug', 'business-hosting-usd')->firstOrFail();
        $professional = HostingPackage::where('slug', 'professional-hosting')->firstOrFail();

        $this->assertSame('29.99', number_format((float) $standard->price_usd, 2, '.', ''));
        $this->assertSame(3, $standard->disk_space_gb);
        $this->assertSame(30, $standard->bandwidth_gb);
        $this->assertSame(10, $standard->email_accounts);
        $this->assertSame(2, $standard->databases);

        $this->assertSame('49.99', number_format((float) $business->price_usd, 2, '.', ''));
        $this->assertSame('99.99', number_format((float) $professional->price_usd, 2, '.', ''));

        $this->assertTrue($standard->is_active);
        $this->assertTrue($business->is_active);
        $this->assertTrue($professional->is_active);
    }

    public function test_usd_hosting_orders_are_sent_to_paystack_in_usd(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        config()->set('services.paystack.currencies', ['USD', 'NGN']);
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => function (HttpRequest $request) {
                return Http::response([
                    'status' => true,
                    'message' => 'Authorization URL created',
                    'data' => [
                        'authorization_url' => 'https://checkout.paystack.com/test-authorization',
                        'access_code' => 'test-access-code',
                        'reference' => $request['reference'],
                    ],
                ]);
            },
        ]);

        $user = User::factory()->create(['email' => 'usd-customer@example.test']);
        $order = ServiceOrder::create([
            'user_id' => $user->id,
            'order_number' => 'DRY-USD-CHECKOUT',
            'service_type' => 'hosting',
            'amount_ngn' => 0,
            'amount' => 29.99,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'metadata' => [],
        ]);

        $this->actingAs($user)
            ->post(route('checkout.pay', $order))
            ->assertRedirect('https://checkout.paystack.com/test-authorization');

        $this->actingAs($user)
            ->post(route('checkout.pay', $order))
            ->assertRedirect('https://checkout.paystack.com/test-authorization');

        Http::assertSentCount(1);
        Http::assertSent(function (HttpRequest $request): bool {
            return $request->url() === 'https://api.paystack.co/transaction/initialize'
                && $request['amount'] === 2999
                && $request['currency'] === 'USD';
        });

        $order->refresh();
        $this->assertSame('USD', data_get($order->metadata, 'payment.currency'));
        $this->assertSame(2999, data_get($order->metadata, 'payment.amount_minor'));
        $this->assertSame('https://checkout.paystack.com/test-authorization', data_get($order->metadata, 'payment.authorization_url'));
        $this->assertNotEmpty($order->payment_reference);
        $this->assertDatabaseHas('payment_transactions', [
            'service_order_id' => $order->id,
            'reference' => $order->payment_reference,
            'amount_minor' => 2999,
            'currency' => 'USD',
            'status' => 'initialized',
        ]);
    }

    public function test_usd_checkout_is_disabled_until_merchant_currency_is_explicitly_configured(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        config()->set('services.paystack.currencies', ['NGN']);

        $user = User::factory()->create();
        $order = ServiceOrder::create([
            'user_id' => $user->id,
            'order_number' => 'DRY-USD-DISABLED',
            'service_type' => 'hosting',
            'amount_ngn' => 0,
            'amount' => 29.99,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'metadata' => [],
        ]);

        $this->actingAs($user)
            ->post(route('checkout.pay', $order))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payment_transactions', 0);
        Http::assertNothingSent();
    }

    public function test_ambiguous_initialization_failure_cannot_create_a_second_payment_attempt(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        config()->set('services.paystack.currencies', ['USD']);
        Log::spy();
        $initializationCalls = 0;
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => function () use (&$initializationCalls) {
                $initializationCalls++;
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out.');
            },
        ]);

        $user = User::factory()->create();
        $order = ServiceOrder::create([
            'user_id' => $user->id,
            'order_number' => 'DRY-USD-INIT-UNKNOWN',
            'service_type' => 'hosting',
            'amount_ngn' => 0,
            'amount' => 29.99,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'metadata' => [],
        ]);

        $this->actingAs($user)->post(route('checkout.pay', $order))
            ->assertSessionHasErrors('payment');

        $customerMessage = session('errors')->first('payment');
        $this->assertStringContainsString('Please don’t pay again', $customerMessage);
        $this->assertStringContainsString('contact support', $customerMessage);
        $this->assertStringNotContainsString('artisan', $customerMessage);

        $reference = $order->fresh()->payment_reference;
        $this->assertNotEmpty($reference);
        Log::shouldHaveReceived('error')->withArgs(
            fn (string $message, array $context): bool => $message === 'Paystack initialization result is unknown.'
                && ($context['reference'] ?? null) === $reference
        );
        $this->assertDatabaseHas('payment_transactions', [
            'service_order_id' => $order->id,
            'reference' => $reference,
            'status' => 'initialization_unknown',
        ]);

        $this->actingAs($user)->post(route('checkout.pay', $order))
            ->assertSessionHasErrors('payment');

        $this->assertSame(1, PaymentTransaction::where('service_order_id', $order->id)->count());
        $this->assertSame(1, $initializationCalls);
    }
}
