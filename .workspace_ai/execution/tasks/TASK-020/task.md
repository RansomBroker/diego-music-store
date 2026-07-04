# Task: TASK-020 - POS Transaction Submission, Stock Deductions & Receipt Printing

## Description
Implementasikan backend logic untuk checkout transaksi POS. Ini mencakup pengurangan stok barang cabang secara otomatis, pencatatan mutasi stok ke `stock_movements`, validasi kecukupan uang pembayaran, serta integrasi pencetakan bukti transaksi (struk belanja) format thermal.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-007 - Core POS (Point of Sale)
- **Status**: Done

## Acceptance Criteria
- [x] Tombol checkout menyimpan transaksi ke database pada tabel `sales` dan `sale_items`.
- [x] Pengurangan stok barang di tabel `product_branch_stocks` terjadi secara akurat untuk cabang aktif terkait.
- [x] Setiap transaksi POS yang tersimpan wajib mencatatkan log pergerakan stok keluar ke tabel `stock_movements` (tipe 'out', reference_type 'POS').
- [x] Struk thermal ter-generate secara dinamis dan memunculkan pop-up print browser otomatis setelah checkout sukses.

## Assignee
- Developer
