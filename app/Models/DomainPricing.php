<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPricing extends Model
{
    protected $table = 'domain_pricing';

    protected $fillable = [
        'extension',
        'registration_cost_ngn',
        'renewal_cost_ngn',
        'markup_ngn',
        'markup_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function sellingPrice(): int
    {
        $base = (int) $this->registration_cost_ngn;
        // A TLD-specific percentage takes precedence when configured.
        // Zero means use the global admin default (40% until changed).
        $percent = (int) $this->markup_percent > 0
            ? (int) $this->markup_percent
            : DomainMarkupSetting::defaultPercent();

        // Percentage is the single source of markup; don't add the legacy fixed field again.
        return $base + (int) round($base * ($percent / 100));
    }
}
