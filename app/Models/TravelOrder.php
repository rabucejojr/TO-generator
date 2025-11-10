<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_order_no',
        'filing_date',
        'series',
        'name',
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

    /*--------------------------------------------------------------
     | LOCATION HELPERS
     --------------------------------------------------------------*/
    public function getIsOutsideProvinceAttribute(): bool
    {
        $homeProvince = 'Surigao del Norte';
        return $this->destination && !str_contains(strtolower($this->destination), strtolower($homeProvince));
    }

    /*--------------------------------------------------------------
     | TRAVELER NAME HELPERS
     --------------------------------------------------------------*/
    public function getNamesAttribute(): string
    {
        $names = is_array($this->name)
            ? $this->name
            : (is_string($this->name) ? json_decode($this->name, true) : []);
        return collect($names)->pluck('name')->filter()->implode(', ') ?: '—';
    }

    /*--------------------------------------------------------------
     | FUND SOURCE HELPERS (now column-based)
     --------------------------------------------------------------*/
    public function getFundSourceSummaryAttribute(): string
    {
        $source = $this->fund_source;
        $details = $this->attributes['fund_details'] ?? null;

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
     | SIGNATORIES
     --------------------------------------------------------------*/
    public function applySignatories(): void
    {
        $isOutside = $this->scope === 'outside';

        if ($isOutside) {
            $this->approved_by = 'MR. RICARDO N. VARELA';
            $this->approved_position = 'OIC, PSTO-SDN';
            $this->regional_director = 'ENGR. NOEL M. AJOC';
            $this->regional_position = 'Regional Director';
        } else {
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
                    'name' => $this->approved_by,
                    'position' => $this->approved_position,
                ],
                'approved' => [
                    'label' => 'Approved:',
                    'name' => $this->regional_director,
                    'position' => $this->regional_position,
                ],
            ];
        }

        return [
            'approved' => [
                'label' => 'Approved:',
                'name' => $this->approved_by,
                'position' => $this->approved_position,
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

        // 🧠 Auto-generate Travel Order Number only for "within" travels
        static::creating(function ($travelOrder) {
            $year = now()->year;
            $travelOrder->series = $year;

            if ($travelOrder->scope === 'within') {
                $travelOrder->travel_order_no = self::generateTravelOrderNumber($year);
            } else {
                // ❌ Outside travels should have no number
                $travelOrder->travel_order_no = null;
            }
        });
    }

    /**
     * Generate incremental Travel Order number (e.g. SDN-2025-0001)
     */
    protected static function generateTravelOrderNumber(int $year): string
    {
        // Find the latest "within province" travel order number for this year
        $latest = self::where('scope', 'within')
            ->whereNotNull('travel_order_no')
            ->where('travel_order_no', 'like', "SDN-{$year}-%")
            ->latest('id')
            ->value('travel_order_no');

        $next = 1;
        if ($latest && preg_match('/(\d{4})$/', $latest, $matches)) {
            $next = intval($matches[1]) + 1;
        }

        return sprintf('SDN-%s-%04d', $year, $next);
    }
}
