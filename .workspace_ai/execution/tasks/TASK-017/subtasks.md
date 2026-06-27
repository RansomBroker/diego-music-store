# Subtasks: TASK-017 - Filament Mutations & Stock Opname Resources with Stock Card Log

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang alur status mutasi barang (Draft -> In-Transit -> Received) dan pengurangan/penambahan stok cabang.
  - [ ] Tentukan integrasi log pergerakan barang (`StockMovement`) pada service-service transaksi.
- [ ] **Implementation**:
  - [ ] Buat Filament Resource `InventoryMutationResource` beserta table dan form schema.
  - [ ] Buat Filament Resource `StockOpnameResource`.
  - [ ] Implementasikan pencatatan otomatis ke tabel `stock_movements` (menggunakan Eloquent model events/observers atau helper service).
  - [ ] Tambahkan tab / section "Kartu Stok" di `ProductResource` untuk melihat pergerakan barang rill per cabang.
- [ ] **Verification**:
  - [ ] Uji alur mutasi barang antar cabang dan pastikan stok cabang pengirim berkurang dan cabang penerima bertambah.
  - [ ] Uji stok opname dan pastikan stok sistem terupdate serta tercatat di kartu stok dengan benar.
