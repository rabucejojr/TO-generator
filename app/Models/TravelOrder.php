<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_order_no',
        'filing_date',
        'series',
        'name',
        'position',
        'division_agency',
        'destination',
        'inclusive_dates',
        'purpose',
        'expenses',
        'remarks',
        'approved_by',
        'approved_position',
        'regional_director',
        'regional_position',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'name'=>'array',
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

    public function getNamesAttribute(): string
    {
        $names = is_array($this->name)
            ? $this->name
            : (is_string($this->name)
                ? json_decode($this->name, true)
                : []);

        return collect($names)->pluck('name')->filter()->implode(', ') ?: '—';
    }

}
