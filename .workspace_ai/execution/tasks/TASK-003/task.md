# Task: TASK-003 - Database Migrations for Products, Pricing Tiers, & Variants

## Description
Buat skema database lengkap untuk mendukung katalog barang dinamis, termasuk tabel produk (`products`), varian (`product_variants`), produk bundling (`product_bundles`), pricing tiers (`pricing_tiers`), harga tier per produk (`product_tier_prices`), dan harga produk per cabang (`product_branch_prices`).

## Technical Details
- **Role**: Architect / Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-002 - CRUD Master Barang & Varian (Bundling & Jasa)
- **Status**: Ready

## Acceptance Criteria
- [ ] Migration untuk tabel `products` berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `product_variants` berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `pricing_tiers` berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `product_tier_prices` (pivot produk/varian dan pricing tier) berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `product_branch_prices` (pivot produk/varian dan cabang) berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `product_bundles` (komposisi produk bundling) berhasil dibuat dan dijalankan.

## Assignee
- Architect
