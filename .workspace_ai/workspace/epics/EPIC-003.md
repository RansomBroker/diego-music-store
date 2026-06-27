# Epic: EPIC-003 - Front Desk & POS Dasar

## Description
Epic ini mencakup pembangunan modul Point of Sale (POS) utama untuk operasional kasir sehari-hari di cabang. Ini mencakup manajemen laci kas harian (sesi kasir), antarmuka penjualan kasir (POS), pencetakan struk penjualan, penanganan piutang pelanggan, dan laporan kasir harian (Z-Report).

## User Stories / Features
- [ ] **FEATURE-006: Sesi Kasir Harian & Laci Kas (Daily Cash Session)**
- [ ] **FEATURE-007: Core POS (Point of Sale)**
- [ ] **FEATURE-008: Pelunasan Piutang & Laporan POS**

## Technical Roadmap & Dependencies
- Tergantung pada: EPIC-002 - Back Office Procurement & Inventory (agar stok barang cabang dan HPP rill sudah terhitung dan tersedia untuk dijual)
- Target Waktu: Sprint 3 (Hari 27 - 40)

## Acceptance Criteria
- [ ] Sistem kasir memiliki siklus Buka Sesi (modal awal) dan Tutup Sesi (blind count, hitung selisih kas, Z-Report).
- [ ] Antarmuka POS mendukung pencarian barcode, keranjang belanja, pemilihan sales representative, dan cetak struk thermal.
- [ ] Sistem membatasi transaksi POS di luar sesi kasir yang aktif.
- [ ] Dapat mengelola pelunasan piutang pelanggan secara parsial maupun lunas.

## Status
- **Status**: Todo
- **Progress**: 0%
