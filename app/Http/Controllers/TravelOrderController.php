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
     * Show the form for editing the specified travel order.
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
        ]);

        // Build the expenses JSON structure
        $expenses = [
            'fund_sources' => [
                'general_fund' => $request->boolean('fund_sources.general_fund'),
                'project_funds' => $request->boolean('fund_sources.project_funds'),
                'others' => $request->input('fund_sources.others_text'),
            ],
            'categories' => $request->input('categories', []),
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
        ]);

        $expenses = [
            'fund_sources' => [
                'general_fund' => $request->boolean('fund_sources.general_fund'),
                'project_funds' => $request->boolean('fund_sources.project_funds'),
                'others' => $request->input('fund_sources.others_text'),
            ],
            'categories' => $request->input('categories', []),
        ];

    $travelOrder->update([
        'name' => $validated['name'],
        'destination' => $validated['destination'],
        'inclusive_dates' => $validated['inclusive_dates'],
        'purpose' => $validated['purpose'],
        'expenses' => $expenses,
        'fund_source' => $fundSource,   // ✅ add this
        'fund_details' => $fundDetails, // ✅ add this
        'scope' => $scope,
        'approved_by' => $approvedBy,
        'approved_position' => $approvedPosition,
        'regional_director' => $recommendingApproval['name'] ?? null,
        'regional_position' => $recommendingApproval['position'] ?? null,
    ]);

    //test
    // dd($request->fund_details);

    return redirect()
        ->route('travel_order.edit', $travelOrder)
        ->with('success', 'Travel Order updated successfully.');
}

    /**
     * Remove the specified travel order.
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

        // Ensure decoded arrays for PDF
        $travelOrder->name = is_string($travelOrder->name)
            ? json_decode($travelOrder->name, true)
            : $travelOrder->name;

        $travelOrder->expenses = is_string($travelOrder->expenses)
            ? json_decode($travelOrder->expenses, true)
            : $travelOrder->expenses;

        $fundSource = $travelOrder->fund_source ?? null;
        $fundDetails = $travelOrder->fund_details ?? (
            $travelOrder->expenses['fund_sources']['project_funds_details'] ??
            $travelOrder->expenses['fund_sources']['others'] ??
            null
        );

        // ✅ Pass all needed data into the Blade view
        $pdf = Pdf::loadView('travel_order.template', [
            'travelOrder' => $travelOrder,
            'fundSource' => $fundSource,
            'fundDetails' => $fundDetails,
        ])->setPaper('A4', 'portrait');

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
