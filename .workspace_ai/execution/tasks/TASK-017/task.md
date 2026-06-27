# Task: TASK-017 - Filament Mutations & Stock Opname Resources with Stock Card Log

## Description
Buat Filament Resource untuk Mutasi Barang Antar-Cabang (`InventoryMutationResource`), Stok Opname (`StockOpnameResource`), dan integrasikan log pergerakan stok (Kartu Stok) per barang per gudang cabang.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-005 - Mutasi Barang & Stok Opname
- **Status**: Ready

## Acceptance Criteria
- [ ] CRUD `InventoryMutationResource` dapat mencatat mutasi antar-cabang dengan status *Draft*, *In-Transit* (stok cabang pengirim berkurang), dan *Received* (stok cabang penerima bertambah).
- [ ] CRUD `StockOpnameResource` dapat melakukan pencatatan jumlah fisik riil barang per cabang, menghitung selisih secara otomatis, dan mengupdate stok sistem saat status *Completed*.
- [ ] Sistem secara otomatis mencatat setiap penambahan/pengurangan stok dari semua transaksi (DO, POS, Mutasi, Opname) ke dalam tabel `stock_movements`.
- [ ] Buat visualisasi Kartu Stok (tampilan relasional history keluar-masuk) di `ProductResource` (atau halaman khusus) untuk memantau pergerakan produk.

## Assignee
- Developer
