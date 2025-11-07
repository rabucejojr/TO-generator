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

    /**
     * Show the form for editing the specified travel order.
     */
    public function store(Request $request, TravelOrder $travelOrder)
{

    // dd($travelOrder->toArray());

    $validated = $request->validate([
        'filing_date' => 'required|date',
        'name' => 'required|array|min:1',
        'name.*.name' => 'required|string|max:255',
        'name.*.position' => 'nullable|string|max:255',
        'name.*.agency' => 'nullable|string|max:255',
        'destination' => 'required|string|max:255',
        'inclusive_dates' => 'required|string|max:255',
        'purpose' => 'required|string',
    ]);

    // 🔹 Use helper to build the expenses JSON
    $expenses = $this->buildExpenses($request);

    // -----------------------------------------------------------
    // AUTO-GENERATE TRAVEL ORDER NUMBER
    // -----------------------------------------------------------
    $series = now()->year;

    $lastOrder = TravelOrder::where('series', $series)
        ->orderByDesc('id')
        ->first();

    $startValue = 114;

    if ($lastOrder && preg_match("/SDN-{$series}-(\d+)/", $lastOrder->travel_order_no, $matches)) {
        $nextNumber = (int) $matches[1] + 1;
    } else {
        $nextNumber = $startValue;
    }

    $travelOrderNo = sprintf('SDN-%s-%04d', $series, $nextNumber);

    // -----------------------------------------------------------
    // SIGNATORY LOGIC
    // -----------------------------------------------------------
    $scope = $request->input('scope', 'within');

    if ($scope === 'outside') {
        $approvedBy = 'ENGR. NOEL M. AJOC';
        $approvedPosition = 'Regional Director';
        $recommendingApproval = [
            'name' => 'MR. RICARDO N. VARELA',
            'position' => 'OIC, PSTO-SDN',
        ];
    } else {
        $approvedBy = 'MR. RICARDO N. VARELA';
        $approvedPosition = 'OIC, PSTO-SDN';
        $recommendingApproval = null;
    }

    // -----------------------------------------------------------
    // CREATE TRAVEL ORDER
    // -----------------------------------------------------------
    $travelOrder = TravelOrder::create([
        'travel_order_no' => $travelOrderNo,
        'series' => $series,
        'filing_date' => $request->input('filing_date', now()),
        'name' => $validated['name'],
        'destination' => $validated['destination'],
        'inclusive_dates' => $validated['inclusive_dates'],
        'purpose' => $validated['purpose'],
        'remarks' => $request->input('remarks'),
        'fund_source' => $request->input('fund_source'),
        'fund_details' => $request->input('fund_details'),
        'expenses' => $expenses,
        'scope' => $scope,
        'approved_by' => $approvedBy,
        'approved_position' => $approvedPosition,
        'regional_director' => $recommendingApproval['name'] ?? null,
        'regional_position' => $recommendingApproval['position'] ?? null,
    ]);

    return redirect()
        ->route('travel_order.edit', $travelOrder)
        ->with('success', 'Travel Order created successfully.');
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
            'name.*.agency' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'inclusive_dates' => 'required|string|max:255',
            'purpose' => 'required|string',
        ]);

        // 🔹 Use helper to build the expenses JSON
        $expenses = $this->buildExpenses($request);

        // -----------------------------------------------------------
        // SIGNATORY LOGIC (same as in store)
        // -----------------------------------------------------------
        $scope = $request->input('scope', 'within');

        if ($scope === 'outside') {
            $approvedBy = 'ENGR. NOEL M. AJOC';
            $approvedPosition = 'Regional Director';
            $recommendingApproval = [
                'name' => 'MR. RICARDO N. VARELA',
                'position' => 'OIC, PSTO-SDN',
            ];
        } else {
            $approvedBy = 'MR. RICARDO N. VARELA';
            $approvedPosition = 'OIC, PSTO-SDN';
            $recommendingApproval = null;
        }

        // -----------------------------------------------------------
        // UPDATE TRAVEL ORDER
        // -----------------------------------------------------------
        $travelOrder->update([
            'name' => $validated['name'],
            'destination' => $validated['destination'],
            'inclusive_dates' => $validated['inclusive_dates'],
            'purpose' => $validated['purpose'],
            'fund_source' => $request->fund_source,
            'fund_details' => $request->fund_details,
            'remarks' => $request->input('remarks'),
            'expenses' => $expenses,
            'scope' => $scope,
            'approved_by' => $approvedBy,
            'approved_position' => $approvedPosition,
            'regional_director' => $recommendingApproval['name'] ?? null,
            'regional_position' => $recommendingApproval['position'] ?? null,
        ]);


        // dd($travelOrder->toArray());
        // dd($request->fund_source, $request->fund_details);

        return redirect()
            ->route('travel_order.index', $travelOrder)
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
            $travelOrder->expenses['fund_source']['project_funds_details'] ??
            $travelOrder->expenses['fund_source']['others'] ??
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

    /**
     * Build the expenses JSON structure from form input.
     */
    private function buildExpenses(Request $request): array
    {
        $input = $request->input('expenses.categories', []);

        return [
            'categories' => [
                'actual' => [
                    'accommodation' => !empty($input['actual']['accommodation']),
                    'meals_food' => !empty($input['actual']['meals_food']),
                    'incidental_expenses' => !empty($input['actual']['incidental_expenses']),
                ],
                'per_diem' => [
                    'accommodation' => !empty($input['per_diem']['accommodation']),
                    'subsistence' => !empty($input['per_diem']['subsistence']),
                    'incidental_expenses' => !empty($input['per_diem']['incidental_expenses']),
                ],
                'transportation' => [
                    'official_vehicle' => !empty($input['transportation']['official_vehicle']),
                    'public_conveyance' => !empty($input['transportation']['public_conveyance']),
                    'public_conveyance_text' => $input['transportation']['public_conveyance_text'] ?? null,
                ],
                'others' => $input['others'] ?? '',
            ],
        ];
    }


}
