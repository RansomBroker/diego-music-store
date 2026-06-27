# Subtasks: TASK-012 - Database Migration, Eloquent Model, & Filament CRUD for Units (Satuan Produk)

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang skema tabel `units` (id, name, code, is_active, timestamps).
  - [x] Rancang peletakan menu `UnitResource` di sidebar Filament.
- [x] **Implementation**:
  - [x] Jalankan command pembuatan migration untuk tabel `units`.
  - [x] Isi file migration dan jalankan `./docker-artisan.sh migrate`.
  - [x] Buat model Eloquent `Unit` di `app/Models/Unit.php`.
  - [x] Jalankan command pembuatan Filament Resource `UnitResource`.
  - [x] Konfigurasi form dan table schema di `UnitResource.php`.
- [x] **Verification**:
  - [x] Akses halaman admin, buat data satuan baru (misal: name = "Pieces", code = "pcs").
  - [x] Verifikasi data tersimpan dengan benar di database.
