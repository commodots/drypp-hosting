<?php

namespace App\Services\Hosting;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhmApiClient
{
    private function baseUrl(): string
    {
        $host = trim((string) config('services.verpex.whm_host'));
        if ($host === '') {
            throw new RuntimeException('Verpex WHM hostname is not configured.');
        }

        if (! str_starts_with($host, 'https://') && ! str_starts_with($host, 'http://')) {
            $host = 'https://'.$host;
        }

        $parts = parse_url($host);
        if (! is_array($parts) || empty($parts['host'])) {
            throw new RuntimeException('The configured Verpex WHM hostname is invalid.');
        }

        // WHM API tokens must be sent over TLS. Port 2087 is the standard secure WHM API port.
        $hostname = $parts['host'];
        $port = $parts['port'] ?? 2087;

        return 'https://'.$hostname.':'.$port;
    }

    private function username(): string
    {
        $username = trim((string) config('services.verpex.whm_username'));
        if ($username === '') {
            throw new RuntimeException('Verpex WHM username is not configured.');
        }
        return $username;
    }

    private function token(): string
    {
        $token = trim((string) config('services.verpex.whm_api_token'));
        if ($token === '') {
            throw new RuntimeException('Verpex WHM API token is not configured.');
        }
        return $token;
    }

    private function request(string $function, array $parameters = [], string $method = 'get'): array
    {
        $url = $this->baseUrl().'/json-api/'.$function;
        $http = Http::acceptJson()
            ->withHeaders(['Authorization' => 'whm '.$this->username().':'.$this->token()])
            ->timeout(35);

        $response = $method === 'post'
            ? $http->asForm()->post($url.'?api.version=1', $parameters)
            : $http->get($url, array_merge(['api.version' => 1], $parameters));

        if (! $response->successful()) {
            throw new RuntimeException('Verpex WHM returned HTTP '.$response->status().' for '.$function.'.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Verpex WHM returned an unreadable response for '.$function.'.');
        }

        $result = data_get($payload, 'metadata.result');
        if ((string) $result !== '1') {
            $reason = data_get($payload, 'metadata.reason', data_get($payload, 'error', 'The WHM operation was not successful.'));
            throw new RuntimeException('Verpex WHM '.$function.' failed: '.(string) $reason);
        }

        return $payload;
    }

    public function accountSummary(string $username): array
    {
        return $this->request('accountsummary', ['user' => $username]);
    }

    public function createAccount(array $details): array
    {
        return $this->request('createacct', $details, 'post');
    }

    public function suspendAccount(string $username, string $reason = 'Suspended by account owner'): array
    {
        return $this->request('suspendacct', ['user' => $username, 'reason' => $reason], 'post');
    }

    public function unsuspendAccount(string $username): array
    {
        return $this->request('unsuspendacct', ['user' => $username], 'post');
    }

    public function terminateAccount(string $username): array
    {
        return $this->request('removeacct', ['user' => $username, 'keepdns' => 1], 'post');
    }
}
