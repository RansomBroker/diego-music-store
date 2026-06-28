# Feature: FEATURE-007 - Core POS (Point of Sale)

## Parent Epic
- EPIC-003 - Front Desk & POS Dasar

## Description
Fitur utama antarmuka kasir untuk melakukan transaksi penjualan retail secara cepat dan efisien. Fitur ini mendukung pencarian produk (melalui scan barcode atau pengetikan nama), manajemen keranjang belanja (tambah, edit qty, hapus, diskon item/transaksi), penandaan Sales Representative (untuk perhitungan komisi), fitur penahanan keranjang (*hold / recall cart*), transaksi pembayaran tunggal (tunai/debit/kredit), serta pencetakan struk kasir format thermal.

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Menyediakan antarmuka Point of Sale (POS) yang responsif, intuitif, dan cepat bagi kasir toko musik untuk memproses transaksi ritel harian, merekam data penjualan, dan memfasilitasi pencetakan bukti transaksi bagi pelanggan.

## 2. User Roles & Aktor
- **Kasir**: Membuka halaman POS, memproses transaksi, menggunakan fitur *hold/recall*, memproses pembayaran, dan mencetak struk.
- **Sales Representative (Aktor Pasif)**: Dipilih oleh kasir pada setiap item/transaksi untuk merekam kontribusi penjualan guna perhitungan komisi.

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Pemrosesan Transaksi Penjualan POS
- **Pre-condition**: Kasir memiliki sesi kasir berstatus `open`.
- **Main Flow**:
  1. Kasir membuka halaman transaksi POS.
  2. Kasir mencari produk dengan mengetikkan nama/SKU atau men-scan barcode produk/varian.
  3. Sistem menambahkan produk ke keranjang belanja dengan kuantitas default = 1.
  4. Kasir dapat menyesuaikan jumlah kuantitas, menerapkan diskon (nominal atau persen), atau menghapus item dari keranjang.
  5. Kasir memilih Sales Representative yang melayani transaksi.
  6. Kasir menekan tombol "Bayar" (Checkout).
  7. Kasir memilih metode pembayaran (Tunai, Debit, atau Kredit) dan memasukkan jumlah uang yang dibayarkan.
  8. Sistem menghitung kembalian (jika ada) dan menekan "Konfirmasi Bayar".
  9. Sistem menyimpan transaksi penjualan, mengurangi stok barang di cabang bersangkutan, membuat log kartu stok, dan mencetak struk thermal secara otomatis.

### 3.2. Hold & Recall Keranjang Belanja
- **Flow**:
  1. Jika pelanggan ingin mengambil barang tambahan, kasir dapat menekan tombol "Hold".
  2. Sistem menyimpan draf keranjang belanja saat ini dan membersihkan layar POS agar kasir dapat melayani pelanggan berikutnya.
  3. Saat pelanggan kembali, kasir menekan tombol "Recall", memilih antrean keranjang yang ditahan, dan sistem memuat kembali item-item tersebut ke keranjang aktif untuk diselesaikan.

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Bidang Validasi Pembayaran
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `customer_id` | Relasi | T | Opsional (jika kosong, dianggap "General Customer") |
| `sales_rep_id`| Relasi | Y | Harus memilih salah satu Sales Representative aktif |
| `payment_method` | Enum | Y | `Tunai`, `Debit`, `Kredit` |
| `amount_paid` | Decimal | Y | Harus $\ge$ total tagihan transaksi |

## 5. Integrasi & Data Flow
- **Inventory Sync**: Transaksi sukses memicu pengurangan kuantitas stok di `product_branch_stocks` untuk cabang aktif dan mencatat log `stock_movements`.
- **Printer Integration**: Sistem mengirimkan dokumen print layout langsung ke printer thermal lokal (ukuran lebar kertas 58mm atau 80mm).

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [ ] Halaman POS hanya bisa diakses apabila sesi kasir cabang bersangkutan berstatus `open`.
- [ ] Barcode scanner berhasil mendeteksi dan menginput varian produk ke keranjang belanja secara instan.
- [ ] Fitur diskon item dan diskon faktur berfungsi sesuai perhitungan matematika dasar.
- [ ] Fitur *Hold* dan *Recall* berjalan lancar tanpa menghilangkan data keranjang yang disimpan sementara.
- [ ] Stok cabang berkurang secara otomatis setelah transaksi penjualan diselesaikan.
- [ ] Sistem memicu dialog cetak struk thermal setelah pembayaran terkonfirmasi.
