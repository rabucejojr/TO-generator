<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_order_no',
        'filing_date',
        'series',
        'name', // array containing name, position, agency
        'destination',
        'inclusive_dates',
        'purpose',
        'fund_source',
        'fund_details',
        'expenses',
        'remarks',
        'scope',
        'approved_by',
        'approved_position',
        'regional_director',
        'regional_position',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'name' => 'array',
        'expenses' => 'array',
    ];

    /**
     * Determine if the travel is outside Surigao del Norte.
     */
    public function getIsOutsideProvinceAttribute(): bool
    {
        $homeProvince = 'Surigao del Norte';
        return $this->destination && !str_contains(strtolower($this->destination), strtolower($homeProvince));
    }

    /**
     * Get comma-separated list of traveler names.
     */
    public function getNamesAttribute(): string
    {
        $names = is_array($this->name)
            ? $this->name
            : (is_string($this->name)
                ? json_decode($this->name, true)
                : []);

        return collect($names)->pluck('name')->filter()->implode(', ') ?: '—';
    }

    /*--------------------------------------------------------------
     | CATEGORY HELPERS
     --------------------------------------------------------------*/

    /**
     * Get all expense categories safely
     */
    public function getCategoriesAttribute(): array
    {
        return $this->expenses['categories'] ?? [];
    }

    /**
     * Check if a category or sub-item is checked
     * Example:
     *   $travelOrder->isExpenseChecked('actual') → section enabled?
     *   $travelOrder->isExpenseChecked('actual', 'accommodation') → sub-item?
     */
    public function isExpenseChecked(string $section, ?string $subItem = null): bool
    {
        $cat = $this->categories[$section] ?? [];

        if ($subItem) {
            return !empty($cat[$subItem]);
        }

        return !empty($cat['enabled']);
    }

    /**
     * Get public conveyance note if applicable
     */
    public function getPublicConveyanceTextAttribute(): ?string
    {
        return $this->categories['transportation']['public_conveyance_text'] ?? null;
    }
}
