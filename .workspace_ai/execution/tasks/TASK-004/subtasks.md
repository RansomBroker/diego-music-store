# Subtasks: TASK-004 - Eloquent Models & Relational/Bundling Logic

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang relasi antar model `Product`, `ProductVariant`, `PricingTier`, `ProductTierPrice`, `ProductBranchPrice`, dan `ProductBundle`.
  - [ ] Desain metode accessor/helper Eloquent untuk menghitung stok bundling secara real-time.
- [ ] **Implementation**:
  - [ ] Buat model `Product` dan relasi ke `ProductVariant`, `PricingTier`, dsb.
  - [ ] Buat model `ProductVariant` dan relasinya.
  - [ ] Buat model `PricingTier` untuk mengelola data master tingkat harga.
  - [ ] Buat model pivot `ProductTierPrice` dan `ProductBranchPrice`.
  - [ ] Buat model `ProductBundle` untuk mendefinisikan relasi bundling.
  - [ ] Tulis logika accessor `stok` pada model `Product` tipe bundling yang memeriksa sisa stok terkecil dari produk penyusunnya.
  - [ ] Tulis logika penanganan stok tak terbatas (`is_unlimited`) jika tipe produk adalah Jasa.
- [ ] **Verification**:
  - [ ] Tulis test sederhana atau jalankan php artisan tinker untuk memverifikasi relasi dan perhitungan stok dinamis produk bundling & jasa.
