<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();

            // Travel order identification
            $table->string('travel_order_no')->unique()->nullable();
            $table->year('series')->default(date('Y'));
            $table->date('filing_date')->nullable();
            $table->string('scope')->default('within'); // within or outside province

            // Personnel Information (multiple travelers)
            // Stored as JSON array: [{ name, position, agency }, ...]
            $table->json('name')->nullable();

            // Travel details
            $table->string('destination')->nullable();
            $table->string('inclusive_dates')->nullable();
            $table->text('purpose')->nullable();
            $table->string('fund_source')->nullable();
            $table->string('fund_details')->nullable();

            // Travel expenses: fund sources + categories (nested JSON)
            // Structure:
            // {
            //   "fund_sources": {
            //     "general_fund": true,
            //     "project_funds": true,
            //     "project_funds_details": "SIDLAK",
            //     "others": "LGU"
            //   },
            //   "categories": {
            //     "actual": { "enabled": true, "accommodation": true, ... },
            //     "per_diem": { "enabled": false, ... },
            //     "transportation": { "enabled": true, "public_conveyance_text": "Bus" },
            //     "others_enabled": false
            //   }
            // }
            $table->json('expenses')->nullable();

            // Optional remarks or instructions
            $table->text('remarks')->nullable();

            // Approval information
            $table->string('approved_by')->default('MR. RICARDO N. VARELA');
            $table->string('approved_position')->default('OIC, PSTO-SDN');
            $table->string('regional_director')->nullable();
            $table->string('regional_position')->nullable();

            $table->timestamps();

            // Helpful indexes for quick lookups
            $table->index('series');
            $table->index('scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
