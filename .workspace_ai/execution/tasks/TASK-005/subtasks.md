# Subtasks: TASK-005 - Filament PricingTier & Product Resources

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang skema input form Filament untuk `PricingTierResource` (Nama, Deskripsi).
  - [x] Rancang arsitektur input form dinamis di `ProductResource` (menarik data `PricingTier` dan `Branch` aktif dan me-render text field harga secara dinamis).
  - [x] Desain interface form untuk mengelola Varian dan komponen Bundling.
- [x] **Implementation**:
  - [x] Generate `PricingTierResource` menggunakan command docker-artisan.
  - [x] Rancang form & tabel pada `PricingTierResource` untuk operasi CRUD standar.
  - [x] Generate `ProductResource` menggunakan command docker-artisan.
  - [x] Di dalam form skema `ProductResource`, implementasikan logic dinamis untuk me-render input harga per tier dan per cabang menggunakan relational saving / custom lifecycle saving.
  - [x] Tambahkan component Repeater/RelationManager untuk mengelola varian produk (SKU, stok awal, dll.).
  - [x] Tambahkan component Repeater untuk produk tipe bundling guna memilih item penyusun dan kuantitasnya.
- [x] **Verification**:
  - [x] Tambahkan beberapa Pricing Tier (misal: Emas, Perak) di dashboard.
  - [x] Coba input produk fisik, pastikan input harga dinamis untuk Emas, Perak, dan cabang yang ada ter-render dan tersimpan dengan benar di database.
  - [x] Coba input produk bundling, pastikan item penyusun tersimpan dengan benar.
  - [x] Uji validasi keunikan SKU/Barcode.
