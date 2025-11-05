<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelOrderController;
use App\Models\TravelOrder;
use Barryvdh\DomPDF\Facade\Pdf;

// Root redirect
Route::get('/', function () {
    return redirect()->route('travel_order.index');
});

// 🔹 Static Preview (for layout testing)
Route::get('/travel_order/test', [TravelOrderController::class, 'preview'])
    ->name('travel_order.test');

// 🔹 Dynamic Record PDF Preview (from DB)

Route::get('/travel_order/{id}/preview', function ($id) {
    $travelOrder = TravelOrder::findOrFail($id);
    $pdf = Pdf::loadView('travel_order.template', compact('travelOrder'))
        ->setPaper('A4', 'portrait');
    return $pdf->stream("travel_order_{$travelOrder->travel_order_no}.pdf");
})->name('travel_order.preview');

// RESTful CRUD routes
Route::resource('travel_order', TravelOrderController::class)->except(['show']);

