# Subtasks: TASK-011 - Session validation and middleware

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Tentukan mekanisme terbaik (middleware, Filament page check, atau global filter) untuk validasi sesi aktif.
- [ ] **Implementation**:
  - [ ] Implementasikan check di halaman POS agar memvalidasi sesi kasir yang terasosiasi dengan `auth()->id()` dan cabang aktif.
  - [ ] Tambahkan logika pengalihan (*redirect*) ke halaman pembukaan sesi dengan pesan notifikasi yang jelas.
  - [ ] Opsional: Tambahkan bar info status sesi kasir di header admin/POS.
- [ ] **Verification**:
  - [ ] Uji coba akses POS secara langsung sebelum membuka sesi dan setelah membuka sesi untuk memastikan pengalihan berjalan dengan benar.
