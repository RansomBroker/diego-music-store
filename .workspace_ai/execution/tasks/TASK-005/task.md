# Task: TASK-005 - Filament PricingTier & Product Resources

## Description
Buat Filament Resource untuk `PricingTier` agar Owner dapat menambah/edit nama tier harga, serta buat `ProductResource` yang memiliki form input dinamis. Form produk harus secara otomatis memunculkan kolom harga untuk semua `pricing_tiers` dan `branches` yang aktif. Form juga harus mendukung tab varian (untuk tipe fisik) dan form pencarian item penyusun (untuk tipe bundling).

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-002 - CRUD Master Barang & Varian (Bundling & Jasa)
- **Status**: Merged/Completed

## Acceptance Criteria
- [x] CRUD `PricingTier` dapat digunakan untuk mengelola nama-nama tingkatan harga.
- [x] CRUD `Product` dapat digunakan untuk menambah produk dengan tipe Fisik, Bundling, dan Jasa.
- [x] Di dalam form produk, muncul field input harga secara dinamis untuk seluruh tier harga dan cabang yang terdaftar di database.
- [x] Form produk mendukung tab pengelolaan varian (warna, ukuran, dll.) dan penentuan harga khusus per varian.
- [x] Form produk tipe bundling mendukung pemilihan produk-produk komponen penyusunnya beserta kuantitas masing-masing.

## Assignee
- Developer
