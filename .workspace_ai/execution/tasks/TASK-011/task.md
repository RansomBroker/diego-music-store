# Task: TASK-011 - Session validation and middleware for POS transactions

## Description
Buat validasi sesi kasir aktif. Cegah kasir melakukan transaksi POS atau membuka halaman POS apabila tidak memiliki sesi laci kasir (`cash_sessions`) yang aktif (`open`) di cabang tersebut.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Front Desk & POS Dasar
- **Feature**: FEATURE-004 - Sesi Kasir Harian & Laci Kas
- **Status**: Ready

## Acceptance Criteria
- [ ] Implementasikan pemeriksaan keaktifan sesi kasir (middleware atau page validation) sebelum menampilkan halaman POS.
- [ ] Pengguna diarahkan ke halaman "Sesi Kasir" untuk membuka sesi kasir jika mencoba mengakses POS tanpa sesi aktif.
- [ ] Menambahkan banner status sesi kasir di bagian atas panel dashboard jika memungkinkan (info/bar absensi kasir).

## Assignee
- Developer
