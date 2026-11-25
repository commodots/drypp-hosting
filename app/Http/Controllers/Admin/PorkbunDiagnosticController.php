<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainPricing;
use App\Services\Domain\PorkbunClient;
use Illuminate\Http\Request;
use Throwable;

class PorkbunDiagnosticController extends Controller
{
    public function __invoke(Request $request, PorkbunClient $porkbun)
    {
        $data = $request->validate([
            'mode' => ['nullable', 'in:connectivity,availability,pricing'],
            'domain' => ['nullable', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z]{2,63}$/i'],
        ]);
        $mode = $data['mode'] ?? 'availability';
        try {
            if ($mode === 'connectivity') {
                $result = $porkbun->connectivity();
                return response()->json([
                    'ok' => $result['successful'], 'http_status' => $result['http_status'],
                    'elapsed_ms' => $result['elapsed_ms'],
                    'porkbun_response' => $result['json'] ?? $result['raw'],
                    'log_file' => 'storage/logs/porkbun.log',
                ], $result['successful'] ? 200 : 502);
            }
            if ($mode === 'pricing') {
                $result = $porkbun->pricing();
                $apiPricing = data_get($result, 'json.pricing', []);
                $configured = DomainPricing::query()->get()->keyBy(fn ($row) => ltrim(strtolower($row->extension), '.'));
                $comparison = [];
                foreach ($apiPricing as $tld => $rates) {
                    $local = $configured->get(strtolower($tld));
                    $comparison[$tld] = [
                        'porkbun_rates_raw' => $rates,
                        'drypp_configured' => $local ? [
                            'registration_cost_ngn' => $local->registration_cost_ngn,
                            'renewal_cost_ngn' => $local->renewal_cost_ngn,
                            'legacy_fixed_markup_ngn' => $local->markup_ngn,
                            'markup_percent' => $local->markup_percent,
                            'selling_price_ngn' => $local->sellingPrice(),
                        ] : null,
                    ];
                }
                return response()->json([
                    'ok' => $result['successful'], 'http_status' => $result['http_status'],
                    'elapsed_ms' => $result['elapsed_ms'], 'raw_response' => $result['json'] ?? $result['raw'],
                    'comparison_by_tld' => $comparison, 'log_file' => 'storage/logs/porkbun.log',
                ], $result['successful'] ? 200 : 502);
            }
            if (empty($data['domain'])) return response()->json(['message' => 'domain is required for availability mode.'], 422);
            $domain = strtolower($data['domain']);
            $result = $porkbun->checkAvailability($domain);
            return response()->json([
                'ok' => $result['successful'], 'domain' => $domain,
                'http_status' => $result['http_status'], 'elapsed_ms' => $result['elapsed_ms'],
                'porkbun_response' => $result['json'] ?? $result['raw'], 'log_file' => 'storage/logs/porkbun.log',
            ], $result['successful'] ? 200 : 502);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'log_file' => 'storage/logs/porkbun.log'], 500);
        }
    }
}
