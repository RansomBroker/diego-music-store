# Task: TASK-024 - Form Pelunasan Hutang Grid/Table View & Checkbox Selection

## Description
Implementasikan layout grid/table pada form pelunasan hutang (Supplier Payments) yang memuat seluruh invoice pembelian Kredit milik supplier yang dipilih secara otomatis, lengkap dengan checkbox pilihan, sinkronisasi nilai bayar, dan summary panel.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: Pelunasan Hutang Grid/Table View
- **Status**: Done

## Acceptance Criteria
- [x] Menghapus input form select manual invoice di form detail pelunasan hutang.
- [x] Membuat template blade `items-table-header.blade.php` untuk menampilkan header kolom: Pilih, No. Invoice / Transaksi, Tgl. Transaksi, Jatuh Tempo, Total Tagihan, Sisa Hutang, dan Jumlah Bayar dengan layout grid.
- [x] Mengubah `SupplierPaymentForm` agar repeater `items` tidak addable/deletable (static list) dan ditata secara horizontal seperti tabel PO.
- [x] Melakukan query otomatis seluruh Kredit Purchase Transactions dengan status posted dan sisa tagihan > 0 milik supplier terpilih sewaktu supplier dipilih.
- [x] Mengimplementasikan checkbox pilihan (`is_selected`): sewaktu dicentang, jumlah bayar terisi sebesar sisa tagihan, sewaktu dimatikan jumlah bayar menjadi 0.
- [x] Mengimplementasikan sinkronisasi manual edit jumlah bayar: jika jumlah bayar > 0 maka checkbox otomatis tercentang, jika 0 atau kosong otomatis checkbox mati.
- [x] Menampilkan widget ringkasan pembayaran di bawah repeater yang menghitung total sisa tagihan keseluruhan, total pembayaran yang dicentang, dan estimasi sisa hutang akhir.
- [x] Memperbarui `mutateFormDataBeforeFill` di `EditSupplierPayment` untuk memuat data detail invoice dari relasi database beserta invoice outstanding lainnya yang belum terpilih.
- [x] Memperbarui `CreateSupplierPayment` dan `UpdateSupplierPayment` action class agar hanya memproses item dengan `is_selected` bernilai true dan `amount_paid` > 0.
- [x] Memperbarui unit/integration tests `SupplierPaymentActionsTest` untuk mendukung logika `is_selected` baru.
- [x] Membuat unit/integration test baru `SupplierPaymentFormTest` untuk memvalidasi interaksi reaktif form (otomatis check/uncheck dan sinkronisasi nilai bayar).
