# Subtasks: TASK-001 - Database Migration & Cabang Table Setup

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang skema database tabel `cabang` (id, nama, alamat, telepon, is_active).
  - [ ] Rancang tabel pivot `cabang_user` (user_id, cabang_id) untuk memetakan penugasan user ke cabang.
  - [ ] Tambahkan kolom `cabang_id` di database tabel `users` (opsional: sebagai default_cabang_id).
- [ ] **Implementation**:
  - [x] Buat file migration Laravel untuk tabel `cabang`.
  - [x] Buat file migration Laravel untuk tabel pivot `cabang_user`.
  - [ ] Jalankan `./docker-artisan.sh migrate` di container untuk menerapkan skema baru.
- [ ] **Verification**:
  - [ ] Periksa database menggunakan phpMyAdmin atau terminal untuk memastikan tabel berhasil dibuat.
