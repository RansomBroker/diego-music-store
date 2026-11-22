<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Definisi Lengkap Modul, Fitur & Permissions Diego Music Store ERP
    |--------------------------------------------------------------------------
    |
    | Satu sumber kebenaran RBAC untuk Front Office (FO) dan Backoffice (BO).
    | Struktur: sections -> [frontoffice | backoffice] -> groups -> features -> permissions.
    | Aturan penamaan: tidak ada key yang menjadi dot-prefix dari key lain.
    |
    */

    'sections' => [
        'frontoffice' => [
            'label' => 'Front Office (FO / POS)',
            'description' => 'Mengatur izin masuk Front Office (/pos), seluruh menu sidebar FO, tombol CRUD, fitur kasir, dan data sensitif.',
            'access_permission' => 'fo.access',
            'access_label' => 'Dapat Akses Front Office (FO)',
            'groups' => [
                'fo_main' => [
                    'label' => 'Menu Utama',
                    'features' => [
                        'fo_dashboard' => [
                            'label' => 'Dashboard Front Office',
                            'permissions' => [
                                'fo.dashboard.view' => 'Tampil & Lihat',
                                'fo.dashboard.owner_widgets' => 'Widget Eksekutif & Presensi Cabang',
                            ],
                        ],
                        'fo_catalog' => [
                            'label' => 'Katalog Produk (Read-Only)',
                            'permissions' => [
                                'fo.catalog.view' => 'Tampil & Lihat',
                            ],
                        ],
                        'fo_pos' => [
                            'label' => 'POS Kasir',
                            'permissions' => [
                                'fo.pos.view' => 'Tampil & Akses Layar Kasir',
                                'fo.pos.checkout' => 'Proses Bayar / Checkout',
                                'fo.pos.discount' => 'Beri Diskon Manual',
                                'fo.pos.hold' => 'Transaksi Tunda (Hold/Draft)',
                                'fo.pos.edit_sale' => 'Edit Transaksi POS',
                                'fo.pos.print' => 'Cetak Struk / Bill / Draft',
                            ],
                        ],
                        'fo_cash_session' => [
                            'label' => 'Sesi Kasir',
                            'permissions' => [
                                'fo.cash_session.view' => 'Tampil & Lihat',
                                'fo.cash_session.open' => 'Buka Sesi Kasir',
                                'fo.cash_session.close' => 'Tutup Sesi & Z-Report',
                                'fo.cash_session.reopen' => 'Buka Ulang (Reopen) Sesi',
                            ],
                        ],
                        'fo_transactions' => [
                            'label' => 'Daftar Transaksi',
                            'permissions' => [
                                'fo.transactions.view' => 'Tampil & Lihat',
                                'fo.transactions.return' => 'Retur / Void Transaksi',
                                'fo.transactions.settle' => 'Pelunasan Piutang Nota',
                                'fo.transactions.delete' => 'Hapus Transaksi',
                            ],
                        ],
                        'fo_customer_deposits' => [
                            'label' => 'Deposit Pelanggan',
                            'permissions' => [
                                'fo.customer_deposits.view' => 'Tampil & Lihat',
                                'fo.customer_deposits.create' => 'Tambah',
                                'fo.customer_deposits.edit' => 'Ubah / Settle',
                                'fo.customer_deposits.delete' => 'Batalkan',
                            ],
                        ],
                    ],
                ],
                'fo_operational' => [
                    'label' => 'Operasional & Keuangan',
                    'features' => [
                        'fo_daily_cash' => [
                            'label' => 'Kas Harian',
                            'permissions' => [
                                'fo.daily_cash.view' => 'Tampil & Lihat',
                                'fo.daily_cash.create' => 'Input Kas Masuk / Keluar',
                            ],
                        ],
                        'fo_customer_payments' => [
                            'label' => 'Pelunasan Piutang',
                            'permissions' => [
                                'fo.customer_payments.view' => 'Tampil & Lihat',
                                'fo.customer_payments.create' => 'Input Pelunasan',
                            ],
                        ],
                        'fo_service' => [
                            'label' => 'Service',
                            'permissions' => [
                                'fo.service.view' => 'Tampil & Lihat',
                                'fo.service.edit' => 'Update Status & Biaya Service',
                            ],
                        ],
                    ],
                ],
                'fo_hr' => [
                    'label' => 'SDM & Kehadiran',
                    'features' => [
                        'fo_employees' => [
                            'label' => 'Karyawan',
                            'permissions' => [
                                'fo.employees.view' => 'Tampil & Lihat',
                                'fo.employees.create' => 'Tambah',
                                'fo.employees.edit' => 'Ubah',
                                'fo.employees.delete' => 'Hapus',
                            ],
                        ],
                        'fo_employee_tasks' => [
                            'label' => 'Tugas Karyawan',
                            'permissions' => [
                                'fo.employee_tasks.view' => 'Tampil & Lihat',
                                'fo.employee_tasks.create' => 'Buat Tugas',
                                'fo.employee_tasks.edit' => 'Ubah / Approve / Tolak',
                                'fo.employee_tasks.delete' => 'Hapus',
                            ],
                        ],
                        'fo_day_off' => [
                            'label' => 'Kalender Jadwal Off',
                            'permissions' => [
                                'fo.day_off.view' => 'Tampil & Ajukan Off Sendiri',
                                'fo.day_off.manage' => 'Kelola & Batalkan Off Semua Staf',
                            ],
                        ],
                        'fo_stock_opname_tasks' => [
                            'label' => 'Tugas Stok Opname',
                            'permissions' => [
                                'fo.stock_opname_tasks.view' => 'Tampil & Kerjakan Hitung Opname',
                            ],
                        ],
                        'fo_stock_opname_review' => [
                            'label' => 'Review Stok Opname',
                            'permissions' => [
                                'fo.stock_opname_review.view' => 'Tampil & Lihat',
                                'fo.stock_opname_review.approve' => 'Approve / Revisi / Finalisasi',
                            ],
                        ],
                        'fo_attendances' => [
                            'label' => 'Presensi',
                            'permissions' => [
                                'fo.attendances.view' => 'Tampil & Clock In/Out Sendiri',
                                'fo.attendances.manage' => 'Input Manual & Approve Susulan',
                            ],
                        ],
                        'fo_attendance_radiuses' => [
                            'label' => 'Radius Presensi',
                            'permissions' => [
                                'fo.attendance_radiuses.view' => 'Tampil & Lihat',
                                'fo.attendance_radiuses.edit' => 'Ubah Radius',
                            ],
                        ],
                        'fo_attendance_violations' => [
                            'label' => 'Denda & Pelanggaran Presensi',
                            'permissions' => [
                                'fo.attendance_violations.view' => 'Tampil & Lihat',
                                'fo.attendance_violations.manage' => 'Kelola Aturan & Approve/Waive',
                            ],
                        ],
                        'fo_commissions' => [
                            'label' => 'Komisi Sales',
                            'permissions' => [
                                'fo.commissions.view' => 'Tampil & Lihat',
                                'fo.commissions.manage' => 'Kelola Skema, Grup & Approve',
                            ],
                        ],
                        'fo_kpi' => [
                            'label' => 'KPI & Bonus',
                            'permissions' => [
                                'fo.kpi.view' => 'Tampil & Lihat',
                                'fo.kpi.manage' => 'Kelola Template & Hitung Ulang',
                            ],
                        ],
                        'fo_payroll' => [
                            'label' => 'Payroll Gaji',
                            'permissions' => [
                                'fo.payroll.view' => 'Tampil & Lihat',
                                'fo.payroll.create' => 'Generate Payroll',
                                'fo.payroll.edit' => 'Override, Approve & Bayar',
                                'fo.payroll.delete' => 'Batalkan Payroll',
                            ],
                        ],
                    ],
                ],
                'fo_reports' => [
                    'label' => 'Laporan ERP',
                    'features' => [
                        'fo_reports_sales' => [
                            'label' => 'Laporan Penjualan',
                            'permissions' => ['fo.reports.sales' => 'Tampil & Lihat'],
                        ],
                        'fo_reports_sales_queue' => [
                            'label' => 'Antrian & Giliran Sales',
                            'permissions' => ['fo.reports.sales_queue' => 'Tampil & Lihat'],
                        ],
                        'fo_reports_ar_aging' => [
                            'label' => 'Laporan Piutang (AR Aging)',
                            'permissions' => ['fo.reports.ar_aging' => 'Tampil & Lihat'],
                        ],
                        'fo_reports_ar_settlement' => [
                            'label' => 'Laporan Pelunasan Piutang',
                            'permissions' => ['fo.reports.ar_settlement' => 'Tampil & Lihat'],
                        ],
                        'fo_reports_daily_cash' => [
                            'label' => 'Laporan Kas Harian',
                            'permissions' => ['fo.reports.daily_cash' => 'Tampil & Lihat'],
                        ],
                        'fo_reports_stock_prices' => [
                            'label' => 'Daftar Stok & Harga',
                            'permissions' => ['fo.reports.stock_prices' => 'Tampil & Lihat'],
                        ],
                    ],
                ],
                'fo_master' => [
                    'label' => 'Input Master Data',
                    'features' => [
                        'fo_customers' => [
                            'label' => 'Data Pelanggan',
                            'permissions' => [
                                'fo.customers.view' => 'Tampil & Lihat',
                                'fo.customers.create' => 'Tambah',
                                'fo.customers.edit' => 'Ubah',
                                'fo.customers.delete' => 'Hapus',
                                'fo.customers.broadcast' => 'Broadcast / Kirim WA',
                            ],
                        ],
                        'fo_customer_labels' => [
                            'label' => 'Label Pelanggan',
                            'permissions' => [
                                'fo.customer_labels.view' => 'Tampil & Lihat',
                                'fo.customer_labels.create' => 'Tambah',
                                'fo.customer_labels.edit' => 'Ubah',
                                'fo.customer_labels.delete' => 'Hapus',
                            ],
                        ],
                        'fo_users' => [
                            'label' => 'Data User Login',
                            'permissions' => [
                                'fo.users.view' => 'Tampil & Lihat',
                                'fo.users.create' => 'Tambah',
                                'fo.users.edit' => 'Ubah',
                                'fo.users.delete' => 'Hapus',
                            ],
                        ],
                        'fo_units' => [
                            'label' => 'Satuan Barang',
                            'permissions' => [
                                'fo.units.view' => 'Tampil & Lihat',
                                'fo.units.create' => 'Tambah',
                                'fo.units.edit' => 'Ubah',
                                'fo.units.delete' => 'Hapus',
                            ],
                        ],
                        'fo_sale_categories' => [
                            'label' => 'Kategori Penjualan',
                            'permissions' => [
                                'fo.sale_categories.view' => 'Tampil & Lihat',
                                'fo.sale_categories.create' => 'Tambah',
                                'fo.sale_categories.edit' => 'Ubah',
                                'fo.sale_categories.delete' => 'Hapus',
                            ],
                        ],
                        'fo_payment_methods' => [
                            'label' => 'Metode Pembayaran',
                            'permissions' => [
                                'fo.payment_methods.view' => 'Tampil & Lihat',
                                'fo.payment_methods.create' => 'Tambah',
                                'fo.payment_methods.edit' => 'Ubah',
                                'fo.payment_methods.delete' => 'Hapus',
                            ],
                        ],
                        'fo_vouchers' => [
                            'label' => 'Data Voucher Promo',
                            'permissions' => [
                                'fo.vouchers.view' => 'Tampil & Lihat',
                                'fo.vouchers.create' => 'Tambah',
                                'fo.vouchers.edit' => 'Ubah',
                                'fo.vouchers.delete' => 'Hapus',
                            ],
                        ],
                    ],
                ],
                'fo_utility' => [
                    'label' => 'Pengaturan & Utility',
                    'features' => [
                        'fo_privileges' => [
                            'label' => 'Privilege Role User',
                            'permissions' => [
                                'fo.privileges.view' => 'Tampil & Lihat',
                                'fo.privileges.manage' => 'Tambah / Ubah / Hapus Role',
                            ],
                        ],
                        'fo_store_profile' => [
                            'label' => 'Profil & Nama Toko',
                            'permissions' => [
                                'fo.store_profile.view' => 'Tampil & Lihat',
                                'fo.store_profile.edit' => 'Ubah Profil & Tambah Toko',
                            ],
                        ],
                        'fo_branches' => [
                            'label' => 'Manajemen Cabang',
                            'permissions' => [
                                'fo.branches.view' => 'Tampil & Lihat',
                                'fo.branches.create' => 'Tambah Cabang',
                                'fo.branches.edit' => 'Ubah Cabang',
                            ],
                        ],
                        'fo_whatsapp_settings' => [
                            'label' => 'Setting WA Fonnte',
                            'permissions' => [
                                'fo.whatsapp_settings.view' => 'Tampil & Lihat',
                                'fo.whatsapp_settings.edit' => 'Ubah & Tes Kirim WA',
                            ],
                        ],
                        'fo_branch_performance' => [
                            'label' => 'Performa Cabang Toko',
                            'permissions' => [
                                'fo.branch_performance.view' => 'Tampil & Lihat',
                            ],
                        ],
                        'fo_receipt_settings' => [
                            'label' => 'Setting Struk & Printer Hardware',
                            'permissions' => [
                                'fo.receipt_settings.view' => 'Tampil & Lihat',
                                'fo.receipt_settings.edit' => 'Ubah Setting Struk',
                            ],
                        ],
                        'fo_barcode_print' => [
                            'label' => 'Cetak Barcode Item',
                            'permissions' => [
                                'fo.barcode_print.view' => 'Tampil & Cetak Barcode',
                            ],
                        ],
                    ],
                ],
                'fo_sensitive' => [
                    'label' => 'Akses Sensitif & Lintas Cabang (Berlaku di FO & BO)',
                    'features' => [
                        'shared_branch_switch' => [
                            'label' => 'Pindah Cabang',
                            'permissions' => [
                                'branch.switch' => 'Pindah & Akses Semua Cabang Aktif',
                            ],
                        ],
                        'shared_sensitive_fields' => [
                            'label' => 'Data Sensitif (Field-Level)',
                            'permissions' => [
                                'sales.view_profit' => 'Lihat HPP Penjualan, Laba Kotor & Margin',
                                'inventory.view_cost' => 'Lihat Harga Beli, HPP & Valuasi Stok',
                                'hr.view_salary' => 'Lihat Gaji Pokok & Rekening Karyawan',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'backoffice' => [
            'label' => 'Backoffice (BO / Admin ERP)',
            'description' => 'Mengatur izin masuk Panel Backoffice (/backoffice), seluruh menu sidebar BO, aksi CRUD per resource, laporan, dan pengaturan sistem.',
            'access_permission' => 'bo.access',
            'access_label' => 'Dapat Akses Backoffice (BO)',
            'groups' => [
                'bo_dashboard' => [
                    'label' => 'Dashboard & Widget',
                    'features' => [
                        'bo_dashboard' => [
                            'label' => 'Dashboard Backoffice',
                            'permissions' => [
                                'bo.dashboard.view' => 'Tampil & Lihat Dashboard',
                                'bo.widgets.focus_products' => 'Widget Rekomendasi Produk Fokus',
                                'bo.widgets.financial_chart' => 'Widget Grafik Komparasi Keuangan (COA)',
                            ],
                        ],
                    ],
                ],
                'bo_master' => [
                    'label' => 'Master Data',
                    'features' => [
                        'bo_branches' => [
                            'label' => 'Branches (Cabang)',
                            'permissions' => [
                                'bo.branches.view' => 'Tampil & Lihat',
                                'bo.branches.create' => 'Tambah',
                                'bo.branches.edit' => 'Ubah',
                                'bo.branches.delete' => 'Hapus',
                            ],
                        ],
                        'bo_customer_labels' => [
                            'label' => 'Label Pelanggan',
                            'permissions' => [
                                'bo.customer_labels.view' => 'Tampil & Lihat',
                                'bo.customer_labels.create' => 'Tambah',
                                'bo.customer_labels.edit' => 'Ubah',
                                'bo.customer_labels.delete' => 'Hapus',
                            ],
                        ],
                        'bo_customers' => [
                            'label' => 'Customers (Pelanggan)',
                            'permissions' => [
                                'bo.customers.view' => 'Tampil & Lihat',
                                'bo.customers.create' => 'Tambah',
                                'bo.customers.edit' => 'Ubah',
                                'bo.customers.delete' => 'Hapus',
                            ],
                        ],
                        'bo_pricing_tiers' => [
                            'label' => 'Pricing Tiers (Tingkat Harga)',
                            'permissions' => [
                                'bo.pricing_tiers.view' => 'Tampil & Lihat',
                                'bo.pricing_tiers.create' => 'Tambah',
                                'bo.pricing_tiers.edit' => 'Ubah',
                                'bo.pricing_tiers.delete' => 'Hapus',
                            ],
                        ],
                        'bo_products' => [
                            'label' => 'Produk',
                            'permissions' => [
                                'bo.products.view' => 'Tampil & Lihat',
                                'bo.products.create' => 'Tambah',
                                'bo.products.edit' => 'Ubah',
                                'bo.products.delete' => 'Hapus',
                            ],
                        ],
                        'bo_suppliers' => [
                            'label' => 'Suppliers',
                            'permissions' => [
                                'bo.suppliers.view' => 'Tampil & Lihat',
                                'bo.suppliers.create' => 'Tambah',
                                'bo.suppliers.edit' => 'Ubah',
                                'bo.suppliers.delete' => 'Hapus',
                            ],
                        ],
                        'bo_units' => [
                            'label' => 'Units (Satuan)',
                            'permissions' => [
                                'bo.units.view' => 'Tampil & Lihat',
                                'bo.units.create' => 'Tambah',
                                'bo.units.edit' => 'Ubah',
                                'bo.units.delete' => 'Hapus',
                            ],
                        ],
                    ],
                ],
                'bo_hr' => [
                    'label' => 'Manajemen Karyawan',
                    'features' => [
                        'bo_employees' => [
                            'label' => 'Karyawan',
                            'permissions' => [
                                'bo.employees.view' => 'Tampil & Lihat',
                                'bo.employees.create' => 'Tambah',
                                'bo.employees.edit' => 'Ubah',
                                'bo.employees.delete' => 'Hapus',
                            ],
                        ],
                        'bo_attendance_radiuses' => [
                            'label' => 'Radius Presensi',
                            'permissions' => [
                                'bo.attendance_radiuses.view' => 'Tampil & Lihat',
                                'bo.attendance_radiuses.edit' => 'Ubah',
                            ],
                        ],
                        'bo_commission_schemes' => [
                            'label' => 'Skema & Aturan Komisi',
                            'permissions' => [
                                'bo.commission_schemes.view' => 'Tampil & Lihat',
                                'bo.commission_schemes.create' => 'Tambah',
                                'bo.commission_schemes.edit' => 'Ubah',
                                'bo.commission_schemes.delete' => 'Hapus',
                            ],
                        ],
                        'bo_sales_commissions' => [
                            'label' => 'Rekap & Log Komisi Sales',
                            'permissions' => [
                                'bo.sales_commissions.view' => 'Tampil & Lihat',
                            ],
                        ],
                        'bo_attendance_violation_rules' => [
                            'label' => 'Aturan Denda Presensi',
                            'permissions' => [
                                'bo.attendance_violation_rules.view' => 'Tampil & Lihat',
                                'bo.attendance_violation_rules.create' => 'Tambah',
                                'bo.attendance_violation_rules.edit' => 'Ubah',
                                'bo.attendance_violation_rules.delete' => 'Hapus',
                            ],
                        ],
                        'bo_attendance_violation_logs' => [
                            'label' => 'Log Denda Presensi',
                            'permissions' => [
                                'bo.attendance_violation_logs.view' => 'Tampil & Lihat',
                                'bo.attendance_violation_logs.create' => 'Tambah',
                                'bo.attendance_violation_logs.delete' => 'Hapus',
                            ],
                        ],
                        'bo_kpi_templates' => [
                            'label' => 'Template KPI Karyawan',
                            'permissions' => [
                                'bo.kpi_templates.view' => 'Tampil & Lihat',
                                'bo.kpi_templates.create' => 'Tambah',
                                'bo.kpi_templates.edit' => 'Ubah',
                                'bo.kpi_templates.delete' => 'Hapus',
                            ],
                        ],
                        'bo_kpi_evaluations' => [
                            'label' => 'Evaluasi & Bonus KPI',
                            'permissions' => [
                                'bo.kpi_evaluations.view' => 'Tampil & Lihat',
                            ],
                        ],
                        'bo_payrolls' => [
                            'label' => 'Payroll & Gaji Karyawan',
                            'permissions' => [
                                'bo.payrolls.view' => 'Tampil & Lihat',
                                'bo.payrolls.edit' => 'Override Komponen Gaji',
                            ],
                        ],
                        'bo_employee_cash_advances' => [
                            'label' => 'Kasbon / Cash Advance',
                            'permissions' => [
                                'bo.employee_cash_advances.view' => 'Tampil & Lihat',
                                'bo.employee_cash_advances.create' => 'Ajukan Kasbon',
                                'bo.employee_cash_advances.approve' => 'Approve / Tolak / Pelunasan / Batal',
                            ],
                        ],
                    ],
                ],
                'bo_purchasing' => [
                    'label' => 'Pembelian',
                    'features' => [
                        'bo_purchase_orders' => [
                            'label' => 'Purchase Orders (PO)',
                            'permissions' => [
                                'bo.purchase_orders.view' => 'Tampil & Lihat',
                                'bo.purchase_orders.create' => 'Tambah',
                                'bo.purchase_orders.edit' => 'Ubah',
                                'bo.purchase_orders.delete' => 'Hapus',
                            ],
                        ],
                        'bo_purchase_transactions' => [
                            'label' => 'Transaksi Pembelian',
                            'permissions' => [
                                'bo.purchase_transactions.view' => 'Tampil & Lihat',
                                'bo.purchase_transactions.create' => 'Tambah',
                                'bo.purchase_transactions.edit' => 'Ubah',
                                'bo.purchase_transactions.delete' => 'Hapus',
                            ],
                        ],
                        'bo_purchase_returns' => [
                            'label' => 'Retur Pembelian (Supplier)',
                            'permissions' => [
                                'bo.purchase_returns.view' => 'Tampil & Lihat',
                                'bo.purchase_returns.edit' => 'Ubah',
                                'bo.purchase_returns.delete' => 'Hapus',
                            ],
                        ],
                        'bo_supplier_payments' => [
                            'label' => 'Pelunasan Hutang',
                            'permissions' => [
                                'bo.supplier_payments.view' => 'Tampil & Lihat',
                                'bo.supplier_payments.create' => 'Tambah',
                                'bo.supplier_payments.edit' => 'Ubah',
                                'bo.supplier_payments.delete' => 'Hapus',
                            ],
                        ],
                        'bo_reports_purchase' => [
                            'label' => 'Laporan Pembelian',
                            'permissions' => ['bo.reports.purchase' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_purchase_return' => [
                            'label' => 'Laporan Retur Pembelian',
                            'permissions' => ['bo.reports.purchase_return' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_accounts_payable' => [
                            'label' => 'Laporan Hutang',
                            'permissions' => ['bo.reports.accounts_payable' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_supplier_payment' => [
                            'label' => 'Laporan Pelunasan Hutang',
                            'permissions' => ['bo.reports.supplier_payment' => 'Tampil & Lihat'],
                        ],
                    ],
                ],
                'bo_sales' => [
                    'label' => 'Penjualan',
                    'features' => [
                        'bo_sales_quotations' => [
                            'label' => 'Penawaran Harga (SQ)',
                            'permissions' => [
                                'bo.sales_quotations.view' => 'Tampil & Lihat',
                                'bo.sales_quotations.create' => 'Tambah',
                                'bo.sales_quotations.edit' => 'Ubah',
                                'bo.sales_quotations.delete' => 'Hapus',
                            ],
                        ],
                        'bo_sales_invoices' => [
                            'label' => 'Faktur Penjualan',
                            'permissions' => [
                                'bo.sales_invoices.view' => 'Tampil & Lihat',
                                'bo.sales_invoices.create' => 'Tambah',
                                'bo.sales_invoices.edit' => 'Ubah',
                                'bo.sales_invoices.delete' => 'Hapus',
                            ],
                        ],
                        'bo_delivery_orders' => [
                            'label' => 'Surat Jalan (DO)',
                            'permissions' => [
                                'bo.delivery_orders.view' => 'Tampil & Lihat',
                                'bo.delivery_orders.create' => 'Tambah',
                                'bo.delivery_orders.edit' => 'Ubah',
                                'bo.delivery_orders.delete' => 'Hapus',
                            ],
                        ],
                    ],
                ],
                'bo_inventory' => [
                    'label' => 'Inventori & Persediaan',
                    'features' => [
                        'bo_inventory_mutations' => [
                            'label' => 'Mutasi Barang',
                            'permissions' => [
                                'bo.inventory_mutations.view' => 'Tampil & Lihat',
                                'bo.inventory_mutations.create' => 'Tambah',
                                'bo.inventory_mutations.edit' => 'Ubah',
                                'bo.inventory_mutations.delete' => 'Hapus',
                            ],
                        ],
                        'bo_sales_returns' => [
                            'label' => 'Retur Penjualan (Barang)',
                            'permissions' => ['bo.sales_returns.view' => 'Tampil & Lihat'],
                        ],
                        'bo_stock_movements' => [
                            'label' => 'Kartu Stok',
                            'permissions' => ['bo.stock_movements.view' => 'Tampil & Lihat'],
                        ],
                        'bo_stock_opname_periods' => [
                            'label' => 'Periode Stok Opname',
                            'permissions' => [
                                'bo.stock_opname_periods.view' => 'Tampil & Lihat',
                                'bo.stock_opname_periods.create' => 'Tambah',
                                'bo.stock_opname_periods.edit' => 'Ubah',
                                'bo.stock_opname_periods.delete' => 'Hapus',
                            ],
                        ],
                        'bo_stock_opname_presets' => [
                            'label' => 'Preset Lokasi Opname',
                            'permissions' => [
                                'bo.stock_opname_presets.view' => 'Tampil & Lihat',
                                'bo.stock_opname_presets.create' => 'Tambah',
                                'bo.stock_opname_presets.edit' => 'Ubah',
                                'bo.stock_opname_presets.delete' => 'Hapus',
                            ],
                        ],
                        'bo_stock_opnames' => [
                            'label' => 'Stok Opname',
                            'permissions' => [
                                'bo.stock_opnames.view' => 'Tampil & Lihat',
                                'bo.stock_opnames.create' => 'Tambah',
                                'bo.stock_opnames.edit' => 'Ubah',
                                'bo.stock_opnames.delete' => 'Hapus',
                            ],
                        ],
                        'bo_stock_opname_review' => [
                            'label' => 'Review Stok Opname',
                            'permissions' => [
                                'bo.stock_opname_review.view' => 'Tampil & Lihat',
                                'bo.stock_opname_review.approve' => 'Approve / Revisi / Finalisasi',
                            ],
                        ],
                        'bo_stock_transfers' => [
                            'label' => 'Transfer Antar Cabang',
                            'permissions' => [
                                'bo.stock_transfers.view' => 'Tampil & Lihat',
                                'bo.stock_transfers.create' => 'Tambah',
                                'bo.stock_transfers.edit' => 'Ubah',
                                'bo.stock_transfers.delete' => 'Hapus',
                            ],
                        ],
                        'bo_focus_products' => [
                            'label' => 'Produk Fokus',
                            'permissions' => [
                                'bo.focus_products.view' => 'Tampil & Lihat',
                                'bo.focus_products.create' => 'Tambah',
                                'bo.focus_products.edit' => 'Ubah',
                                'bo.focus_products.delete' => 'Hapus',
                            ],
                        ],
                        'bo_reports_stock_list' => [
                            'label' => 'Laporan Daftar Stok',
                            'permissions' => ['bo.reports.stock_list' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_stock_movement' => [
                            'label' => 'Laporan Mutasi Barang',
                            'permissions' => ['bo.reports.stock_movement' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_stock_opname' => [
                            'label' => 'Laporan Stok Opname',
                            'permissions' => ['bo.reports.stock_opname' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_ending_inventory' => [
                            'label' => 'Laporan Persediaan Akhir',
                            'permissions' => ['bo.reports.ending_inventory' => 'Tampil & Lihat'],
                        ],
                    ],
                ],
                'bo_accounting' => [
                    'label' => 'Akuntansi',
                    'features' => [
                        'bo_account_classifications' => [
                            'label' => 'Klasifikasi Akun',
                            'permissions' => [
                                'bo.account_classifications.view' => 'Tampil & Lihat',
                                'bo.account_classifications.create' => 'Tambah',
                                'bo.account_classifications.edit' => 'Ubah',
                                'bo.account_classifications.delete' => 'Hapus',
                            ],
                        ],
                        'bo_accounts' => [
                            'label' => 'Accounts / Bagan Akun (COA)',
                            'permissions' => [
                                'bo.accounts.view' => 'Tampil & Lihat',
                                'bo.accounts.create' => 'Tambah',
                                'bo.accounts.edit' => 'Ubah',
                                'bo.accounts.delete' => 'Hapus',
                            ],
                        ],
                        'bo_cash_transactions' => [
                            'label' => 'Transaksi Kas & Bank',
                            'permissions' => [
                                'bo.cash_transactions.view' => 'Tampil & Lihat',
                                'bo.cash_transactions.create' => 'Tambah',
                                'bo.cash_transactions.edit' => 'Ubah',
                                'bo.cash_transactions.delete' => 'Hapus',
                            ],
                        ],
                        'bo_journal_entries' => [
                            'label' => 'Jurnal Umum',
                            'permissions' => [
                                'bo.journal_entries.view' => 'Tampil & Lihat',
                                'bo.journal_entries.create' => 'Tambah',
                                'bo.journal_entries.edit' => 'Ubah',
                                'bo.journal_entries.delete' => 'Hapus',
                            ],
                        ],
                        'bo_scheduled_journal_entries' => [
                            'label' => 'Jurnal Umum Terjadwal',
                            'permissions' => [
                                'bo.scheduled_journal_entries.view' => 'Tampil & Lihat',
                                'bo.scheduled_journal_entries.create' => 'Tambah',
                                'bo.scheduled_journal_entries.edit' => 'Ubah',
                                'bo.scheduled_journal_entries.delete' => 'Hapus',
                            ],
                        ],
                        'bo_assets' => [
                            'label' => 'Master Aset Tetap',
                            'permissions' => [
                                'bo.assets.view' => 'Tampil & Lihat',
                                'bo.assets.create' => 'Tambah',
                                'bo.assets.edit' => 'Ubah',
                                'bo.assets.delete' => 'Hapus',
                            ],
                        ],
                        'bo_asset_disposals' => [
                            'label' => 'Disposisi & Penghapusan Aset',
                            'permissions' => [
                                'bo.asset_disposals.view' => 'Tampil & Lihat',
                                'bo.asset_disposals.create' => 'Tambah',
                                'bo.asset_disposals.edit' => 'Ubah',
                                'bo.asset_disposals.delete' => 'Hapus',
                            ],
                        ],
                        'bo_monthly_closing' => [
                            'label' => 'Tutup Buku Bulanan',
                            'permissions' => [
                                'bo.monthly_closing.view' => 'Tampil & Lihat',
                                'bo.monthly_closing.execute' => 'Eksekusi & Buka Ulang Tutup Buku',
                            ],
                        ],
                    ],
                ],
                'bo_financial_reports' => [
                    'label' => 'Laporan Keuangan',
                    'features' => [
                        'bo_reports_journal' => [
                            'label' => 'Laporan Jurnal',
                            'permissions' => ['bo.reports.journal' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_general_ledger' => [
                            'label' => 'Buku Besar (General Ledger)',
                            'permissions' => ['bo.reports.general_ledger' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_trial_balance' => [
                            'label' => 'Neraca Saldo (Trial Balance)',
                            'permissions' => ['bo.reports.trial_balance' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_income_statement' => [
                            'label' => 'Laba Rugi (Income Statement)',
                            'permissions' => ['bo.reports.income_statement' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_balance_sheet' => [
                            'label' => 'Balance Sheet (Neraca)',
                            'permissions' => ['bo.reports.balance_sheet' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_cash_book' => [
                            'label' => 'Laporan Kas & Bank',
                            'permissions' => ['bo.reports.cash_book' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_bank_ledger' => [
                            'label' => 'Buku Bank (Bank Ledger)',
                            'permissions' => ['bo.reports.bank_ledger' => 'Tampil & Lihat'],
                        ],
                        'bo_reports_vendor_ledger' => [
                            'label' => 'Buku Vendor (Kartu Hutang)',
                            'permissions' => ['bo.reports.vendor_ledger' => 'Tampil & Lihat'],
                        ],
                    ],
                ],
                'bo_settings' => [
                    'label' => 'Kelola User & Pengaturan',
                    'features' => [
                        'bo_users' => [
                            'label' => 'Users (Kelola Akun)',
                            'permissions' => [
                                'bo.users.view' => 'Tampil & Lihat',
                                'bo.users.create' => 'Tambah',
                                'bo.users.edit' => 'Ubah',
                                'bo.users.delete' => 'Hapus',
                            ],
                        ],
                        'bo_user_privileges' => [
                            'label' => 'Setting Hak Akses User',
                            'permissions' => [
                                'bo.user_privileges.view' => 'Tampil & Lihat',
                                'bo.user_privileges.manage' => 'Kelola Role & Hak Akses',
                            ],
                        ],
                        'bo_database_backup' => [
                            'label' => 'Backup Database',
                            'permissions' => [
                                'bo.database_backup.view' => 'Tampil & Lihat',
                                'bo.database_backup.manage' => 'Buat / Unduh / Hapus Backup',
                            ],
                        ],
                        'bo_po_smart_assist' => [
                            'label' => 'Smart Assist PO',
                            'permissions' => [
                                'bo.po_smart_assist.view' => 'Tampil & Lihat',
                                'bo.po_smart_assist.edit' => 'Ubah Pengaturan',
                            ],
                        ],
                        'bo_pos_action_otp' => [
                            'label' => 'OTP Otorisasi POS',
                            'permissions' => [
                                'bo.pos_action_otp.view' => 'Lihat Kode OTP & Riwayat',
                                'bo.pos_action_otp.refresh' => 'Refresh / Buat OTP Baru',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

];
