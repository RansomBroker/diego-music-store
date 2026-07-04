# Task: TASK-011 - Session validation and middleware for POS transactions

## Description
Buat validasi sesi kasir aktif. Cegah kasir melakukan transaksi POS atau membuka halaman POS apabila tidak memiliki sesi laci kasir (`cash_sessions`) yang aktif (`open`) di cabang tersebut.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-006 - Sesi Kasir Harian & Laci Kas (Daily Cash Session)
- **Status**: Completed

## Acceptance Criteria
- [x] Implementasikan pemeriksaan keaktifan sesi kasir (middleware atau page validation) sebelum menampilkan halaman POS.
- [x] Pengguna diarahkan ke halaman "Sesi Kasir" untuk membuka sesi kasir jika mencoba mengakses POS tanpa sesi aktif.
- [x] Menambahkan banner status sesi kasir di bagian atas panel dashboard jika memungkinkan (info/bar absensi kasir).

## Assignee
- Developer
