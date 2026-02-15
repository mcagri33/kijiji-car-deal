<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a search filter for Kijiji car listings.
 */
class KijijiFilter extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_price' => 'integer',
            'max_price' => 'integer',
            'min_year' => 'integer',
            'max_year' => 'integer',
            'max_km' => 'integer',
        ];
    }

    /**
     * Scope to get only active filters.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
