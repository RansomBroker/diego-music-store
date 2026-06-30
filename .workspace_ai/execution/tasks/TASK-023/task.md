# Task: TASK-023 - Integrasi Akuntansi Persediaan (Jurnal Umum & Otomatis)

## Description
Implemetasikan pemetaan akun pada produk, skema database jurnal umum (general ledger), form/sistem Jurnal Umum di Filament, serta integrasi posting jurnal otomatis dari transaksi pembelian (Purchase) dan stok opname (Opname).

## Technical Details
- **Role**: Developer / Architect
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-005B - Integrasi Akuntansi Persediaan (Jurnal Umum & Otomatis)
- **Status**: Done

## Acceptance Criteria
- [x] Migration untuk tabel `products` menambahkan kolom akun persediaan, penjualan, dan HPP.
- [x] Tab "Akuntansi" di Filament `ProductForm` untuk memetakan akun persediaan, penjualan, dan HPP ke Chart of Accounts (COA) dengan opsi default.
- [x] Migration untuk tabel `journal_entries` dan `journal_items` (Buku Besar) beserta model Eloquent `JournalEntry` dan `JournalItem`.
- [x] Filament Resource `JournalEntryResource` (Jurnal Umum) di bawah grup "Akuntansi" untuk melihat, membuat, dan memposting jurnal manual dengan validasi debit-kredit seimbang.
- [x] Logika posting jurnal otomatis saat posting Transaksi Pembelian (`PostPurchaseTransaction`) dan penyesuaian Stok Opname.

## Assignee
- Developer
