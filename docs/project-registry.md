# Project Registry (Brain)

Peta komponen arsitektur Diego Music Store (ERP, POS & Accounting). Jaga file ini tetap ringkas (<2.5 KB) tanpa mendaftar method CRUD.

## Architecture Pattern
- Pola bisnis menggunakan **Action Pattern** (`app/Actions/<Domain>/`), bukan Service/Repository. Setiap Action fokus pada 1 tanggung jawab spesifik (`execute()` atau `handle()`).

## Core Domains & Action Modules (`app/Actions/`)
- `Pos/`: Transaksi kasir, held transactions, pembayaran multi-metode, voucher, & integrasi stok.
- `SalesQueue/`: Antrian giliran melayani sales (round-robin), on-duty tracking, & laporan performa.
- `Accounting/`: General ledger, pembuatan journal entry otomatis/terjadwal, COA, & monthly closing.
- `Product/`: Manajemen master produk, varian, pricing tier, branch stock, barcode, & bundle.
- `PurchaseTransaction/` & `PurchaseOrder/`: Siklus pengadaan, PO, penerimaan barang (DO), & hutang supplier.
- `SalesInvoice/`, `SalesQuotation/`, `SalesReturn/`: Faktur penjualan B2B, penawaran, retur, & piutang.
- `CashSession/`: Buka/tutup shift kasir, modal awal, & rekonsiliasi kas.
- `StockOpname/`, `StockTransfer/`, `InventoryMutation/`: Mutasi stok antar-cabang, opname, & kartu stok.
- `Employee/`, `Payroll/`, `Attendance/`, `Commission/`: HRIS, absensi/radius, hitung komisi sales, & penggajian.
- `Customer/` & `Supplier/`: Master data kontak, deposit pelanggan, & histori transaksi.
- `Branch/`: Konfigurasi multi-cabang, prefix nota, pengaturan toko, & provisioning replikasi COA per cabang.

## Key Models (`app/Models/`)
- Sales & POS: `Sale`, `SaleItem`, `PosHeldTransaction`, `PaymentMethod`, `Voucher`, `SalesQueueMember`, `SalesDutyLog`
- Accounting: `Account`, `AccountClassification`, `JournalEntry`, `JournalItem`, `MonthlyClosing`
- Inventory: `Product`, `ProductVariant`, `ProductBranchStock`, `StockMovement`, `StockTransfer`
- Purchasing: `PurchaseOrder`, `PurchaseTransaction`, `DeliveryOrder`, `SupplierPayment`
- HR & Org: `Branch`, `Employee`, `Payroll`, `SalesCommissionLog`, `User`

## Schema Inspection (JIT)
- Jangan catat daftar kolom/relasi di sini. Gunakan inspection realtime:
  `docker exec -w /var/www/public/diego-music-store lemp-app-diego-music-store php artisan model:show <ModelName>`
  *(Fallback: baca langsung file `app/Models/<ModelName>.php`)*
