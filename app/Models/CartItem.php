<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'item_type', 'domain_name', 'hosting_package_id',
        'parent_item_id', 'provider', 'provider_product_id', 'quantity',
        'billing_period', 'unit_price', 'line_total', 'currency', 'price_metadata',
    ];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'line_total' => 'decimal:2', 'price_metadata' => 'array'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function hostingPackage(): BelongsTo
    {
        return $this->belongsTo(HostingPackage::class);
    }

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_item_id');
    }

    public function childItems(): HasMany
    {
        return $this->hasMany(self::class, 'parent_item_id');
    }
}
