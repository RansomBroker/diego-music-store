# Feature: FEATURE-005 - Mutasi Barang & Stok Opname

## Parent Epic
- EPIC-002 - Back Office Procurement & Inventory

## Description
Fitur ini memfasilitasi pemindahan stok barang antar-cabang (Mutasi Barang) dan audit stok fisik berkala per cabang (Stok Opname). Setiap perubahan stok dari transaksi pengadaan (DO), penjualan (POS), mutasi, maupun penyesuaian opname secara otomatis dicatat dalam log pergerakan stok (Kartu Stok / Stock Cards) untuk memantau histori stok secara historis.

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Untuk mengontrol perpindahan stok antar cabang secara aman, memvalidasi keakuratan data sistem dengan kondisi fisik gudang riil melalui audit stok opname, dan menyediakan visibilitas mutasi stok historis yang akurat melalui kartu stok guna meminimalisir kehilangan inventaris.

## 2. User Roles & Aktor
- **Admin Cabang / Gudang**: Membuat permohonan mutasi barang, mengkonfirmasi penerimaan mutasi, dan menginput hasil perhitungan fisik stok opname.
- **Supervisor / Owner**: Melakukan persetujuan terhadap stok opname yang memiliki selisih untuk di-posting ke sistem (memicu penyesuaian stok sistem dan jurnal koreksi).

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Mutasi Barang Antar-Cabang (Inventory Mutation)
- **Flow**:
  1. Admin membuat dokumen mutasi dengan memilih `sender_branch_id` (pengirim) dan `receiver_branch_id` (penerima), serta item-item dan kuantitasnya. Status awal dokumen adalah `Draft`.
  2. Saat barang dikirim, status diubah menjadi `In-Transit`. Sistem secara otomatis memotong stok fisik pada cabang pengirim.
  3. Setelah barang sampai di cabang tujuan, Admin cabang penerima memverifikasi jumlah barang dan mengubah status menjadi `Received`. Sistem secara otomatis menambahkan stok fisik pada cabang penerima.

### 3.2. Stok Opname (Stock Opname)
- **Flow**:
  1. Admin membuat dokumen stok opname baru untuk cabang tertentu. Status dokumen awal `Draft`.
  2. Sistem menarik seluruh data produk/varian aktif beserta stok sistem saat ini (`system_qty`).
  3. Admin menghitung fisik barang dan menginput hasilnya ke field kuantitas fisik (`physical_qty`).
  4. Sistem menghitung selisih secara otomatis: `difference = physical_qty - system_qty`.
  5. Setelah data terinput lengkap, status diubah menjadi `Completed`. Sistem mengupdate stok sistem pada `product_branch_stocks` menjadi sama dengan `physical_qty`, serta mencatat transaksi mutasi keluar/masuk di `stock_movements`.

### 3.3. Pencatatan Kartu Stok (Stock Movements)
- **Flow**:
  1. Setiap transaksi yang memengaruhi stok (`DO`, `Mutation`, `Opname`, `POS`) secara otomatis membuat log record di tabel `stock_movements`.
  2. Halaman detail produk menampilkan log history keluar-masuk barang secara kronologis per cabang untuk mempermudah pelacakan.

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Elemen Mutasi Barang
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `sender_branch_id` | Relasi | Y | Cabang pengirim |
| `receiver_branch_id` | Relasi | Y | Cabang penerima, tidak boleh sama dengan pengirim |
| `status` | Enum | Y | `draft`, `transit`, `received` |
| `items.quantity` | Integer | Y | Qty yang dimutasi, harus > 0 dan $\le$ stok cabang pengirim |

### 4.2. Elemen Stok Opname
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `branch_id` | Relasi | Y | Cabang yang diaudit |
| `opname_date` | Date | Y | Tanggal pelaksanaan |
| `items.physical_qty` | Integer | Y | Jumlah fisik yang dihitung, harus $\ge 0$ |

## 5. Integrasi & Data Flow
- **Stock Card Sync**: Setiap perubahan status mutasi (`transit` / `received`) atau stok opname (`completed`) langsung men-trigger penambahan baris data baru ke tabel `stock_movements`.
- **Accounting Sync**: Finalisasi stok opname (`completed`) yang memiliki selisih akan otomatis memicu jurnal penyesuaian nilai persediaan di modul akuntansi.

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [ ] CRUD `InventoryMutationResource` dapat mencatat mutasi antar-cabang dengan status *Draft*, *In-Transit* (stok cabang pengirim berkurang), dan *Received* (stok cabang penerima bertambah).
- [ ] CRUD `StockOpnameResource` dapat melakukan pencatatan jumlah fisik riil barang per cabang, menghitung selisih secara otomatis, dan mengupdate stok sistem saat status *Completed*.
- [ ] Sistem secara otomatis mencatat setiap penambahan/pengurangan stok dari semua transaksi (DO, POS, Mutasi, Opname) ke dalam tabel `stock_movements`.
- [ ] Tersedia halaman atau komponen visualisasi Kartu Stok (tampilan relasional history keluar-masuk) di admin panel.
