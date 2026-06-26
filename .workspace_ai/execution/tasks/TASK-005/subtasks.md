# Subtasks: TASK-005 - Filament PricingTier & Product Resources

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang skema input form Filament untuk `PricingTierResource` (Nama, Deskripsi).
  - [ ] Rancang arsitektur input form dinamis di `ProductResource` (menarik data `PricingTier` dan `Branch` aktif dan me-render text field harga secara dinamis).
  - [ ] Desain interface form untuk mengelola Varian dan komponen Bundling.
- [ ] **Implementation**:
  - [ ] Generate `PricingTierResource` menggunakan command docker-artisan.
  - [ ] Rancang form & tabel pada `PricingTierResource` untuk operasi CRUD standar.
  - [ ] Generate `ProductResource` menggunakan command docker-artisan.
  - [ ] Di dalam form skema `ProductResource`, implementasikan logic dinamis untuk me-render input harga per tier dan per cabang menggunakan relational saving / custom lifecycle saving.
  - [ ] Tambahkan component Repeater/RelationManager untuk mengelola varian produk (SKU, stok awal, dll.).
  - [ ] Tambahkan component Repeater untuk produk tipe bundling guna memilih item penyusun dan kuantitasnya.
- [ ] **Verification**:
  - [ ] Tambahkan beberapa Pricing Tier (misal: Emas, Perak) di dashboard.
  - [ ] Coba input produk fisik, pastikan input harga dinamis untuk Emas, Perak, dan cabang yang ada ter-render dan tersimpan dengan benar di database.
  - [ ] Coba input produk bundling, pastikan item penyusun tersimpan dengan benar.
  - [ ] Uji validasi keunikan SKU/Barcode.
