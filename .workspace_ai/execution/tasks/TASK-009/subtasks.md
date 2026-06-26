# Subtasks: TASK-009 - Database Migration & Eloquent Model

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Desain skema tabel `cash_sessions` dengan tipe kolom keuangan dan indeks yang sesuai.
- [ ] **Implementation**:
  - [ ] Buat berkas migrasi `create_cash_sessions_table`.
  - [ ] Buat model Eloquent `CashSession`.
  - [ ] Hubungkan relasi `belongsTo` antara `CashSession`, `User`, dan `Branch`.
- [ ] **Verification**:
  - [ ] Jalankan migrasi dan pastikan tabel database `cash_sessions` terbuat dengan sukses.
