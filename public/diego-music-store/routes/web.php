<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/backoffice/purchase-orders/{purchaseOrder}/print', [App\Http\Controllers\BackOffice\PurchaseOrderPrintController::class, 'show'])
    ->name('backoffice.purchase-orders.print');
