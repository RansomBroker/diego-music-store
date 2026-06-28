# Subtasks: TASK-018 - Database Migration & Eloquent Models for Sales Transactions & Payments (POS)

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang struktur tabel `sales` dan `sale_items` untuk mendukung relasi multi-cabang, kasir, sales rep, dan sesi kasir.
- [ ] **Implementation**:
  - [ ] Buat file migration untuk tabel `sales` dan `sale_items`.
  - [ ] Definisikan model Eloquent `Sale` dan `SaleItem` beserta relasi-relasinya di Laravel.
  - [ ] Jalankan `./docker-artisan.sh migrate` untuk menerapkan perubahan skema.
- [ ] **Verification**:
  - [ ] Pastikan migration berjalan tanpa error dan relasi database terpetakan dengan benar.
