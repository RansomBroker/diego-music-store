<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Definisi Lengkap Modul & Permissions Diego Music Store ERP
    |--------------------------------------------------------------------------
    |
    | Daftar hak akses (permissions) terkelompok per modul untuk sistem RBAC.
    | Digunakan secara terpusat oleh RoleAndPermissionSeeder, Backoffice
    | UserPrivileges, dan Frontoffice POS Privileges.
    |
    */

    'modules' => [
        'POS & Kasir Toko' => [
            'pos.access' => 'Akses Front Office & Kasir POS',
            'pos.discount' => 'Beri Diskon Khusus / Manual',
            'pos.hold' => 'Simpan Transaksi Gantung (Hold)',
            'pos.void' => 'Batalkan Transaksi (Void)',
            'cash.session' => 'Buka & Tutup Sesi Kasir',
            'daily_cash.view' => 'Lihat Kas Harian Toko',
            'daily_cash.manage' => 'Input Kas Masuk & Keluar',
            'pos.service' => 'Manajemen Penerimaan & Ambil Service',
        ],

        'Penjualan & Piutang (Sales & AR)' => [
            'sales.quotations' => 'Surat Penawaran Harga (Sales Quotation)',
            'sales.invoices' => 'Faktur Penjualan B2B (Sales Invoice)',
            'sales.delivery_orders' => 'Surat Jalan Pengiriman (Delivery Order)',
            'sales.returns' => 'Retur Penjualan (Sales Return)',
            'sales.view_profit' => '🔒 Lihat HPP Penjualan, Laba Kotor & Margin Transaksi (Field-Level Sensitif)',
        ],

        'Pembelian & Hutang (Purchasing & AP)' => [
            'purchases.orders' => 'Purchase Order (PO)',
            'purchases.transactions' => 'Transaksi Pembelian Langsung',
            'purchases.returns' => 'Retur Pembelian ke Supplier',
            'supplier_payments.manage' => 'Pelunasan Hutang Supplier',
        ],

        'Persediaan & Pergudangan (Inventory)' => [
            'inventory.products' => 'Master Produk, Varian & Harga Modal',
            'inventory.view_cost' => '🔒 Lihat Harga Beli, HPP & Valuasi Persediaan (Field-Level Sensitif)',
            'inventory.mutations' => 'Mutasi Stok Masuk/Keluar Cabang',
            'inventory.transfers' => 'Transfer Stok Antar-Cabang',
            'inventory.movements' => 'Lihat Kartu Riwayat Stok',
            'inventory.stock_opname' => 'Hitung Fisik Stok Opname',
            'inventory.stock_opname_review' => 'Review & Approval Penyesuaian Opname',
            'inventory.focus_products' => 'Pengaturan Produk Fokus Penjualan',
        ],

        'Akuntansi & Keuangan (GL & Finance)' => [
            'accounting.coa' => 'Bagan Akun (COA) & Klasifikasi',
            'accounting.journals' => 'Input Jurnal Umum & Jurnal Berulang',
            'accounting.cash_bank' => 'Transaksi Kas & Bank',
            'accounting.assets' => 'Pencatatan Aset Tetap & Penyusutan',
            'accounting.closing' => 'Proses Tutup Buku Bulanan',
        ],

        'SDM & Penggajian (HRM & Payroll)' => [
            'hr.employees' => 'Kelola Data Personel Karyawan',
            'hr.view_salary' => '🔒 Lihat Gaji Pokok & Rekening Karyawan (Field-Level Sensitif)',
            'hr.attendance' => 'Jadwal Kerja, Radius & Log Absensi',
            'hr.commissions' => 'Skema & Perhitungan Komisi Sales',
            'hr.cash_advances' => 'Pengajuan & Potongan Kasbon',
            'hr.payroll' => 'Hitung & Cetak Penggajian (Payroll)',
            'hr.kpi' => 'Template & Evaluasi Kinerja KPI',
        ],

        'Laporan Finansial & Bisnis (Reports)' => [
            'reports.sales' => 'Laporan Penjualan & Piutang Pelanggan',
            'reports.purchases' => 'Laporan Pembelian & Hutang Supplier',
            'reports.inventory' => 'Laporan Nilai Persediaan Akhir & Mutasi Stok',
            'reports.accounting' => 'Laporan Keuangan (Laba Rugi, Neraca, Buku Besar, Trial Balance)',
        ],

        'Data Master & Pengaturan Sistem' => [
            'master.customers' => 'Kelola Master Pelanggan & Label',
            'master.suppliers' => 'Kelola Master Data Supplier',
            'master.units' => 'Kelola Satuan Barang',
            'master.categories' => 'Kelola Kategori Penjualan',
            'master.pricing_tiers' => 'Kelola Tingkat Harga (Pricing Tiers)',
            'master.payment_methods' => 'Kelola Metode Pembayaran & EDC',
            'master.vouchers' => 'Kelola Voucher & Promo Toko',
            'master.branches' => 'Kelola Data Cabang Toko',
            'master.users' => 'Kelola User Akun Login',
            'utility.store' => 'Profil Toko, Setting WhatsApp & Performa Cabang',
            'utility.receipt' => 'Pengaturan Template Struk & Printer',
            'utility.barcode' => 'Cetak Barcode Label Produk',
            'utility.backup' => 'Backup & Restore Database Sistem',
            'utility.privileges' => 'Setting Hak Akses User (Khusus Owner)',
        ],
    ],
];
