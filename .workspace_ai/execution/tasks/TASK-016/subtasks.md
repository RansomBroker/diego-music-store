# Subtasks: TASK-016 - Database Migration & Eloquent Models for Mutations, Opname, & Stock Cards

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang skema tabel mutasi barang dan stok opname.
  - [x] Rancang tabel `stock_movements` untuk kartu stok per cabang.
- [x] **Implementation**:
  - [x] Buat migration untuk tabel mutasi barang.
  - [x] Buat migration untuk tabel stok opname.
  - [x] Buat migration untuk tabel pergerakan stok.
  - [x] Jalankan `./docker-artisan.sh migrate`.
  - [x] Buat model Eloquent `InventoryMutation`, `InventoryMutationItem`, `StockOpname`, `StockOpnameItem`, `StockMovement` beserta relasi-relasinya.
- [x] **Verification**:
  - [x] Verifikasi kebenaran migrasi database dan relasi Eloquent melalui Tinker atau test case.
