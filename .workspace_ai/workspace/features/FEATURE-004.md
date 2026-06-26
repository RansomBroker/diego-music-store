# Feature: FEATURE-004 - Sesi Kasir Harian & Laci Kas (Daily Cash Session)

## Parent Epic
- EPIC-002 - Front Desk & POS Dasar

## Description
Fitur ini mengelola laci kas harian (sesi kasir). Kasir diwajibkan membuka sesi dengan menginput uang modal awal sebelum melakukan transaksi. Saat shift selesai, kasir melakukan tutup sesi dengan metode *blind count* (menghitung fisik uang riil tanpa melihat ekspektasi sistem), lalu sistem membandingkan nilai fisik dan ekspektasi untuk mengetahui selisih kurang/lebih kas, diikuti dengan pencetakan Z-Report.

## Detailed Specifications
1. **Siklus Sesi Kasir**:
   - Status Sesi: `open`, `closed`.
   - Hanya diperbolehkan satu sesi `open` per kasir per cabang dalam satu waktu.
   - Transaksi POS hanya diperbolehkan jika kasir memiliki sesi yang berstatus `open`.
2. **Buka Sesi (Open Session)**:
   - Form input: Modal Awal Kas Laci (`opening_cash`).
   - Secara otomatis mencatat `user_id`, `cabang_id`, dan waktu buka (`opened_at`).
3. **Tutup Sesi (Close Session & Blind Count)**:
   - Form input: Nilai Uang Fisik Riil di laci (`actual_cash`) dan Catatan (`notes`).
   - Sistem secara otomatis menghitung `expected_cash` berdasarkan rumus: `opening_cash` + total transaksi tunai masuk - total pengeluaran laci kas.
   - Selisih dihitung sebagai: `actual_cash` - `expected_cash`.
   - Mencetak Z-Report (ringkasan penjualan tunai, non-tunai, modal awal, dan selisih kas).
4. **Otorisasi Supervisor / Owner**:
   - Jika terdapat perbedaan (selisih kas), sistem membutuhkan otorisasi supervisor (input PIN/Password) untuk melanjutkan penutupan sesi.

## Acceptance Criteria
- [ ] Database tabel `cash_sessions` telah didefinisikan dengan migration.
- [ ] Kasir tidak bisa mengakses halaman POS jika belum membuka sesi kasir.
- [ ] Form Buka Sesi menyimpan modal awal kas dengan benar.
- [ ] Form Tutup Sesi melakukan perhitungan selisih kas secara tepat (Blind Counting).
- [ ] Tombol Tutup Sesi mencetak Z-Report ringkasan keuangan sesi.
- [ ] Logika pembatalan atau persetujuan selisih memerlukan otorisasi Supervisor.

## Technical Implementation Details
- Buat migration `create_cash_sessions_table`.
- Buat model `CashSession` dan relasi ke `User` serta `Branch`.
- Implementasikan halaman/modal Filament untuk manajemen Buka/Tutup Sesi Kasir.
- Tambahkan Middleware atau Policy di `POSResource` untuk memeriksa keaktifan sesi kasir saat mengakses penjualan.
