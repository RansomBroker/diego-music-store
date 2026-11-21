<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\POSLogin;

Route::view('/', 'welcome');

Route::get('/backoffice/purchase-orders/{purchaseOrder}/print', [App\Http\Controllers\BackOffice\PurchaseOrderPrintController::class, 'show'])
    ->middleware('auth')
    ->name('backoffice.purchase-orders.print');

Route::get('/backoffice/inventory-mutations/{inventoryMutation}/print', [App\Http\Controllers\BackOffice\InventoryMutationPrintController::class, 'show'])
    ->middleware('auth')
    ->name('backoffice.inventory-mutations.print');

Route::get('/backoffice/sales-invoices/{salesInvoice}/print', [App\Http\Controllers\BackOffice\SalesInvoicePrintController::class, 'show'])
    ->middleware('auth')
    ->name('backoffice.sales-invoices.print');

// Custom POS Routes
Route::get('/pos/login', POSLogin::class)->name('pos.login');
Route::post('/pos/logout', [App\Http\Controllers\POS\PosWebController::class, 'logout'])->name('pos.logout');

Route::middleware('auth.pos')->group(function () {
    // General Operasional POS
    Route::get('/pos/front-office', App\Livewire\FrontOfficeDashboard::class)
        ->middleware('feature.permission:fo.dashboard.view')
        ->name('pos.front-office');
    Route::get('/pos/catalog', App\Livewire\PosCatalog::class)
        ->middleware('feature.permission:fo.catalog.view')
        ->name('pos.catalog');
    Route::get('/pos', App\Livewire\POS::class)
        ->middleware('feature.permission:fo.pos.view')
        ->name('pos');
    Route::get('/pos/receipt/{sale}', [App\Http\Controllers\POS\POSReceiptController::class, 'show'])
        ->middleware('feature.permission:fo.pos.print|fo.transactions.view')
        ->name('pos.receipt');
    Route::get('/pos/receipt-draft', [App\Http\Controllers\POS\POSReceiptController::class, 'showDraft'])
        ->middleware('feature.permission:fo.pos.print')
        ->name('pos.receipt-draft');
    Route::get('/pos/session', App\Livewire\POSCashSession::class)
        ->middleware('feature.permission:fo.cash_session.view')
        ->name('pos.session');
    Route::get('/pos/session/{cashSession}/z-report', [App\Http\Controllers\POS\ZReportController::class, 'show'])
        ->middleware('feature.permission:fo.cash_session.view|fo.cash_session.close')
        ->name('pos.z-report');
    Route::get('/pos/session/{cashSession}/z-report/pdf', [App\Http\Controllers\POS\ZReportController::class, 'downloadPdf'])
        ->middleware('feature.permission:fo.cash_session.view|fo.cash_session.close')
        ->name('pos.z-report.pdf');
    Route::get('/pos/transactions', App\Livewire\POSTransactions::class)
        ->middleware('feature.permission:fo.transactions.view')
        ->name('pos.transactions');
    Route::get('/pos/customer-deposits', App\Livewire\PosCustomerDeposits::class)
        ->middleware('feature.permission:fo.customer_deposits.view')
        ->name('pos.customer-deposits');
    Route::get('/pos/daily-cash', App\Livewire\POSDailyCash::class)
        ->middleware('feature.permission:fo.daily_cash.view')
        ->name('pos.daily-cash');
    Route::get('/pos/customer-payments', App\Livewire\PosCustomerPayments::class)
        ->middleware('feature.permission:fo.customer_payments.view')
        ->name('pos.customer-payments');
    Route::get('/pos/customers', App\Livewire\PosCustomers::class)
        ->middleware('feature.permission:fo.customers.view')
        ->name('pos.customers');
    Route::get('/pos/attendances', App\Livewire\PosAttendances::class)
        ->middleware('feature.permission:fo.attendances.view')
        ->name('pos.attendances');
    Route::get('/pos/day-off-calendar', App\Livewire\EmployeeDayOffCalendar::class)
        ->middleware('feature.permission:fo.day_off.view')
        ->name('pos.day-off-calendar');
    Route::get('/pos/stock-opname-tasks', App\Livewire\StockOpnameTasks::class)
        ->middleware('feature.permission:fo.stock_opname_tasks.view')
        ->name('pos.stock-opname-tasks');
    Route::get('/pos/service-management', App\Livewire\PosServiceManagement::class)
        ->middleware('feature.permission:fo.service.view')
        ->name('pos.service-management');
    Route::get('/pos/barcode-print', App\Livewire\PosBarcodePrint::class)
        ->middleware('feature.permission:fo.barcode_print.view')
        ->name('pos.barcode-print');
    Route::get('/pos/barcode-print/sheet', [App\Http\Controllers\POS\POSBarcodePrintController::class, 'show'])
        ->middleware('feature.permission:fo.barcode_print.view')
        ->name('pos.barcode-print.sheet');

    // Komisi & Performa Sales
    Route::get('/pos/commissions', App\Livewire\PosCommissions::class)
        ->middleware('feature.permission:fo.commissions.view')
        ->name('pos.commissions');
    Route::get('/pos/kpi-performance', App\Livewire\PosKpiPerformance::class)
        ->middleware('feature.permission:fo.kpi.view')
        ->name('pos.kpi-performance');

    // Laporan ERP Routes
    Route::get('/pos/reports', [App\Http\Controllers\POS\PosWebController::class, 'redirectReports'])->name('pos.reports');
    Route::get('/pos/reports/sales', App\Livewire\PosReportsSales::class)
        ->middleware('feature.permission:fo.reports.sales')
        ->name('pos.reports.sales');
    Route::get('/pos/reports/sales-queue', App\Livewire\PosReportsSalesQueue::class)
        ->middleware('feature.permission:fo.reports.sales_queue')
        ->name('pos.reports.sales-queue');
    Route::get('/pos/reports/ar-aging', App\Livewire\PosReportsArAging::class)
        ->middleware('feature.permission:fo.reports.ar_aging')
        ->name('pos.reports.ar-aging');
    Route::get('/pos/reports/ar-settlement', App\Livewire\PosReportsArSettlement::class)
        ->middleware('feature.permission:fo.reports.ar_settlement')
        ->name('pos.reports.ar-settlement');
    Route::get('/pos/reports/daily-cash', App\Livewire\PosReportsDailyCash::class)
        ->middleware('feature.permission:fo.reports.daily_cash')
        ->name('pos.reports.daily-cash');
    Route::get('/pos/reports/stock-prices', App\Livewire\PosReportsStockPrices::class)
        ->middleware('feature.permission:fo.reports.stock_prices')
        ->name('pos.reports.stock-prices');

    // Branch Switcher Route
    Route::get('/pos/switch-branch/{branch}', [App\Http\Controllers\POS\PosWebController::class, 'switchBranch'])->name('pos.switch-branch');

    // Individual payslip routes enforce employee ownership in their controllers.
    Route::get('/pos/payroll/payslip-pdf/{id}', [App\Http\Controllers\POS\PayrollPayslipController::class, 'show'])->name('pos.payroll.payslip-pdf');
    Route::get('/pos/payroll/item/export-excel/{id}', [App\Http\Controllers\POS\PayrollExportController::class, 'exportItem'])->name('pos.payroll.item.export-excel');

    // SDM & Manajemen Karyawan
    Route::get('/pos/employees', App\Livewire\PosEmployees::class)
        ->middleware('feature.permission:fo.employees.view')
        ->name('pos.employees');
    Route::get('/pos/employee-tasks', App\Livewire\PosEmployeeTasks::class)
        ->middleware('feature.permission:fo.employee_tasks.view')
        ->name('pos.employee-tasks');
    Route::get('/pos/stock-opname-review', App\Livewire\StockOpnameReview::class)
        ->middleware('feature.permission:fo.stock_opname_review.view')
        ->name('pos.stock-opname-review');
    Route::get('/pos/attendance-radiuses', App\Livewire\PosAttendanceRadiuses::class)
        ->middleware('feature.permission:fo.attendance_radiuses.view')
        ->name('pos.attendance-radiuses');
    Route::get('/pos/attendance-violations', App\Livewire\PosAttendanceViolations::class)
        ->middleware('feature.permission:fo.attendance_violations.view')
        ->name('pos.attendance-violations');

    // Master Data Pengaturan
    Route::get('/pos/users', App\Livewire\PosUsers::class)
        ->middleware('feature.permission:fo.users.view')
        ->name('pos.users');
    Route::get('/pos/units', App\Livewire\PosUnits::class)
        ->middleware('feature.permission:fo.units.view')
        ->name('pos.units');
    Route::get('/pos/customer-labels', App\Livewire\PosCustomerLabels::class)
        ->middleware('feature.permission:fo.customer_labels.view')
        ->name('pos.customer-labels');
    Route::get('/pos/sale-categories', App\Livewire\PosSaleCategories::class)
        ->middleware('feature.permission:fo.sale_categories.view')
        ->name('pos.sale-categories');
    Route::get('/pos/payment-methods', App\Livewire\PosPaymentMethods::class)
        ->middleware('feature.permission:fo.payment_methods.view')
        ->name('pos.payment-methods');
    Route::get('/pos/vouchers', App\Livewire\PosVouchers::class)
        ->middleware('feature.permission:fo.vouchers.view')
        ->name('pos.vouchers');

    // Utility & Pengaturan Toko
    Route::get('/pos/privileges', App\Livewire\PosPrivileges::class)
        ->middleware('feature.permission:fo.privileges.view')
        ->name('pos.privileges');
    Route::get('/pos/store-profile', App\Livewire\PosStoreProfile::class)
        ->middleware('feature.permission:fo.store_profile.view')
        ->name('pos.store-profile');
    Route::get('/pos/branches', App\Livewire\PosBranches::class)
        ->middleware('feature.permission:fo.branches.view')
        ->name('pos.branches');
    Route::get('/pos/whatsapp-settings', App\Livewire\PosWhatsAppSettings::class)
        ->middleware('feature.permission:fo.whatsapp_settings.view')
        ->name('pos.whatsapp-settings');
    Route::get('/pos/branch-performance', App\Livewire\PosBranchPerformance::class)
        ->middleware('feature.permission:fo.branch_performance.view')
        ->name('pos.branch-performance');
    Route::get('/pos/receipt-settings', App\Livewire\PosReceiptSettings::class)
        ->middleware('feature.permission:fo.receipt_settings.view')
        ->name('pos.receipt-settings');

    // Payroll & Gaji Karyawan
    Route::get('/pos/payroll', App\Livewire\PosPayrollManagement::class)
        ->middleware('feature.permission:fo.payroll.view')
        ->name('pos.payroll');
    Route::get('/pos/payroll/bulk-payslip-pdf/{id}', [App\Http\Controllers\POS\BulkPayrollPayslipController::class, 'show'])
        ->middleware('feature.permission:fo.payroll.view')
        ->name('pos.payroll.bulk-payslip-pdf');
    Route::get('/pos/payroll/export-excel/{id}', [App\Http\Controllers\POS\PayrollExportController::class, 'export'])
        ->middleware('feature.permission:fo.payroll.view')
        ->name('pos.payroll.export-excel');
});

// Public Service Tracking Route (Without Login)
Route::get('/track-service/{ticketCode}', [App\Http\Controllers\PublicServiceTrackingController::class, 'show'])->name('public.service-tracking');

// Fallback redirect for /admin URL path to /backoffice
Route::get('/admin/{path?}', [App\Http\Controllers\POS\PosWebController::class, 'redirectAdmin'])->where('path', '.*');

