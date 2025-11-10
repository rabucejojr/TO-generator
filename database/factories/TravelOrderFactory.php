<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TravelOrder;

class TravelOrderFactory extends Factory
{
    public function definition(): array
    {
        // Randomize travel scope
        $isOutside = $this->faker->boolean(50);
        $scope = $isOutside ? 'outside' : 'within';

        // Randomize destination based on scope
        $destination = $isOutside
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

        // Randomly pick 1–2 travelers
        $selectedTravelers = collect($names)
            ->shuffle()
            ->take(rand(1, 2))
            ->map(fn($travelerName) => [
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

        // Random boolean helper (70% true)
        $b = fn() => $this->faker->boolean(70);

        // Expense categories
        $categories = [
            'actual' => [
                'enabled' => $b(),
                'accommodation' => $b(),
                'meals_food' => $b(),
                'incidental_expenses' => $b(),
            ],
            'per_diem' => [
                'enabled' => $b(),
                'accommodation' => $b(),
                'subsistence' => $b(),
                'incidental_expenses' => $b(),
            ],
            'transportation' => [
                'enabled' => $b(),
                'official_vehicle' => $b(),
                'public_conveyance' => $b(),
                'public_conveyance_text' => $this->faker->optional(0.5)->randomElement([
                    'Bus', 'Airplane', 'Taxi', 'Van'
                ]),
            ],
            'others_enabled' => $b(),
        ];

        // Fund Source logic
        $fundSources = ['General Fund', 'Project Funds', 'Others'];
        $fundSource = $this->faker->randomElement($fundSources);
        $fundDetails = '';

        if ($fundSource === 'Project Funds') {
            $fundDetails = $this->faker->randomElement(['SIDLAK', 'STARBOOKS', 'CEST', 'SMART CITY']);
        } elseif ($fundSource === 'Others') {
            $fundDetails = $this->faker->randomElement(['LGU Counterpart', 'Private Sponsorship', 'Personal Contribution']);
        }

        // Signatories
        $approvedBy = 'MR. RICARDO N. VARELA';
        $approvedPosition = 'OIC, PSTO-SDN';
        $regionalDirector = $scope === 'outside' ? 'ENGR. NOEL M. AJOC' : null;
        $regionalPosition = $scope === 'outside' ? 'Regional Director' : null;

        return [
            // 🚫 travel_order_no is auto-generated in the model
            'filing_date' => $this->faker->date(),
            'series' => now()->year,
            'scope' => $scope,
            'name' => $selectedTravelers,
            'destination' => $destination,
            'inclusive_dates' => 'November ' . $this->faker->numberBetween(1, 5) . ', ' . now()->year,
            'purpose' => 'To conduct project monitoring and coordination with local government partners.',
            'fund_source' => $fundSource,
            'fund_details' => $fundDetails,
            'expenses' => [
                'categories' => $categories,
                'fund_source' => $fundSource,
                'fund_details' => $fundDetails,
            ],
            'remarks' => 'Liquidation of travel expenses should follow DOST guidelines and be submitted within seven (7) days after completion of travel.',
            'approved_by' => $approvedBy,
            'approved_position' => $approvedPosition,
            'regional_director' => $regionalDirector,
            'regional_position' => $regionalPosition,
        ];
    }
}
