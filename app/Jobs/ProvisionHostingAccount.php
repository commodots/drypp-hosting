<?php

namespace App\Jobs;

use App\Models\HostingAccount;
use App\Models\UserService;
use App\Services\Hosting\WhmApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProvisionHostingAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public array $backoff = [60, 300, 900, 1800];
    public int $timeout = 90;

    public function __construct(public int $hostingAccountId)
    {
    }

    public function handle(WhmApiClient $whm): void
    {
        $account = HostingAccount::with(['user', 'package'])->findOrFail($this->hostingAccountId);
        if ($account->status === 'active' && $account->cpanel_username) {
            return;
        }

        if (! $account->package || ! $account->user) {
            throw new RuntimeException('Hosting account is missing its customer or package relationship.');
        }

        $username = $account->cpanel_username;
        if (! $username) {
            $username = 'dr'.Str::lower(Str::random(8));
            $account->update(['cpanel_username' => $username, 'provisioning_mode' => 'automatic']);
        }

        // A previous request may have reached WHM even if the network timed out.
        // Reconcile by the stable username before attempting account creation again.
        try {
            $existing = $whm->accountSummary($username);
            $existingAccount = data_get($existing, 'data.acct.0', data_get($existing, 'acct.0'));
            if (is_array($existingAccount)) {
                $existingDomain = strtolower((string) data_get($existingAccount, 'domain', ''));
                if ($existingDomain !== strtolower($account->domain_name)) {
                    throw new RuntimeException('The generated cPanel username is already assigned to a different domain.');
                }
                $this->markProvisioned($account, $existingAccount, ['reconciled_existing_account' => true]);
                return;
            }
        } catch (RuntimeException $exception) {
            // accountsummary can report that an account does not exist. Continue to create it;
            // actual API/network errors will be caught by the createacct call below.
            if (str_contains(strtolower($exception->getMessage()), 'different domain')) {
                throw $exception;
            }
        }

        $packageName = trim((string) ($account->package->whm_package_name ?: $account->package->slug));
        if ($packageName === '') {
            throw new RuntimeException('No WHM package name is mapped to this Drypp hosting package.');
        }

        $password = Str::random(40);
        $created = $whm->createAccount([
            'username' => $username,
            'domain' => strtolower($account->domain_name),
            'password' => $password,
            'contactemail' => $account->user->email,
            'pkgname' => $packageName,
        ]);

        $data = data_get($created, 'data', []);
        $this->markProvisioned($account, is_array($data) ? $data : [], [
            'whm_package' => $packageName,
            'created_at' => now()->toIso8601String(),
        ]);
    }

    private function markProvisioned(HostingAccount $account, array $whmData, array $extra = []): void
    {
        $account->forceFill([
            'status' => 'active',
            'provisioning_mode' => 'automatic',
            'external_account_id' => (string) (data_get($whmData, 'user') ?: $account->cpanel_username),
            'provider_metadata' => array_merge($account->provider_metadata ?? [], [
                'provider' => 'verpex_whm',
                'ip_address' => data_get($whmData, 'ip'),
                'nameserver' => data_get($whmData, 'nameserver'),
                'whm_result' => $whmData,
            ], $extra),
        ])->save();

        $serviceQuery = UserService::query()
            ->where('user_id', $account->user_id)
            ->where('service_order_id', $account->service_order_id)
            ->where('service_type', 'hosting');
        if ($account->service_order_item_id) {
            $serviceQuery->where('service_order_item_id', $account->service_order_item_id);
        } else {
            $serviceQuery->where('identifier', $account->domain_name);
        }
        foreach ($serviceQuery->get() as $service) {
            $metadata = $service->metadata ?? [];
            $metadata['cpanel_username'] = $account->cpanel_username;
            $service->update([
                'status' => 'active',
                'started_at' => $service->started_at ?: now(),
                'metadata' => $metadata,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        $account = HostingAccount::find($this->hostingAccountId);
        if (! $account || $account->status === 'active') {
            return;
        }

        $account->update([
            'status' => 'provisioning_failed',
            'provisioning_mode' => 'automatic',
            'provider_metadata' => array_merge($account->provider_metadata ?? [], [
                'last_error' => Str::limit($exception->getMessage(), 500),
                'failed_at' => now()->toIso8601String(),
            ]),
        ]);

        $serviceQuery = UserService::query()
            ->where('user_id', $account->user_id)
            ->where('service_order_id', $account->service_order_id)
            ->where('service_type', 'hosting');
        if ($account->service_order_item_id) {
            $serviceQuery->where('service_order_item_id', $account->service_order_item_id);
        } else {
            $serviceQuery->where('identifier', $account->domain_name);
        }
        $serviceQuery->update(['status' => 'provisioning_failed', 'updated_at' => now()]);
    }
}
