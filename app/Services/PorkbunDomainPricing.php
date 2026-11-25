<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PorkbunDomainPricing
{
    /**
     * Return Porkbun's current registration/renewal price for a TLD, in USD.
     * Returns null when Porkbun doesn't provide a usable price.
     */
    public function forExtension(string $extension): ?array
    {
        $tld = ltrim(strtolower($extension), '.');
        $base = rtrim(config('services.porkbun.base_url', 'https://api.porkbun.com/api/json/v3'), '/');
        $key = config('services.porkbun.api_key');
        $secret = config('services.porkbun.secret_key');

        if (!$key || !$secret || !$tld) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-API-Key' => $key,
                    'X-Secret-API-Key' => $secret,
                ])
                ->timeout(15)
                ->post($base.'/pricing/get');

            if (!$response->successful()) {
                Log::warning('Porkbun pricing API returned non-success status', [
                    'status' => $response->status(),
                    'tld' => $tld,
                ]);
                return null;
            }

            $json = $response->json();
            if (!is_array($json) || strtolower((string)($json['status'] ?? '')) !== 'success') {
                return null;
            }

            $pricing = $json['pricing'] ?? [];
            // Porkbun's pricing response is a TLD-keyed map; tolerate case and dot variations.
            $entry = $pricing[$tld] ?? $pricing['.'.$tld] ?? null;
            if (!is_array($entry)) {
                foreach ($pricing as $keyTld => $value) {
                    if (ltrim(strtolower((string)$keyTld), '.') === $tld && is_array($value)) {
                        $entry = $value;
                        break;
                    }
                }
            }
            if (!is_array($entry)) {
                return null;
            }

            $registration = $this->number($entry['registration'] ?? $entry['register'] ?? null);
            $renewal = $this->number($entry['renewal'] ?? $entry['renew'] ?? null);

            if ($registration === null) {
                return null;
            }

            return [
                'registration_usd' => $registration,
                'renewal_usd' => $renewal ?? $registration,
            ];
        } catch (\Throwable $e) {
            Log::warning('Porkbun pricing lookup failed', ['tld' => $tld, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function number(mixed $value): ?float
    {
        if (!is_numeric($value) || (float)$value <= 0) {
            return null;
        }
        return round((float)$value, 2);
    }
}
