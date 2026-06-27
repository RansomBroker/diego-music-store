# Subtasks: TASK-016 - Database Migration & Eloquent Models for Mutations, Opname, & Stock Cards

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang skema tabel mutasi barang dan stok opname.
  - [ ] Rancang tabel `stock_movements` untuk kartu stok per cabang.
- [ ] **Implementation**:
  - [ ] Buat migration untuk tabel mutasi barang.
  - [ ] Buat migration untuk tabel stok opname.
  - [ ] Buat migration untuk tabel pergerakan stok.
  - [ ] Jalankan `./docker-artisan.sh migrate`.
  - [ ] Buat model Eloquent `InventoryMutation`, `InventoryMutationItem`, `StockOpname`, `StockOpnameItem`, `StockMovement` beserta relasi-relasinya.
- [ ] **Verification**:
  - [ ] Verifikasi kebenaran migrasi database dan relasi Eloquent melalui Tinker atau test case.
