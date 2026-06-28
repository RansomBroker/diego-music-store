# Task: TASK-022 - POS Reports and Daily Z-Report PDF/Thermal Exports

## Description
Buat fitur pelaporan transaksi penjualan harian (Laporan POS) per cabang, per kasir, dan ringkasan Z-Report saat sesi kasir ditutup. Sediakan tombol unduh laporan berformat PDF untuk arsip manajerial.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-008 - Pelunasan Piutang & Laporan POS
- **Status**: Ready

## Acceptance Criteria
- [ ] Buat halaman laporan POS di Filament dengan filter Cabang, Kasir, dan Tanggal.
- [ ] Tampilkan ringkasan metrik: Total Omset, Total Tunai/Debit/Kredit, Total Transaksi, dan Sisa Piutang Berjalan.
- [ ] Z-Report dapat dicetak langsung dalam layout thermal (untuk kasir) dan diunduh sebagai file PDF (untuk supervisor/owner).
- [ ] Integrasikan export PDF menggunakan library PDF kustom (misal: Barryvdh DomPDF atau sejenisnya) yang sudah ada di proyek.

## Assignee
- Developer
