# Subtasks: TASK-017 - Filament Mutations & Stock Opname Resources with Stock Card Log

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang alur status mutasi barang (Draft -> In-Transit -> Received) dan pengurangan/penambahan stok cabang.
  - [x] Tentukan integrasi log pergerakan barang (`StockMovement`) pada service-service transaksi.
- [x] **Implementation**:
  - [x] Buat Filament Resource `InventoryMutationResource` beserta table dan form schema.
  - [x] Buat Filament Resource `StockOpnameResource`.
  - [x] Implementasikan pencatatan otomatis ke tabel `stock_movements` (menggunakan Eloquent model events/observers atau helper service).
  - [x] Tambahkan tab / section "Kartu Stok" di `ProductResource` untuk melihat pergerakan barang rill per cabang.
- [x] **Verification**:
  - [x] Uji alur mutasi barang antar cabang dan pastikan stok cabang pengirim berkurang dan cabang penerima bertambah.
  - [x] Uji stok opname dan pastikan stok sistem terupdate serta tercatat di kartu stok dengan benar.
