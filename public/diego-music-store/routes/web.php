<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\POSLogin;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/backoffice/purchase-orders/{purchaseOrder}/print', [App\Http\Controllers\BackOffice\PurchaseOrderPrintController::class, 'show'])
    ->name('backoffice.purchase-orders.print');

// Custom POS Routes
Route::get('/pos/login', POSLogin::class)->name('pos.login');

Route::middleware('auth.pos')->group(function () {
    Route::get('/pos', App\Livewire\POS::class)->name('pos');
    Route::get('/pos/receipt/{sale}', [App\Http\Controllers\POS\POSReceiptController::class, 'show'])->name('pos.receipt');
    Route::get('/pos/session', App\Livewire\POSCashSession::class)->name('pos.session');
    Route::get('/pos/session/{cashSession}/z-report', [App\Http\Controllers\POS\ZReportController::class, 'show'])->name('pos.z-report');
});
