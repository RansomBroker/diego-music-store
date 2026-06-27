# Task: TASK-010 - Cashier Session Management Interface in Filament

## Description
Buat antarmuka manajemen sesi kasir (Buka/Tutup Sesi) di Filament. Halaman ini digunakan oleh kasir untuk menginput modal awal, memantau estimasi kas berjalan, melakukan tutup sesi (blind count), dan mengotorisasi selisih kas dengan supervisor.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-006 - Sesi Kasir Harian & Laci Kas (Daily Cash Session)
- **Status**: Ready

## Acceptance Criteria
- [ ] Tersedia halaman/modal Filament untuk manajemen Buka Sesi (mengisi modal awal).
- [ ] Tersedia halaman/modal Filament untuk manajemen Tutup Sesi (mengisi blind count actual cash).
- [ ] Sistem menghitung expected cash dan difference secara akurat pada saat penutupan.
- [ ] Muncul modal PIN/Password otorisasi supervisor apabila terdapat selisih kas antara expected cash dan actual cash.
- [ ] Z-Report berhasil digenerate dan siap dicetak setelah sesi kasir ditutup.

## Assignee
- Developer
