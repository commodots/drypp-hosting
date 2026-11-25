<?php

namespace App\Services\Domain;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PorkbunClient
{
    private function request(string $path, array $payload = []): array
    {
        $key = config('services.porkbun.api_key');
        $secret = config('services.porkbun.secret_key');
        if (blank($key) || blank($secret)) {
            return ['successful' => false, 'json' => [], 'error' => 'Porkbun credentials are not configured.'];
        }

        $base = rtrim(config('services.porkbun.base_url', 'https://api.porkbun.com/api/json/v3'), '/');
        try {
            $response = Http::acceptJson()->asJson()->timeout(20)->withHeaders([
                'X-API-Key' => $key,
                'X-Secret-API-Key' => $secret,
            ])->post($base.$path, $payload);

            $json = $response->json();
            $successful = $response->successful() && is_array($json)
                && strtoupper((string) ($json['status'] ?? '')) === 'SUCCESS';

            if (!$successful) {
                Log::warning('Porkbun API request failed.', [
                    'path' => $path,
                    'http_status' => $response->status(),
                    'api_status' => is_array($json) ? ($json['status'] ?? null) : null,
                ]);
            }

            return ['successful' => $successful, 'json' => is_array($json) ? $json : []];
        } catch (\Throwable $exception) {
            report($exception);
            return ['successful' => false, 'json' => [], 'error' => 'Porkbun request failed.'];
        }
    }

    public function checkAvailability(string $domain): array
    {
        return $this->request('/domain/checkDomain/'.rawurlencode(strtolower(trim($domain))));
    }

    public function pricing(): array
    {
        $result = $this->request('/pricing/get');
        if ($result['successful']) {
            // Normalize the documented pricing map for the existing checkout controller.
            $result['json']['response'] = $result['json']['pricing'] ?? $result['json']['response'] ?? [];
        }
        return $result;
    }
}
