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
        // return view('travel_order.create');
        $travelOrder = new TravelOrder(); // empty model instance
        return view('travel_order.create');
    }

    /**
     * Show the form for editing the specified travel order.
     */
    public function edit(TravelOrder $travelOrder)
    {
        return view('travel_order.edit', compact('travelOrder'));
    }

    public function store(Request $request)
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
            'fund_source' => 'required|string|in:General Fund,Project Funds,Others',
            'fund_details' => 'nullable|string|max:255',
        ]);

        /* -------------------------------
        * Normalize expense categories
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

        // Store only expense categories (no fund info duplication)
        $expenses = ['categories' => $categories];

        /* -------------------------------
        * Create the travel order
        * ------------------------------- */
        $travelOrder = TravelOrder::create([
            // 'series' => now()->year,
            'filing_date' => now(),
            'name' => $validated['name'],
            'destination' => $validated['destination'],
            'inclusive_dates' => $validated['inclusive_dates'],
            'purpose' => $validated['purpose'],
            'fund_source' => $validated['fund_source'],
            'fund_details' => $validated['fund_details'] ?? '',
            'expenses' => $expenses,
            'scope' => $request->input('scope', 'within'),
        ]);

        $travelOrder->applySignatories();

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

        $expenses = ['categories' => $categories];

        $travelOrder->update([
            'name' => $validated['name'],
            'destination' => $validated['destination'],
            'inclusive_dates' => $validated['inclusive_dates'],
            'purpose' => $validated['purpose'],
            'fund_source' => $validated['fund_source'],
            'fund_details' => $validated['fund_details'] ?? '',
            'expenses' => $expenses,
            'scope' => $request->input('scope', 'within'),
        ]);

        $travelOrder->applySignatories();

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
