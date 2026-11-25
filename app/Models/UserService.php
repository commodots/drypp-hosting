<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserService extends Model
{
    protected $fillable = [
        'user_id', 'service_order_id', 'service_order_item_id', 'service_type',
        'name', 'identifier', 'provider', 'status', 'started_at', 'expires_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderItem::class, 'service_order_item_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return str($this->status)->replace('_', ' ')->title()->toString();
    }
}
