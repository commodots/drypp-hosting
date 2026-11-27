<?php

namespace Tests\Feature;

use App\Models\PaymentWebhookEvent;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaystackIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_webhook_and_callback_verify_once_and_create_one_service_record(): void
    {
        $secret = 'test-secret';
        config()->set('services.paystack.secret_key', $secret);
        config()->set('services.paystack.currencies', ['USD']);
        Http::fake([
            'https://api.paystack.co/transaction/verify/DRYPP-WEBHOOK-ONCE' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 1400,
                    'currency' => 'USD',
                    'reference' => 'DRYPP-WEBHOOK-ONCE',
                    'customer' => ['email' => 'payer@example.test'],
                    'gateway_response' => 'Successful',
                    'paid_at' => '2026-11-27T09:00:00.000Z',
                    'channel' => 'card',
                ],
            ]),
        ]);
        Mail::fake();

        $user = User::factory()->create(['email' => 'payer@example.test']);
        $order = $this->createOrder($user, 'DRY-WEBHOOK-ONCE', 'DRYPP-WEBHOOK-ONCE');
        $this->createDomainItem($order);
        $payload = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'DRYPP-WEBHOOK-ONCE',
                'amount' => 1400,
                'currency' => 'USD',
            ],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $body, $secret);

        $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $body)->assertOk();

        $this->postJson(route('webhooks.paystack'), $payload, [
            'x-paystack-signature' => $signature,
        ])->assertOk()->assertJson(['duplicate' => true]);

        $this->get(route('checkout.callback', ['reference' => 'DRYPP-WEBHOOK-ONCE']))
            ->assertRedirect(route('my-services.index'));

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertDatabaseCount('user_services', 1);
        $this->assertDatabaseHas('payment_transactions', [
            'service_order_id' => $order->id,
            'reference' => 'DRYPP-WEBHOOK-ONCE',
            'amount_minor' => 1400,
            'currency' => 'USD',
            'status' => 'paid',
        ]);
        $this->assertSame(1, PaymentWebhookEvent::where('status', 'processed')->count());
        Http::assertSentCount(1);
    }

    public function test_invalid_webhook_signature_is_rejected_without_verification(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        Http::fake();

        $this->postJson(route('webhooks.paystack'), [
            'event' => 'charge.success',
            'data' => ['reference' => 'DRYPP-INVALID-SIGNATURE'],
        ], ['x-paystack-signature' => 'invalid'])->assertUnauthorized();

        $this->assertDatabaseCount('payment_webhook_events', 0);
        Http::assertNothingSent();
    }

    public function test_manual_reconciliation_uses_the_same_server_side_verification(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        config()->set('services.paystack.currencies', ['USD']);
        Http::fake([
            'https://api.paystack.co/transaction/verify/DRYPP-MANUAL-RECONCILE' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 1400,
                    'currency' => 'USD',
                    'reference' => 'DRYPP-MANUAL-RECONCILE',
                    'customer' => ['email' => 'reconcile@example.test'],
                ],
            ]),
        ]);
        Mail::fake();

        $user = User::factory()->create(['email' => 'reconcile@example.test']);
        $order = $this->createOrder($user, 'DRY-RECONCILE', 'DRYPP-MANUAL-RECONCILE');
        $this->createDomainItem($order);

        $this->artisan('paystack:reconcile', ['reference' => 'DRYPP-MANUAL-RECONCILE'])
            ->expectsOutput("Order {$order->order_number} is processing.")
            ->assertSuccessful();

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertDatabaseCount('user_services', 1);
    }

    private function createOrder(User $user, string $orderNumber, string $reference): ServiceOrder
    {
        return ServiceOrder::create([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'service_type' => 'marketplace',
            'domain_name' => 'paid.example.test',
            'amount_ngn' => 0,
            'amount' => 14,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'payment_reference' => $reference,
            'metadata' => [],
        ]);
    }

    private function createDomainItem(ServiceOrder $order): void
    {
        ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'item_type' => 'domain_registration',
            'domain_name' => 'paid.example.test',
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
    }
}
