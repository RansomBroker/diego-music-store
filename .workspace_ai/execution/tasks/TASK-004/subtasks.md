# Subtasks: TASK-004 - Eloquent Models & Relational/Bundling Logic

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang relasi antar model `Product`, `ProductVariant`, `PricingTier`, `ProductTierPrice`, `ProductBranchPrice`, dan `ProductBundle`.
  - [x] Desain metode accessor/helper Eloquent untuk menghitung stok bundling secara real-time.
- [x] **Implementation**:
  - [x] Buat model `Product` dan relasi ke `ProductVariant`, `PricingTier`, dsb.
  - [x] Buat model `ProductVariant` dan relasinya.
  - [x] Buat model `PricingTier` untuk mengelola data master tingkat harga.
  - [x] Buat model pivot `ProductTierPrice` dan `ProductBranchPrice`.
  - [x] Buat model `ProductBundle` untuk mendefinisikan relasi bundling.
  - [x] Tulis logika accessor `stok` pada model `Product` tipe bundling yang memeriksa sisa stok terkecil dari produk penyusunnya.
  - [x] Tulis logika penanganan stok tak terbatas (`is_unlimited`) jika tipe produk adalah Jasa.
- [x] **Verification**:
  - [x] Tulis test sederhana atau jalankan php artisan tinker untuk memverifikasi relasi dan perhitungan stok dinamis produk bundling & jasa.
