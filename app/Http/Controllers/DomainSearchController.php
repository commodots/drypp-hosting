<?php

namespace App\Http\Controllers;

use App\Models\HostingPackage;
use App\Services\Domain\PorkbunClient;
use Illuminate\Http\Request;

class DomainSearchController extends Controller
{
    public function search(Request $request, PorkbunClient $porkbun)
    {
        $domain = strtolower(trim((string) $request->query('domain', '')));
        $results = [];
        $packages = HostingPackage::query()->where('is_active', true)->orderBy('price_usd')->get();

        if ($domain !== '') {
            if (!preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
                return redirect()->route('services')->withErrors(['domain' => 'Enter a valid domain name, such as yourbusiness.com.']);
            }

            $base = substr($domain, 0, strrpos($domain, '.'));
            $extensions = array_values(array_unique([substr(strrchr($domain, '.'), 1), 'com', 'net', 'org', 'co', 'io', 'app', 'dev']));
            $markup = max(0, (float) config('services.porkbun.markup_percent', 40));
            $pricing = $porkbun->pricing();

            foreach (array_slice($extensions, 0, 8) as $extension) {
                $candidate = $base.'.'.$extension;
                $availability = $porkbun->checkAvailability($candidate);
                $response = data_get($availability, 'json.response', []);
                $avail = strtolower((string) data_get($response, 'avail', data_get($response, 'availability', '')));
                $confirmedAvailable = $availability['successful'] && in_array($avail, ['yes', 'available'], true);
                $confirmedTaken = $availability['successful'] && in_array($avail, ['no', 'unavailable'], true);
                if (!$confirmedAvailable && !$confirmedTaken) {
                    continue;
                }

                $tldPricing = data_get($pricing, 'json.response.'.$extension, []);
                $registration = data_get($tldPricing, 'registration');
                if (is_array($registration)) $registration = data_get($registration, 'price');
                $renewal = data_get($tldPricing, 'renewal');
                if (is_array($renewal)) $renewal = data_get($renewal, 'price');
                $dryppPrice = is_numeric($registration) && (float) $registration > 0
                    ? round((float) $registration * (1 + $markup / 100), 2)
                    : null;

                $results[] = [
                    'domain' => $candidate,
                    'available' => $confirmedAvailable,
                    'premium' => (bool) data_get($response, 'premium', false),
                    'sandbox' => (bool) data_get($response, 'sandbox', false),
                    'drypp_price_usd' => $dryppPrice,
                    'renewal_usd' => is_numeric($renewal) && (float) $renewal > 0
                        ? round((float) $renewal * (1 + $markup / 100), 2)
                        : null,
                ];
            }
        }

        return view('domains.results', compact('domain', 'results', 'packages'));
    }
}
