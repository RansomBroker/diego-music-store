# Subtasks: TASK-003 - Database Migrations for Products, Pricing Tiers, & Variants

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang skema field untuk tabel `products` (SKU, nama, deskripsi, tipe, harga beli, HPP awal, status).
  - [ ] Rancang skema field untuk tabel `product_variants` (produk induk, SKU varian, nama varian, stok, harga dasar).
  - [ ] Rancang skema pivot `product_tier_prices` dan `product_branch_prices` untuk menyimpan relasi harga dinamis.
- [ ] **Implementation**:
  - [ ] Buat file migration untuk tabel `pricing_tiers`.
  - [ ] Buat file migration untuk tabel `products`.
  - [ ] Buat file migration untuk tabel `product_variants`.
  - [ ] Buat file migration untuk tabel `product_tier_prices` dan `product_branch_prices`.
  - [ ] Buat file migration untuk tabel `product_bundles` (menghubungkan produk utama tipe bundling dengan produk komponen fisiknya).
  - [ ] Jalankan migration menggunakan script wrapper Docker (`./docker-artisan.sh migrate`).
- [ ] **Verification**:
  - [ ] Konfirmasi struktur tabel terbuat dengan benar di database PostgreSQL menggunakan query tool atau CLI.
