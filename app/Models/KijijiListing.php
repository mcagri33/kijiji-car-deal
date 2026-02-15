<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a Kijiji car listing.
 */
class KijijiListing extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'year' => 'integer',
            'mileage' => 'integer',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Get the tracked listing record.
     */
    public function trackedListing(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TrackedListing::class);
    }

    /**
     * Check if this listing is being tracked.
     */
    public function isTracked(): bool
    {
        return $this->trackedListing()->exists();
    }

    /**
     * Scope for active listings only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for sold listings.
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }
}
