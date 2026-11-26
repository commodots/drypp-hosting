<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingServer extends Model
{
    protected $fillable = ['name','provider','hostname','ip_address','max_accounts','disk_limit_gb','bandwidth_limit_gb','package_capacity','is_active','notes'];
    protected function casts(): array { return ['package_capacity' => 'array', 'is_active' => 'boolean']; }
    public function accounts(): HasMany { return $this->hasMany(HostingAccount::class); }
    public function orders(): HasMany { return $this->hasMany(ServiceOrder::class); }
    public function activeAccountsCount(): int { return $this->accounts()->whereIn('status', ['pending_provisioning','active','suspended'])->count(); }
    public function availableSlots(): int { return max(0, (int) $this->max_accounts - $this->activeAccountsCount()); }
}
