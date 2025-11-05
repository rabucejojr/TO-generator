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
            $table->string('travel_order_no')->unique()->nullable();
            $table->date('filing_date');
            $table->string('series')->default('2025');

            // Personnel Information
            $table->json('name');
            $table->string('position')->nullable();
            $table->string('division_agency')->nullable();

            // Travel Details
            $table->string('destination');
            $table->string('inclusive_dates')->nullable();
            $table->text('purpose')->nullable();

            // Travel Expenses - structured JSON field
            $table->json('expenses')->nullable();

            // Remarks / Special Instructions
            $table->text('remarks')->nullable();

            // Approval
            $table->string('approved_by')->default('MR. RICARDO N. VARELA');
            $table->string('approved_position')->default('OIC, PSTO-SDN');
            $table->string('regional_director')->default('ENGR. NOEL M. AJOC');
            $table->string('regional_position')->default('REGIONAL DIRECTOR');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};
