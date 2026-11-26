<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostingAccount extends Model
{
    protected $fillable = [
        'user_id',
        'hosting_server_id',
        'hosting_package_id',
        'service_order_id',
        'service_order_item_id',
        'domain_name',
        'cpanel_username',
        'external_account_id',
        'status',
        'provisioning_mode',
        'expires_at',
        'provider_metadata'
    ];
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'provider_metadata' => 'array'];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function server(): BelongsTo
    {
        return $this->belongsTo(HostingServer::class, 'hosting_server_id');
    }
    public function package(): BelongsTo
    {
        return $this->belongsTo(HostingPackage::class, 'hosting_package_id');
    }
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ServiceOrderItem::class, 'service_order_item_id');
    }
    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }
}
