<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a Kijiji listing that the user is tracking for price/sold notifications.
 */
class TrackedListing extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_tracking' => 'boolean',
            'last_notified_price' => 'integer',
            'notify_on_price_drop' => 'boolean',
            'notify_on_sold' => 'boolean',
        ];
    }

    /**
     * Get the Kijiji listing.
     */
    public function kijijiListing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KijijiListing::class);
    }

    /**
     * Scope for actively tracked listings.
     */
    public function scopeTracking($query)
    {
        return $query->where('is_tracking', true);
    }
}
