# Epic: EPIC-002 - Back Office Procurement & Inventory

## Description
Epic ini mencakup pembangunan modul rantai pasok (Procurement) dan pengelolaan stok (Inventory) di tingkat Back Office. Ini mencakup manajemen Satuan Produk (UoM), Purchase Order (PO) ke supplier, Delivery Order (DO) / penerimaan barang masuk ke gudang, mutasi barang antar-cabang, stok opname, serta kalkulasi otomatis HPP Rata-rata Terbobot (Weighted Average) teratribusi ongkos kirim.

## User Stories / Features
- [x] **FEATURE-004: Satuan Produk & Pengelolaan PO/DO**
- [x] **FEATURE-005: Mutasi Barang & Stok Opname**
- [x] **FEATURE-005B: Integrasi Akuntansi Persediaan (Jurnal Umum & Otomatis)**

## Technical Roadmap & Dependencies
- Tergantung pada: EPIC-001 - Master Data Setup & Basic Config (khususnya data produk/barang, supplier, cabang, dan COA dasar)
- Target Waktu: Sprint 2 (Hari 13 - 26)

## Acceptance Criteria
- [x] Tersedia CRUD Satuan Produk (Unit) di Back Office dan terintegrasi ke form input barang.
- [x] Pembuatan PO ke supplier dan verifikasi penerimaan fisik barang melalui DO.
- [x] Nilai HPP Rata-rata Terbobot (Weighted Average) terhitung otomatis saat DO disimpan dengan memperhitungkan ongkos kirim.
- [x] Mendukung mutasi barang antar-cabang dengan pelacak status (In-Transit).
- [x] Form Stok Opname (fisik vs sistem) menghasilkan penyesuaian stok.
- [x] Pemetaan Chart of Accounts (Akun Persediaan, Penjualan, HPP) pada data master Produk.
- [x] Skema database & model Eloquent untuk `journal_entries` dan `journal_items` (Buku Besar).
- [x] Filament Resource Jurnal Umum dengan validasi balance Debit/Kredit sebelum disimpan.
- [x] Posting jurnal otomatis saat Transaksi Pembelian di-posted dan Stok Opname diselesaikan.

## Status
- **Status**: Done
- **Progress**: 100%
