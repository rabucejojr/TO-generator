<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TravelOrderController extends Controller
{
    /**
     * Display a listing of all travel orders.
     */
    public function index()
    {
        $travelOrders = TravelOrder::latest()->paginate(10);
        return view('travel_order.index', compact('travelOrders'));
    }

    /**
     * Show the form for creating a new travel order.
     */
    public function create()
    {
        return view('travel_order.create');
    }

    /**
     * Store a newly created travel order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array|min:1',
            'name.*.name' => 'required|string|max:255',
            'name.*.position' => 'nullable|string|max:255',
            'name.*.division_agency' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'inclusive_dates' => 'required|string|max:255',
            'purpose' => 'required|string',
            'fund_sources.selected' => 'required|in:general_fund,project_funds,others',
        ]);

        // Build the expenses JSON structure
        $selectedFund = $request->input('fund_sources.selected');

        $expenses = [
            'fund_sources' => [
                'general_fund' => $selectedFund === 'general_fund',
                'project_funds' => $selectedFund === 'project_funds',
                'project_funds_text' => $selectedFund === 'project_funds' ? $request->input('fund_sources.project_funds_text') : null,
                'others' => $selectedFund === 'others' ? $request->input('fund_sources.others_text') : null,
            ],
            'categories' => [
                'actual_enabled' => $request->boolean('categories.actual_enabled'),
                'per_diem_enabled' => $request->boolean('categories.per_diem_enabled'),
                'transportation_enabled' => $request->boolean('categories.transportation_enabled'),

                'actual' => [
                    'accommodation' => $request->boolean('categories.actual.accommodation'),
                    'meals_food' => $request->boolean('categories.actual.meals_food'),
                    'incidental_expenses' => $request->boolean('categories.actual.incidental_expenses'),
                ],
                'per_diem' => [
                    'accommodation' => $request->boolean('categories.per_diem.accommodation'),
                    'subsistence' => $request->boolean('categories.per_diem.subsistence'),
                    'incidental_expenses' => $request->boolean('categories.per_diem.incidental_expenses'),
                ],
                'transportation' => [
                    'official_vehicle' => $request->boolean('categories.transportation.official_vehicle'),
                    'public_conveyance' => $request->input('categories.transportation.public_conveyance'),
                ],
                'others' => $request->input('categories.others'),
            ],
        ];


        // Determine current series (year)
        $series = now()->year;

        // Find the latest travel order for this year
        $lastOrder = TravelOrder::where('series', $series)
            ->orderByDesc('id')
            ->first();

        // Starting number (0114 means base integer 114)
        $startValue = 114;

        // Extract last sequence number (e.g. from "SDN-2025-0125")
        if ($lastOrder && preg_match('/SDN-' . $series . '-(\d+)/', $lastOrder->travel_order_no, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = $startValue;
        }

        // Format number as four digits with leading zeros
        $travelOrderNo = 'SDN-' . $series . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);    
        // Default filing_date is today if not provided
        $filingDate = $validated['filing_date'] ?? now();

        $travelOrder = TravelOrder::create([
            'travel_order_no' => $travelOrderNo,
            'series' => now()->year,
            'filing_date' => $filingDate,
            'name' => $validated['name'], // stored as array
            'destination' => $validated['destination'],
            'inclusive_dates' => $validated['inclusive_dates'],
            'purpose' => $validated['purpose'],
            'expenses' => $expenses,
            'fund_sources.selected' => 'required|in:general_fund,project_funds,others',
            'fund_sources.project_funds_text' => 'nullable|string|max:255',
            'fund_sources.others_text' => 'nullable|string|max:255',
        ]);

        return redirect()
            ->route('travel_order.edit', $travelOrder)
            ->with('success', 'Travel Order created successfully.');
    }

    /**
     * Show the form for editing the specified travel order.
     */
    public function edit(TravelOrder $travelOrder)
    {
        return view('travel_order.edit', compact('travelOrder'));
    }

    /**
     * Update the specified travel order.
     */
    public function update(Request $request, TravelOrder $travelOrder)
    {
        $validated = $request->validate([
            'name' => 'required|array|min:1',
            'name.*.name' => 'required|string|max:255',
            'name.*.position' => 'nullable|string|max:255',
            'name.*.division_agency' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'inclusive_dates' => 'required|string|max:255',
            'purpose' => 'required|string',
            'fund_sources.selected' => 'required|in:general_fund,project_funds,others',
            'fund_sources.project_funds_text' => 'nullable|string|max:255',
            'fund_sources.others_text' => 'nullable|string|max:255',
        ]);

        $selectedFund = $request->input('fund_sources.selected');

        $expenses = [
            'fund_sources' => [
                'general_fund' => $selectedFund === 'general_fund',
                'project_funds' => $selectedFund === 'project_funds',
                'project_funds_text' => $selectedFund === 'project_funds' ? $request->input('fund_sources.project_funds_text') : null,
                'others' => $selectedFund === 'others' ? $request->input('fund_sources.others_text') : null,
            ],
            'categories' => $request->input('categories', []),
        ];

        $travelOrder->update([
            'name' => $validated['name'],
            'destination' => $validated['destination'],
            'inclusive_dates' => $validated['inclusive_dates'],
            'purpose' => $validated['purpose'],
            'expenses' => $expenses,
        ]);

        return redirect()
            ->route('travel_order.edit', $travelOrder)
            ->with('success', 'Travel Order updated successfully.');
    }

    /**
     * Remove the specified travel order from storage.
     */
    public function destroy(TravelOrder $travelOrder)
    {
        $travelOrder->delete();

        return redirect()
            ->route('travel_order.index')
            ->with('success', 'Travel Order deleted successfully.');
    }

    /**
     * Preview a specific Travel Order PDF template.
     */
    public function preview($id)
    {
        $travelOrder = TravelOrder::findOrFail($id);

        if (is_string($travelOrder->name)) {
            $travelOrder->name = json_decode($travelOrder->name, true);
        }

        if (is_string($travelOrder->expenses)) {
            $travelOrder->expenses = json_decode($travelOrder->expenses, true);
        }

        $pdf = Pdf::loadView('travel_order.template', compact('travelOrder'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("travel_order_{$travelOrder->travel_order_no}.pdf");
    }

    /**
     * Generate PDF for a specific travel order record.
     */
    public function generate(TravelOrder $travelOrder)
    {
        $pdf = Pdf::loadView('travel_order.pdf', compact('travelOrder'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("travel_order_{$travelOrder->travel_order_no}.pdf");
    }
}
