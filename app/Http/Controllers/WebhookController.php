<?php

namespace App\Http\Controllers;

use App\Models\CampaignMessage;
use App\Models\PaymentWebhookEvent;
use App\Services\Payments\PaystackPaymentService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /** Handle SendGrid webhook events. */
    public function sendgrid(Request $request)
    {
        foreach ($request->all() as $event) {
            if (!isset($event['custom_args']['uuid'])) {
                continue;
            }

            $message = CampaignMessage::with('smtp')->where('message_uuid', $event['custom_args']['uuid'])->first();
            if ($message) {
                $status = $event['event'] ?? 'processed';
                $message->update(['status' => $status]);

                if ($message->smtp) {
                    if (in_array($status, ['bounce', 'deferred', 'dropped'])) {
                        $message->smtp->increment('bounces_last_24h');
                    }
                    if ($status === 'dropped') {
                        $message->smtp->increment('fails_last_24h');
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /** Verify Paystack's signature, then independently verify the transaction with Paystack. */
    public function paystack(Request $request, PaystackPaymentService $payments)
    {
        $secret = config('services.paystack.secret_key');
        $signature = (string) $request->header('x-paystack-signature', '');
        $expectedSignature = $secret ? hash_hmac('sha512', $request->getContent(), $secret) : '';

        if (!$secret || $signature === '' || !hash_equals($expectedSignature, $signature)) {
            Log::warning('Rejected Paystack webhook with invalid signature.');
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->json()->all();
        if (($payload['event'] ?? null) !== 'charge.success') {
            return response()->json(['received' => true]);
        }

        $reference = (string) data_get($payload, 'data.reference', '');
        if ($reference === '') {
            return response()->json(['message' => 'Missing transaction reference.'], 422);
        }

        $eventHash = hash('sha256', $request->getContent());
        try {
            $event = PaymentWebhookEvent::firstOrCreate(
                ['event_hash' => $eventHash],
                [
                    'event_name' => 'charge.success',
                    'reference' => $reference,
                    'status' => 'received',
                ]
            );
        } catch (QueryException $exception) {
            $event = PaymentWebhookEvent::where('event_hash', $eventHash)->first();
            if (! $event) {
                throw $exception;
            }
        }

        if ($event->status === 'processed') {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        $event->update([
            'status' => 'processing',
            'attempts' => $event->attempts + 1,
            'last_error' => null,
        ]);

        try {
            $payments->verifyAndMarkPaid($reference);
        } catch (\Throwable $exception) {
            $event->update([
                'status' => 'failed',
                'last_error' => Str::limit($exception->getMessage(), 500),
            ]);
            report($exception);
            // Non-2xx asks Paystack to retry transient verification failures.
            return response()->json(['message' => 'Transaction verification is pending.'], 500);
        }

        $event->update([
            'status' => 'processed',
            'processed_at' => now(),
            'last_error' => null,
        ]);

        return response()->json(['received' => true]);
    }
}
