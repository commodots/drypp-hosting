<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use LogicException;

class ServiceOrderItem extends Model
{
    public const FULFILLMENT_STATUSES = [
        'awaiting_payment',
        'pending_registration',
        'provisioning',
        'pending_fulfillment',
        'fulfilled',
        'registration_failed',
        'provisioning_failed',
        'fulfillment_failed',
        'cancelled',
    ];

    protected $fillable = [
        'service_order_id',
        'item_type',
        'domain_name',
        'hosting_package_id',
        'hosting_server_id',
        'parent_item_id',
        'provider',
        'provider_product_id',
        'quantity',
        'billing_period',
        'unit_price',
        'line_total',
        'currency',
        'item_metadata',
        'fulfillment_status',
        'domain_reservation_key',
    ];

    protected function casts(): array
    {
        return
            [
                'unit_price' => 'decimal:2',
                'line_total' => 'decimal:2',
                'item_metadata' => 'array'
            ];
    }

    protected static function booted(): void
    {
        static::updating(function (ServiceOrderItem $item): void {
            $immutableFields = [
                'service_order_id',
                'item_type',
                'domain_name',
                'hosting_package_id',
                'hosting_server_id',
                'parent_item_id',
                'provider',
                'provider_product_id',
                'quantity',
                'billing_period',
                'unit_price',
                'line_total',
                'currency',
                'item_metadata',
            ];

            foreach ($immutableFields as $field) {
                if ($item->isDirty($field)) {
                    throw new LogicException('Order item details and pricing snapshots cannot be changed after checkout.');
                }
            }
        });
    }

    public function setFulfillmentStatus(string $status): self
    {
        if (! in_array($status, self::FULFILLMENT_STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported service item fulfillment status.');
        }

        $attributes = ['fulfillment_status' => $status];
        if ($status === 'registration_failed' && $this->item_type === 'domain_registration') {
            $attributes['domain_reservation_key'] = null;
        }

        $this->update($attributes);
        $this->order?->syncFulfillmentStatus();

        return $this;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function hostingPackage(): BelongsTo
    {
        return $this->belongsTo(HostingPackage::class);
    }

    public function hostingServer(): BelongsTo
    {
        return $this->belongsTo(HostingServer::class);
    }

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_item_id');
    }
}
