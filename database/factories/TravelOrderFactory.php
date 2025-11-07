<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TravelOrderFactory extends Factory
{
    public function definition(): array
    {
        // Randomize if this travel is outside or within Surigao del Norte
        $outside = $this->faker->boolean(50);
        $destination = $outside
            ? $this->faker->city() . ', Agusan del Norte'
            : $this->faker->city() . ', Surigao del Norte';

        // Predefined traveler pool
        $names = [
            'Juan Dela Cruz',
            'Maria Santos',
            'Jose Rizal',
            'Ana Dela Peña',
            'Pedro Pascual',
            'Liza Manalo',
            'Mark Villanueva',
            'Grace Navarro',
            'Carlo Mendoza',
        ];

        // Randomly pick 1 or 2 travelers
        $selectedTravelers = collect($names)
            ->shuffle()
            ->take(rand(1, 2))
            ->map(fn ($travelerName) => [
                'name' => $travelerName,
                'position' => $this->faker->randomElement([
                    'Project Technical Assistant I',
                    'Administrative Aide',
                    'Science Research Specialist',
                    'Administrative Assistant II',
                ]),
                'agency' => 'DOST Surigao del Norte',
            ])
            ->values()
            ->all();

        // Random boolean helper for shorter syntax
        $b = fn() => $this->faker->boolean(70); // 70% chance true

        // Random top-level fund source
        $fundSource = $this->faker->randomElement(['General Fund', 'Project Funds', 'Others']);
        $fundDetails = null;

        if ($fundSource === 'Project Funds') {
            $fundDetails = $this->faker->randomElement(['SIDLAK', 'STARBOOKS', 'CEST', 'SMART CITY']);
        } elseif ($fundSource === 'Others') {
            $fundDetails = $this->faker->randomElement(['LGU', 'Private Partner', 'N/A']);
        }

        // Random categories data
        $categories = [
            'actual' => [
                'accommodation' => $b(),
                'meals_food' => $b(),
                'incidental_expenses' => $b(),
            ],
            'per_diem' => [
                'accommodation' => $b(),
                'subsistence' => $b(),
                'incidental_expenses' => $b(),
            ],
            'transportation' => [
                'official_vehicle' => $b(),
                'public_conveyance' => $b(),
                'public_conveyance_text' => $this->faker->optional(0.5)->randomElement(['Bus', 'Airplane', 'Taxi', 'Van']),
            ],
            'others' => $this->faker->optional(0.5)->sentence(3),
        ];

        return [
            'travel_order_no' => 'SDN-' . $this->faker->unique()->numberBetween(1000, 9999),
            'filing_date' => $this->faker->date(),
            'series' => '2025',
            'name' => $selectedTravelers,
            'destination' => $destination,
            'inclusive_dates' => 'November ' . $this->faker->numberBetween(1, 5) . ', 2025',
            'purpose' => 'To conduct project monitoring and coordination with local government partners.',

            // Top-level fund fields (cleaner schema)
            'fund_source' => $fundSource,
            'fund_details' => $fundDetails,

            // Expenses: only categories
            'expenses' => [
                'categories' => $categories,
            ],

            'remarks' => 'Liquidation of travel expenses should follow DOST guidelines and be submitted within seven (7) days after completion of travel.',
            'approved_by' => 'MR. RICARDO N. VARELA',
            'approved_position' => 'OIC, PSTO-SDN',
            'regional_director' => 'ENGR. NOEL M. AJOC',
            'regional_position' => 'Regional Director',
        ];
    }
}
