# Feature: FEATURE-002 - CRUD Master Barang & Varian (Bundling & Jasa)

## Parent Epic
- EPIC-001 - Master Data Setup & Basic Config

## Description
Fitur ini mencakup pembuatan katalog produk lengkap, yang mencakup barang fisik, varian (warna, ukuran, dll.), produk bundling (kombinasi beberapa barang fisik), dan produk jasa (dengan stok tidak terbatas). Fitur ini juga menyertakan HPP (Harga Pokok Penjualan) awal, input SKU/Barcode, harga cabang, serta 5 tingkat harga (pricing tiers) untuk mendukung POS lanjutan.

## Detailed Specifications
1. **Master Barang**:
   - Kolom: SKU/Barcode (unik), Nama Barang, Deskripsi, Tipe Produk (Fisik, Bundling, Jasa), Harga Beli, HPP Awal, Gambar Produk, Stok Minimum, Status Aktif.
2. **Pricing Tiers & Harga Cabang**:
   - Menyimpan 5 tingkat harga: Ritel, Member, Grosir, Agen, Distributor.
   - Menyimpan harga khusus per cabang (tabel pivot `branch_product` dengan kolom `harga_cabang`).
3. **Varian Produk**:
   - Hubungan One-to-Many dari produk utama ke varian (misal: Gitar Yamaha C40 -> Varian: Hitam, Natural).
   - Setiap varian memiliki SKU/Barcode, stok, dan harga tersendiri.
4. **Produk Bundling**:
   - Relasi ke beberapa produk fisik penyusunnya (misal: Paket Pemula Gitar = 1x Gitar + 1x Gigbag + 3x Pick).
   - Stok bundling dihitung dinamis berdasarkan stok terkecil dari produk fisiknya.
5. **Produk Jasa**:
   - Tipe produk jasa (misal: Les Musik, Reparasi) memiliki stok tidak terbatas (flag `is_unlimited` atau abaikan pengurangan stok).

## Acceptance Criteria
- [ ] Database tabel `products`, `product_variants`, `product_bundles`, dan `branch_product` telah didefinisikan dengan migration.
- [ ] Tersedia CRUD Produk di Filament Backoffice dengan form yang mendukung tipe Fisik, Bundling, dan Jasa.
- [ ] Form edit produk mendukung pengelolaan varian dan harga khusus per cabang.
- [ ] Input SKU/Barcode divalidasi keunikan datanya.

## Technical Implementation Details
- Buat migration untuk tabel terkait.
- Implementasikan form Filament menggunakan tab atau wizard agar mudah menginput varian dan komponen bundling.
- Implementasikan logic model Eloquent untuk kalkulasi dinamis stok bundling.
