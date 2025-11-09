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
        'scope', // 'within' or 'outside'
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

    /*--------------------------------------------------------------
     | LOCATION HELPERS
     --------------------------------------------------------------*/

    /**
     * Determine if the travel is outside Surigao del Norte.
     */
    public function getIsOutsideProvinceAttribute(): bool
    {
        $homeProvince = 'Surigao del Norte';
        return $this->destination && !str_contains(strtolower($this->destination), strtolower($homeProvince));
    }

    /*--------------------------------------------------------------
     | NAME HELPERS
     --------------------------------------------------------------*/

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

    public function getFundSourcesAttribute(): array
    {
        return $this->expenses['fund_sources'] ?? [];
    }

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

    public function getFundSourceSummaryAttribute(): string
    {
        $source = $this->fund_source;
        $details = $this->fund_details;

        if (!$source) {
            return '—';
        }

        return $details ? "{$source} ({$details})" : $source;
    }

    /*--------------------------------------------------------------
     | CATEGORY HELPERS
     --------------------------------------------------------------*/

    public function getCategoriesAttribute(): array
    {
        return $this->expenses['categories'] ?? [];
    }

    public function isExpenseChecked(string $section, ?string $subItem = null): bool
    {
        $categories = data_get($this->expenses, 'categories', []);

        if (array_key_exists($section, $categories) && !is_array($categories[$section])) {
            return filter_var($categories[$section], FILTER_VALIDATE_BOOLEAN);
        }

        $cat = $categories[$section] ?? [];

        if ($subItem) {
            return filter_var(data_get($cat, $subItem, false), FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(data_get($cat, 'enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function getPublicConveyanceTextAttribute(): ?string
    {
        return $this->categories['transportation']['public_conveyance_text'] ?? null;
    }

    /*--------------------------------------------------------------
     | SIGNATORIES LOGIC
     --------------------------------------------------------------*/

    public function applySignatories(): void
    {
        $isOutside = $this->scope === 'outside';

        if ($isOutside) {
            // Outside Province
            $this->approved_by = 'MR. RICARDO N. VARELA';
            $this->approved_position = 'OIC, PSTO-SDN';
            $this->regional_director = 'ENGR. NOEL M. AJOC';
            $this->regional_position = 'Regional Director';
        } else {
            // Within Province
            $this->approved_by = 'MR. RICARDO N. VARELA';
            $this->approved_position = 'OIC, PSTO-SDN';
            $this->regional_director = null;
            $this->regional_position = null;
        }

        if ($this->exists) {
            $this->save();
        }
    }

    public function getSignatoriesAttribute(): array
    {
        $this->applySignatories();

        if ($this->scope === 'outside') {
            return [
                'recommending' => [
                    'label' => 'Recommending Approval:',
                    'name' => $this->approved_by ?? 'MR. RICARDO N. VARELA',
                    'position' => $this->approved_position ?? 'OIC, PSTO-SDN',
                ],
                'approved' => [
                    'label' => 'Approved:',
                    'name' => $this->regional_director ?? 'MR. RICARDO N. VARELA',
                    'position' => $this->regional_position ?? 'OIC, PSTO-SDN',
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

    /*--------------------------------------------------------------
     | MODEL EVENTS (SYNC + AUTO-NUMBER)
     --------------------------------------------------------------*/

    protected static function booted()
    {
        // 🔄 Keep expenses JSON in sync with fund_source & fund_details
        static::saving(function ($travelOrder) {
            $expenses = $travelOrder->expenses ?? [];

            $travelOrder->expenses = array_merge($expenses, [
                'fund_source'  => $travelOrder->fund_source,
                'fund_details' => $travelOrder->fund_details,
            ]);
        });

        // 🔄 Reflect JSON data back into columns on retrieval
        static::retrieved(function ($travelOrder) {
            $expenses = $travelOrder->expenses ?? [];

            if (empty($travelOrder->fund_source) && isset($expenses['fund_source'])) {
                $travelOrder->fund_source = $expenses['fund_source'];
            }

            if (empty($travelOrder->fund_details) && isset($expenses['fund_details'])) {
                $travelOrder->fund_details = $expenses['fund_details'];
            }
        });

        // 🧠 Conditional Travel Order Numbering
        static::creating(function ($travelOrder) {
            if ($travelOrder->scope === 'within') {
                $travelOrder->travel_order_no = self::generateTravelOrderNumber();
            } else {
                $travelOrder->travel_order_no = null;
            }
        });
    }

    /**
     * Generate incremental Travel Order number (e.g. 2025-SDN-0001)
     */
    protected static function generateTravelOrderNumber(): string
    {
        $year = now()->format('Y');

        // Get the latest travel order for the current year only
        $latest = self::whereNotNull('travel_order_no')
            ->where('travel_order_no', 'like', "{$year}-%") // filter by year prefix
            ->latest('id')
            ->value('travel_order_no');

        // Extract the numeric sequence (e.g. 0001)
        $next = 1;
        if ($latest) {
            preg_match('/(\d{4})$/', $latest, $matches);
            $next = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        }

        // Format: YYYY-SDN-XXXX
        return sprintf('%s-SDN-%04d', $year, $next);
    }
}
