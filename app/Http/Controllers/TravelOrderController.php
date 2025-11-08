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
    public function edit(TravelOrder $travelOrder)
    {
        return view('travel_order.edit', compact('travelOrder'));
    }

public function store(Request $request, TravelOrder $travelOrder)
{
    $validated = $request->validate([
        'name' => 'required|array|min:1',
        'name.*.name' => 'required|string|max:255',
        'name.*.position' => 'nullable|string|max:255',
        'name.*.agency' => 'nullable|string|max:255',
        'destination' => 'required|string|max:255',
        'inclusive_dates' => 'required|string|max:255',
        'purpose' => 'required|string',
        'scope' => 'nullable|string|in:within,outside',
        'expenses' => 'nullable|array',
        'fund_source' => 'string|in:General Fund,Project Funds,Others',
        'fund_details' => 'string|max:255',
    ]);

    /* -------------------------------
     * Normalize categories
     * ------------------------------- */
    $categories = array_replace_recursive([
        'actual' => [
            'enabled' => false,
            'accommodation' => false,
            'meals_food' => false,
            'incidental_expenses' => false,
        ],
        'per_diem' => [
            'enabled' => false,
            'accommodation' => false,
            'subsistence' => false,
            'incidental_expenses' => false,
        ],
        'transportation' => [
            'enabled' => false,
            'official_vehicle' => false,
            'public_conveyance' => false,
            'public_conveyance_text' => '',
        ],
        'others_enabled' => false,
    ], $request->input('expenses.categories', []));

    $expenses = [
        'categories' => $categories,
    ];
    $fundSource = $request->input('fund_source');
    $fundDetails = $request->input('fund_details');
    /* -------------------------------
     * Generate Travel Order Number
     * ------------------------------- */
    $series = now()->year;
    $lastOrder = TravelOrder::where('series', $series)
        ->whereNotNull('travel_order_no')
        ->orderByDesc('id')
        ->first();

    if ($lastOrder && preg_match('/SDN-\d{4}-(\d+)/', $lastOrder->travel_order_no, $matches)) {
        $lastNumber = (int) $matches[1];
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 114; // your preferred starting number
    }

    $travelOrderNo = sprintf('SDN-%s-%04d', $series, $nextNumber);


    /* -------------------------------
     * Determine Signatories
     * ------------------------------- */
    $scope = $request->input('scope', 'within');

    // if ($scope === 'outside') {
    //     $approvedBy = 'ENGR. NOEL M. AJOC';
    //     $approvedPosition = 'Regional Director';
    //     $recommendingApproval = [
    //         'name' => 'MR. RICARDO N. VARELA',
    //         'position' => 'OIC, PSTO-SDN',
    //     ];
    // } else {
    //     $approvedBy = 'MR. RICARDO N. VARELA';
    //     $approvedPosition = 'OIC, PSTO-SDN';
    //     $recommendingApproval = null;
    // }
    $travelOrder->applySignatories();

    /* -------------------------------
     * Create Travel Order
     * ------------------------------- */
    $travelOrder = TravelOrder::create([
        'travel_order_no' => $travelOrderNo,
        'series' => $series,
        'filing_date' => now(),
        'name' => $validated['name'],
        'destination' => $validated['destination'],
        'inclusive_dates' => $validated['inclusive_dates'],
        'purpose' => $validated['purpose'],
        'fund_source' => $fundSource,   // ✅ add this
        'fund_details' => $fundDetails, // ✅ add this
        'expenses' => $expenses,
        'scope' => $scope,
        // 'approved_by' => $approvedBy,
        // 'approved_position' => $approvedPosition,
        'regional_director' => $recommendingApproval['name'] ?? null,
        'regional_position' => $recommendingApproval['position'] ?? null,
    ]);

    // dd($expenses['categories']['actual']);

    return redirect()
        ->route('travel_order.edit', $travelOrder)
        ->with('success', 'Travel Order created successfully.');
}

public function update(Request $request, TravelOrder $travelOrder)
{
    $validated = $request->validate([
        'name' => 'required|array|min:1',
        'destination' => 'required|string|max:255',
        'inclusive_dates' => 'required|string|max:255',
        'purpose' => 'required|string',
        'scope' => 'nullable|string|in:within,outside',
        'expenses' => 'nullable|array',
        'fund_source' => 'nullable|string|in:General Fund,Project Funds,Others',
        'fund_details' => 'nullable|string|max:255',
    ]);

    /* -------------------------------
     * Normalize categories
     * ------------------------------- */
    $categories = array_replace_recursive([
        'actual' => [
            'enabled' => false,
            'accommodation' => false,
            'meals_food' => false,
            'incidental_expenses' => false,
        ],
        'per_diem' => [
            'enabled' => false,
            'accommodation' => false,
            'subsistence' => false,
            'incidental_expenses' => false,
        ],
        'transportation' => [
            'enabled' => false,
            'official_vehicle' => false,
            'public_conveyance' => false,
            'public_conveyance_text' => '',
        ],
        'others_enabled' => false,
    ], $request->input('expenses.categories', []));

    $expenses = [
        'categories' => $categories,
    ];
    $fundSource = $request->input('fund_source');
    $fundDetails = $request->input('fund_details');

    $scope = $request->input('scope', 'within');

    // if ($scope === 'outside') {
    //     $approvedBy = 'ENGR. NOEL M. AJOC';
    //     $approvedPosition = 'Regional Director';
    //     $recommendingApproval = [
    //         'name' => 'MR. RICARDO N. VARELA',
    //         'position' => 'OIC, PSTO-SDN',
    //     ];
    // } else {
    //     $approvedBy = 'MR. RICARDO N. VARELA';
    //     $approvedPosition = 'OIC, PSTO-SDN';
    //     $recommendingApproval = null;
    // }

    $travelOrder->applySignatories();

    $travelOrder->update([
        'name' => $validated['name'],
        'destination' => $validated['destination'],
        'inclusive_dates' => $validated['inclusive_dates'],
        'purpose' => $validated['purpose'],
        'expenses' => $expenses,
        'fund_source' => $fundSource,   // ✅ add this
        'fund_details' => $fundDetails, // ✅ add this
        'scope' => $scope,
        // 'approved_by' => $approvedBy,
        // 'approved_position' => $approvedPosition,
        'regional_director' => $recommendingApproval['name'] ?? null,
        'regional_position' => $recommendingApproval['position'] ?? null,
    ]);

    //test
    // dd($request->expenses);

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
    // public function preview($id)
    // {
    //     $travelOrder = TravelOrder::findOrFail($id);

    //     // Ensure decoded arrays for PDF
    //     $travelOrder->name = is_string($travelOrder->name)
    //         ? json_decode($travelOrder->name, true)
    //         : $travelOrder->name;

    //     $travelOrder->expenses = is_string($travelOrder->expenses)
    //         ? json_decode($travelOrder->expenses, true)
    //         : $travelOrder->expenses;

    //     $fundSource = $travelOrder->fund_source ?? null;
    //     $fundDetails = $travelOrder->fund_details ?? (
    //         $travelOrder->expenses['fund_sources']['project_funds_details'] ??
    //         $travelOrder->expenses['fund_sources']['others'] ??
    //         null
    //     );

    //     // ✅ Pass all needed data into the Blade view
    //     $pdf = Pdf::loadView('travel_order.template', [
    //         'travelOrder' => $travelOrder,
    //         'fundSource' => $fundSource,
    //         'fundDetails' => $fundDetails,
    //     ])->setPaper('A4', 'portrait');

    //     return $pdf->stream("travel_order_{$travelOrder->travel_order_no}.pdf");
    // }

        /**
     * Preview and Download the specific Travel Order PDF template.
     */
    public function preview($id)
    {
        $travelOrder = TravelOrder::findOrFail($id)->refresh();

        $pdf = Pdf::loadView('travel_order.template', [
            'travelOrder' => $travelOrder,
            'fundSource' => $travelOrder->fund_source,
            'fundDetails' => $travelOrder->fund_details,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("travel_order_{$travelOrder->travel_order_no}.pdf");
    }
}
