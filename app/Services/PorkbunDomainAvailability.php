<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PorkbunDomainAvailability
{
    /**
     * Returns true (confirmed available), false (confirmed unavailable),
     * or null (Porkbun could not provide a usable answer).
     */
    public function check(string $domain): ?bool
    {
        $apiKey = config('services.porkbun.api_key');
        $secretKey = config('services.porkbun.secret_key');

        if (blank($apiKey) || blank($secretKey)) {
            Log::warning('Porkbun availability check: credentials are not configured.');
            return null;
        }

        $baseUrl = rtrim(
            config('services.porkbun.base_url', 'https://api.porkbun.com/api/json/v3'),
            '/'
        );

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'X-Secret-API-Key' => $secretKey,
                ])
                ->post($baseUrl.'/domain/checkDomain/'.rawurlencode($domain));

            $payload = $response->json();

            if (!$response->successful() || !is_array($payload) || strtoupper((string) ($payload['status'] ?? '')) !== 'SUCCESS') {
                Log::warning('Porkbun availability check returned an error.', [
                    'domain' => $domain,
                    'http_status' => $response->status(),
                    'api_status' => is_array($payload) ? ($payload['status'] ?? 'missing') : 'invalid_json',
                    'api_code' => is_array($payload) ? ($payload['code'] ?? null) : null,
                ]);
                return null;
            }

            // Porkbun's v3 availability field is "avail" ("yes" / "no").
            // Keep a few documented/legacy response shapes compatible, but do not
            // guess when the response contains no explicit availability value.
            $responseData = $payload['response'] ?? [];
            if (is_array($responseData) && array_is_list($responseData)) {
                $responseData = $responseData[0] ?? [];
            }

            $value = data_get($responseData, 'avail')
                ?? data_get($responseData, 'availability')
                ?? data_get($payload, 'avail')
                ?? data_get($payload, 'availability');

            if (is_bool($value)) {
                return $value;
            }

            $normalized = strtolower(trim((string) $value));

            return match ($normalized) {
                'yes', 'available', 'true', '1' => true,
                'no', 'unavailable', 'taken', 'false', '0' => false,
                default => $this->logUnrecognizedResponse($domain, $payload),
            };
        } catch (Throwable $exception) {
            Log::warning('Porkbun availability request failed.', [
                'domain' => $domain,
                'exception' => class_basename($exception),
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function logUnrecognizedResponse(string $domain, array $payload): ?bool
    {
        $responseData = $payload['response'] ?? null;
        Log::warning('Porkbun availability response did not contain a recognized value.', [
            'domain' => $domain,
            'response_keys' => is_array($responseData) ? array_keys($responseData) : gettype($responseData),
        ]);

        return null;
    }
}
