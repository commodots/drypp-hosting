<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingPackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'whm_package_name', 'description', 'price_ngn', 'price_usd', 'billing_cycle',
        'disk_space_gb', 'bandwidth_gb', 'email_accounts', 'addon_domains',
        'databases', 'max_accounts_per_server', 'features',
        'free_website_enabled', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'decimal:2',
            'features' => 'array',
            'free_website_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany { return $this->hasMany(ServiceOrder::class); }
    public function accounts(): HasMany { return $this->hasMany(HostingAccount::class); }
}
