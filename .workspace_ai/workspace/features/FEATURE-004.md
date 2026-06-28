# Feature: FEATURE-004 - Satuan Produk & Pengelolaan PO/DO

## Parent Epic
- EPIC-002 - Back Office Procurement & Inventory

## Description
Fitur ini mencakup pengelolaan rantai pasok pembelian barang dari supplier (Purchase Order / PO) dan verifikasi penerimaan fisik barang (Delivery Order / DO) ke cabang. Fitur ini juga mengintegrasikan satuan produk (Unit of Measure / UoM) serta perhitungan Harga Pokok Penjualan (HPP) otomatis per cabang menggunakan metode *Weighted Average* (Rata-rata Terbobot) yang menyerap ongkos kirim.

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Untuk mengelola siklus pengadaan barang (procurement) secara terstruktur dari supplier, memastikan jumlah barang yang diterima sesuai dengan yang dipesan, dan menghitung biaya modal (HPP) secara akurat per masing-masing cabang dengan menyertakan ongkos kirim.

## 2. User Roles & Aktor
- **Admin Gudang / Cabang**: Menginput draf PO, mencatat penerimaan barang (DO), memasukkan jumlah fisik yang diterima, dan menginput ongkos kirim real.
- **Supervisor / Owner**: Menyetujui PO (*Approved*) sebelum dapat ditindaklanjuti menjadi penerimaan barang (DO).

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Pengelolaan Satuan Produk (UoM)
- **Flow**: Admin mengelola daftar satuan barang (misal: Pcs, Set, Box) di menu Master Data. Satuan aktif akan terpilih di form pembuatan/edit barang.

### 3.2. Pembuatan Purchase Order (PO)
- **Flow**:
  1. Admin membuat dokumen PO baru, memilih supplier, menginput item varian barang, jumlah pesanan, dan harga beli satuan.
  2. Status PO awal adalah `Draft`.
  3. Dokumen diajukan ke Supervisor/Owner. Setelah disetujui, status PO berubah menjadi `Approved`.

### 3.3. Penerimaan Barang & Perhitungan HPP (Delivery Order / DO)
- **Pre-condition**: Terdapat dokumen PO dengan status `Approved`.
- **Main Flow**:
  1. Ketika barang fisik tiba di cabang, Admin membuat dokumen DO baru dan memilih PO referensi.
  2. Sistem menarik seluruh item dari PO tersebut ke form DO.
  3. Admin mengisi `do_number`, tanggal penerimaan, `shipping_cost` (ongkos kirim total), dan kuantitas barang fisik yang diterima (`quantity_received`) per item.
  4. Admin menekan tombol "Simpan Penerimaan (Set Received)".
  5. Sistem mengubah status DO menjadi `Received`, menambahkan stok fisik pada `product_branch_stocks` untuk cabang tersebut.
  6. Sistem memicu kalkulasi otomatis HPP Rata-rata Terbobot (Weighted Average HPP) per cabang untuk produk yang diterima dan mengupdate kolom `hpp` cabang tersebut.
- **Formula Weighted Average HPP Cabang**:
  $$\text{HPP Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Baru} \times (\text{Harga Beli} + \text{Ongkos Kirim Satuan}))}{\text{Stok Lama} + \text{Qty Baru}}$$
  Di mana:
  $$\text{Ongkos Kirim Satuan} = \frac{\text{Total Ongkos Kirim DO}}{\text{Total Qty Semua Item yang Diterima dalam DO}}$$

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Elemen Purchase Order (PO)
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `supplier_id` | Relasi | Y | Harus supplier terdaftar |
| `po_number` | String | Y | Unik, auto-generated |
| `order_date` | Date | Y | Minimal hari ini |
| `items` (Repeater) | Array | Y | Minimal 1 item (produk, qty, harga) |

### 4.2. Elemen Delivery Order (DO)
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `purchase_order_id` | Relasi | Y | Harus PO dengan status `Approved` |
| `branch_id` | Relasi | Y | Cabang penerima barang |
| `do_number` | String | Y | Nomor surat jalan dari supplier, wajib unik |
| `shipping_cost` | Decimal | Y | Ongkos kirim keseluruhan, default 0 |
| `items.quantity_received` | Integer | Y | Qty fisik diterima, harus $\ge 0$ |

## 5. Integrasi & Data Flow
- **Inventory Sync**: Mengubah status DO ke `Received` secara otomatis mencatatkan mutasi masuk ke `stock_movements` dan menambah stok di tabel `product_branch_stocks`.
- **Accounting Sync**: Pemrosesan DO akan memicu pembuatan jurnal otomatis (Persediaan Barang debit, Hutang Dagang kredit) pada modul akuntansi.

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [x] Tersedia CRUD Satuan Produk (Unit) di Back Office dan terintegrasi ke form input barang.
- [ ] CRUD `PurchaseOrderResource` dapat mengelola data PO (Draft, Approved), serta input item PO dan harganya.
- [ ] CRUD `DeliveryOrderResource` dapat menginput Qty fisik yang diterima per item vs Qty dipesan dari PO yang aktif, serta input `shipping_cost` (ongkos kirim).
- [ ] Saat status DO diselesaikan (*Received*), sistem secara otomatis menambahkan stok fisik di `product_branch_stocks` untuk cabang penerima.
- [ ] Nilai HPP Rata-rata Terbobot (Weighted Average) terhitung otomatis saat DO disimpan dengan memperhitungkan ongkos kirim.
