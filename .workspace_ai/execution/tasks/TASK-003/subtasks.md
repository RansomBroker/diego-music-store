# Subtasks: TASK-003 - Database Migrations for Products, Pricing Tiers, & Variants

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang skema field untuk tabel `products` (SKU, nama, deskripsi, tipe, harga beli, HPP awal, status).
  - [x] Rancang skema field untuk tabel `product_variants` (produk induk, SKU varian, nama varian, stok, harga dasar).
  - [x] Rancang skema pivot `product_tier_prices` dan `product_branch_prices` untuk menyimpan relasi harga dinamis.
- [x] **Implementation**:
  - [x] Buat file migration untuk tabel `pricing_tiers`.
  - [x] Buat file migration untuk tabel `products`.
  - [x] Buat file migration untuk tabel `product_variants`.
  - [x] Buat file migration untuk tabel `product_tier_prices` dan `product_branch_prices`.
  - [x] Buat file migration untuk tabel `product_bundles` (menghubungkan produk utama tipe bundling dengan produk komponen fisiknya).
  - [x] Jalankan migration menggunakan script wrapper Docker (`./docker-artisan.sh migrate`).
- [x] **Verification**:
  - [x] Konfirmasi struktur tabel terbuat dengan benar di database PostgreSQL menggunakan query tool atau CLI.
