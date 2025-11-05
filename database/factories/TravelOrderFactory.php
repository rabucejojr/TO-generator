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

        // Predefined list of possible traveler names
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

        // Randomly pick 1 or 2 names from the list
        $randomNames = collect($names)->shuffle()->take(rand(1, 2))->values()->all();

        return [
            'travel_order_no' => 'SDN-' . $this->faker->unique()->numberBetween(1000, 9999),
            'filing_date' => $this->faker->date(),
            'series' => '2025',
            'name' => $randomNames,
            'position' => $this->faker->randomElement([
                'Project Technical Assistant I',
                'Administrative Aide',
                'Science Research Specialist',
                'Administrative Assistant II'
            ]),
            'division_agency' => 'PSTO-SDN',
            'destination' => $destination,
            'inclusive_dates' => 'November ' . $this->faker->numberBetween(1, 5) . ', 2025',
            'purpose' => 'To conduct project monitoring and coordination with local government partners.',

            // Expenses structured JSON
            'expenses' => [
                'fund_sources' => [
                    'general_fund' => $this->faker->boolean(),
                    'project_funds' => $this->faker->boolean(),
                    'others' => $this->faker->optional()->sentence(3),
                ],
                'categories' => [
                    'actual' => [
                        'accommodation' => $this->faker->boolean(),
                        'meals_food' => $this->faker->boolean(),
                        'incidental_expenses' => $this->faker->boolean(),
                    ],
                    'per_diem' => [
                        'accommodation' => $this->faker->boolean(),
                        'subsistence' => $this->faker->boolean(),
                        'incidental_expenses' => $this->faker->boolean(),
                    ],
                    'transportation' => [
                        'official_vehicle' => $this->faker->boolean(),
                        'public_conveyance' => $this->faker->randomElement([
                            'Bus', 'Taxi', 'Airplane', null
                        ]),
                    ],
                    'others' => $this->faker->optional()->sentence(4),
                ]
            ],

            'remarks' => 'Liquidation of travel expenses should follow DOST guidelines and be submitted within seven (7) days after completion of travel.',
            'approved_by' => 'MR. RICARDO N. VARELA',
            'approved_position' => 'OIC, PSTO-SDN',
            'regional_director' => 'ENGR. NOEL M. AJOC',
            'regional_position' => 'Regional Director',
        ];
    }
}
