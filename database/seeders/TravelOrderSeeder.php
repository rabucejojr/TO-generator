<?php

namespace Database\Seeders;

use App\Models\TravelOrder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TravelOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TravelOrder::factory()->count(5)->create();
    }
}
