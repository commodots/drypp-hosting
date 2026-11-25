<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DomainRegistrar
{
    /**
     * Return confirmed availability only. Null means the registrar could not verify.
     */
    public function checkAvailability(string $domain): ?bool
    {
        $apiKey = config('services.porkbun.api_key');
        $secret = config('services.porkbun.secret_key');

        if (!$apiKey || !$secret) {
            Log::warning('Porkbun availability check skipped: API credentials missing.');
            return null;
        }

        $base = rtrim(config('services.porkbun.base_url', 'https://api.porkbun.com/api/json/v3'), '/');
        $endpoint = $base.'/domain/checkDomain/'.rawurlencode($domain);

        try {
            $response = Http::timeout(12)
                ->acceptJson()
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'X-Secret-API-Key' => $secret,
                ])
                ->post($endpoint);

            if (!$response->successful()) {
                Log::warning('Porkbun domain check returned HTTP '.$response->status(), ['domain' => $domain]);
                return null;
            }

            $payload = $response->json();
            if (!is_array($payload) || ($payload['status'] ?? null) !== 'SUCCESS') {
                Log::warning('Porkbun domain check response was not successful.', ['domain' => $domain]);
                return null;
            }

            // Porkbun availability response uses response.availability (AVAILABLE / UNAVAILABLE).
            $availability = strtoupper((string) data_get($payload, 'response.availability', ''));
            return match ($availability) {
                'AVAILABLE' => true,
                'UNAVAILABLE' => false,
                default => null,
            };
        } catch (Throwable $e) {
            Log::warning('Porkbun domain check failed.', ['domain' => $domain, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
