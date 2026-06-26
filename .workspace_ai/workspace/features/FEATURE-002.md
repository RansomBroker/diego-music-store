# Feature: FEATURE-002 - CRUD Master Barang & Varian (Bundling & Jasa)

## Parent Epic
- EPIC-001 - Master Data Setup & Basic Config

## Description
Fitur ini mencakup pembuatan katalog produk lengkap, yang mencakup barang fisik, varian (warna, ukuran, dll.), produk bundling (kombinasi beberapa barang fisik), dan produk jasa (dengan stok tidak terbatas). Fitur ini juga menyertakan HPP (Harga Pokok Penjualan) awal, input SKU/Barcode, harga cabang, serta 5 tingkat harga (pricing tiers) untuk mendukung POS lanjutan.

## Detailed Specifications
1. **Master Barang**:
   - Kolom: SKU/Barcode (unik), Nama Barang, Deskripsi, Tipe Produk (Fisik, Bundling, Jasa), Harga Beli, HPP Awal, Gambar Produk, Stok Minimum, Status Aktif.
2. **Pricing Tiers & Harga Cabang**:
   - **Tabel `pricing_tiers`**: Menyimpan nama tingkatan harga secara dinamis (misal: "Emas", "Perak", "Grosir") yang dapat ditambahkan/dikelola secara mandiri oleh Owner di menu terpisah.
   - **Tabel `product_tier_prices`**: Menyimpan harga per produk untuk masing-masing tier yang aktif (relasi antara produk/varian dan `pricing_tiers`).
   - **Tabel `product_branch_prices`**: Menyimpan harga khusus produk per cabang (relasi antara produk/varian dan `branches`).
   - Di form input barang, sistem akan menarik data dari `pricing_tiers` dan `branches` yang aktif secara dinamis dan menampilkannya sebagai daftar kolom input harga (misal: "Harga Tier Emas: [input]", "Harga Cabang Depok: [input]").
3. **Varian Produk**:
   - Hubungan One-to-Many dari produk utama ke varian (misal: Gitar Yamaha C40 -> Varian: Hitam, Natural).
   - Setiap varian memiliki SKU/Barcode, stok, dan harga dasar tersendiri.
   - Penentuan harga tier dan harga cabang dapat diatur pada level varian (atau produk utama jika tidak ada varian).
4. **Produk Bundling**:
   - Relasi ke beberapa produk fisik penyusunnya (misal: Paket Pemula Gitar = 1x Gitar + 1x Gigbag + 3x Pick).
   - Stok bundling dihitung dinamis berdasarkan stok terkecil dari produk fisiknya.
5. **Produk Jasa**:
   - Tipe produk jasa (misal: Les Musik, Reparasi) memiliki stok tidak terbatas (flag `is_unlimited` atau abaikan pengurangan stok).

## Acceptance Criteria
- [x] Database tabel `products`, `product_variants`, `pricing_tiers`, `product_tier_prices`, `product_branch_prices`, dan `product_bundles` telah didefinisikan dengan migration.
- [x] Tersedia CRUD `PricingTierResource` terpisah untuk mengelola nama-nama tingkatan harga.
- [x] Tersedia CRUD `ProductResource` di Filament Backoffice dengan form yang mendukung tipe Fisik, Bundling, dan Jasa.
- [x] Di dalam form input produk, muncul field input dinamis untuk seluruh `pricing_tiers` dan `branches` aktif untuk mengisi harga masing-masing.
- [x] Form edit produk mendukung pengelolaan varian dan produk bundling.
- [x] Input SKU/Barcode divalidasi keunikan datanya.

## Technical Implementation Details
- Buat migration untuk tabel terkait.
- Gunakan field Filament seperti `Repeater` atau generate fields dinamis secara programatik (mengambil data `PricingTier::all()` dan `Branch::all()`) di dalam skema form `ProductResource`.
- Implementasikan form Filament menggunakan tab atau wizard agar mudah menginput varian dan komponen bundling.
- Implementasikan logic model Eloquent untuk kalkulasi dinamis stok bundling.
