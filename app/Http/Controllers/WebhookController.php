<?php

namespace App\Http\Controllers;

use App\Models\CampaignMessage;
use App\Services\Payments\PaystackPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        try {
            $payments->verifyAndMarkPaid($reference);
        } catch (\Throwable $exception) {
            report($exception);
            // Non-2xx asks Paystack to retry transient verification failures.
            return response()->json(['message' => 'Transaction verification is pending.'], 500);
        }

        return response()->json(['received' => true]);
    }
}
