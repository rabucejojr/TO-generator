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
     | FUND SOURCE HELPERS
     --------------------------------------------------------------*/

    /**
     * Get all fund sources safely
     */
    public function getFundSourcesAttribute(): array
    {
        return $this->expenses['fund_sources'] ?? [];
    }

    /**
     * Determine which fund source is selected (General, Project, or Others)
     */
    public function getActiveFundSourceAttribute(): ?string
    {
        $fund = $this->fund_sources;

        if (!empty($fund['general_fund'])) {
            return 'General Fund';
        } elseif (!empty($fund['project_funds'])) {
            return 'Project Funds';
        } elseif (!empty($fund['others'])) {
            return 'Others';
        }

        return null;
    }

    /**
     * Get fund details depending on active fund type
     * Example: (CEST) or (LGU)
     */
    public function getFundDetailsAttribute(): ?string
    {
        $fund = $this->fund_sources;

        if (!empty($fund['project_funds']) && !empty($fund['project_funds_details'])) {
            return $fund['project_funds_details'];
        }

        if (!empty($fund['others'])) {
            return $fund['others'];
        }

        return null;
    }

    /**
     * Get formatted string summary, e.g. "Project Funds (SIDLAK)"
     */
    public function getFundSourceSummaryAttribute(): string
    {
        $source = $this->active_fund_source;
        $details = $this->fund_details;

        if (!$source) {
            return '—';
        }

        return $details ? "{$source} ({$details})" : $source;
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
        // Safely access nested "categories" array
        $categories = data_get($this->expenses, 'categories', []);

        // ✅ Handle flat keys like "others_enabled" (can be string, int, bool, or null)
        if (array_key_exists($section, $categories) && !is_array($categories[$section])) {
            return filter_var($categories[$section], FILTER_VALIDATE_BOOLEAN);
        }

        // Handle nested expense sections (actual, per_diem, transportation)
        $cat = $categories[$section] ?? [];

        if ($subItem) {
            return filter_var(data_get($cat, $subItem, false), FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(data_get($cat, 'enabled', false), FILTER_VALIDATE_BOOLEAN);
    }




    /**
     * Get public conveyance note if applicable
     */
    public function getPublicConveyanceTextAttribute(): ?string
    {
        return $this->categories['transportation']['public_conveyance_text'] ?? null;
    }

        /**
     * Automatically apply correct signatories based on destination or scope.
     *
     * - Within Surigao del Norte → Approved by: Mr. Ricardo N. Varela
     * - Outside Surigao del Norte → Recommending Approval: Mr. Varela, Approved by: Engr. Ajoc
     */
    public function applySignatories(): void
    {
        // Detect if travel is outside province using model logic
        $isOutside = $this->is_outside_province;

        if ($isOutside) {
            // Outside province
            $this->approved_by = 'ENGR. NOEL M. AJOC';
            $this->approved_position = 'Regional Director, DOST Caraga';
            $this->regional_director = 'MR. RICARDO N. VARELA';
            $this->regional_position = 'OIC, PSTO-SDN';
        } else {
            // Within province
            $this->approved_by = 'MR. RICARDO N. VARELA';
            $this->approved_position = 'OIC, PSTO-SDN';
            $this->regional_director = null;
            $this->regional_position = null;
        }

        // Save immediately if model already exists
        if ($this->exists) {
            $this->save();
        }
    }

    /**
 * Return structured signatory data ready for display.
 *
 * @return array
 */
public function getSignatoriesAttribute(): array
{
    $this->applySignatories(); // ensure it's up to date

    if ($this->is_outside_province) {
        return [
            'recommending' => [
                'label' => 'Recommending Approval:',
                'name' => $this->approved_by ?? 'MR. RICARDO N. VARELA',
                'position' => $this->approved_position ?? 'OIC, PSTO-SDN',

                // 'name' => $this->regional_director ?? 'MR. RICARDO N. VARELA',
                // 'position' => $this->regional_position ?? 'OIC, PSTO-SDN',
            ],
            'approved' => [
                'label' => 'Approved:',
                'name' => $this->regional_director ?? 'ENGR. NOEL M. AJOC',
                'position' => $this->approved_position ?? 'Regional Director, DOST Caraga',
            ],
        ];
    }

    return [
        'approved' => [
            'label' => 'Approved:',
            'name' => $this->approved_by ?? 'MR. RICARDO N. VARELA',
            'position' => $this->approved_position ?? 'OIC, PSTO-SDN',
        ],
    ];
}


}
