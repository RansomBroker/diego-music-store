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
    Route::get('/pos/front-office', App\Livewire\FrontOfficeDashboard::class)->name('pos.front-office');
    Route::get('/pos', App\Livewire\POS::class)->name('pos');
    Route::get('/pos/receipt/{sale}', [App\Http\Controllers\POS\POSReceiptController::class, 'show'])->name('pos.receipt');
    Route::get('/pos/receipt-draft', [App\Http\Controllers\POS\POSReceiptController::class, 'showDraft'])->name('pos.receipt-draft');
    Route::get('/pos/session', App\Livewire\POSCashSession::class)->name('pos.session');
    Route::get('/pos/session/{cashSession}/z-report', [App\Http\Controllers\POS\ZReportController::class, 'show'])->name('pos.z-report');
    Route::get('/pos/transactions', App\Livewire\POSTransactions::class)->name('pos.transactions');
    Route::get('/pos/daily-cash', App\Livewire\POSDailyCash::class)->name('pos.daily-cash');
    Route::get('/pos/supplier-payments', App\Livewire\PosSupplierPayments::class)->name('pos.supplier-payments');

    // Input Data
    Route::get('/pos/customers', App\Livewire\PosCustomers::class)->name('pos.customers');
    Route::get('/pos/users', App\Livewire\PosUsers::class)->name('pos.users');
    Route::get('/pos/units', App\Livewire\PosUnits::class)->name('pos.units');
    Route::get('/pos/customer-labels', App\Livewire\PosCustomerLabels::class)->name('pos.customer-labels');
    Route::get('/pos/sale-categories', App\Livewire\PosSaleCategories::class)->name('pos.sale-categories');
    Route::get('/pos/payment-methods', App\Livewire\PosPaymentMethods::class)->name('pos.payment-methods');
});
