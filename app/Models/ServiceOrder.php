<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    public const STATUSES = [
        'awaiting_payment',
        'paid',
        'payment_failed',
        'processing',
        'partially_fulfilled',
        'fulfilled',
        'cancelled',
        'refunded',
    ];

    protected $fillable = [
        'user_id',
        'hosting_server_id',
        'hosting_package_id',
        'order_number',
        'service_type',
        'domain_name',
        'amount_ngn',
        'amount',
        'currency',
        'status',
        'payment_reference',
        'paid_at',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function syncFulfillmentStatus(): void
    {
        if (in_array($this->status, ['awaiting_payment', 'payment_failed', 'cancelled', 'refunded'], true)) {
            return;
        }

        $statuses = $this->items()->pluck('fulfillment_status');
        if ($statuses->isEmpty()) {
            return;
        }

        $fulfilledCount = $statuses->filter(fn (string $status) => $status === 'fulfilled')->count();
        $nextStatus = match (true) {
            $fulfilledCount === $statuses->count() => 'fulfilled',
            $fulfilledCount > 0 => 'partially_fulfilled',
            $statuses->every(fn (string $status) => $status === 'awaiting_payment') => 'paid',
            default => 'processing',
        };

        if ($this->status !== $nextStatus) {
            $this->update(['status' => $nextStatus]);
        }
    }

    public function formattedAmount(): string
    {
        return strtoupper($this->currency ?: 'NGN') . ' ' . number_format((float) ($this->amount ?? $this->amount_ngn), 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function hostingServer(): BelongsTo
    {
        return $this->belongsTo(HostingServer::class, 'hosting_server_id');
    }
    public function hostingPackage(): BelongsTo
    {
        return $this->belongsTo(HostingPackage::class);
    }
    public function hostingAccount()
    {
        return $this->hasOne(HostingAccount::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class, 'service_order_id');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'service_order_id');
    }
}
