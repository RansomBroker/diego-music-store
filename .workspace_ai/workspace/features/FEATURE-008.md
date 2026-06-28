# Feature: FEATURE-008 - Pelunasan Piutang & Laporan POS

## Parent Epic
- EPIC-003 - Front Desk & POS Dasar

## Description
Fitur ini mencakup pelacakan penjualan kredit (piutang pelanggan) yang belum lunas, antarmuka pencatatan cicilan pelunasan piutang, penggunaan deposit pelanggan untuk transaksi, serta penyusunan laporan keuangan POS harian (ringkasan penjualan per kasir, per cabang, per metode pembayaran, dan rincian transaksi).

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Membantu toko musik mengelola penjualan tempo/kredit secara aman, melacak jatuh tempo tagihan pelanggan, mempermudah proses penagihan piutang, dan menyediakan laporan analitik performa kasir cabang secara harian.

## 2. User Roles & Aktor
- **Kasir**: Melihat daftar piutang pelanggan, mencatat transaksi pembayaran cicilan piutang, dan mencetak tanda terima pembayaran.
- **Admin Keuangan / Owner**: Mengakses laporan POS konsolidasian seluruh cabang dan menganalisis total piutang yang beredar.

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Pembayaran Piutang Pelanggan (Receivable Payment)
- **Pre-condition**: Terdapat transaksi penjualan POS dengan status pembayaran belum lunas (*unpaid* atau *partially paid*).
- **Main Flow**:
  1. Kasir membuka menu "Pelunasan Piutang".
  2. Kasir mencari berdasarkan nama pelanggan atau nomor invoice POS.
  3. Sistem menampilkan total tagihan, jumlah yang sudah dibayar, dan sisa piutang berjalan.
  4. Kasir menginput nominal pembayaran baru dan memilih metode pembayaran (Tunai/Transfer).
  5. Kasir menekan "Simpan Pelunasan".
  6. Sistem mencatat pembayaran di tabel history pembayaran piutang, mengurangi saldo piutang pelanggan, dan memperbarui status invoice menjadi `Paid` (jika sudah lunas) atau `Partially Paid`.

### 3.2. Generate Laporan POS Harian
- **Flow**:
  1. Pengguna membuka menu "Laporan POS".
  2. Pengguna memfilter data berdasarkan rentang tanggal, kasir, atau cabang.
  3. Sistem menampilkan metrik ringkasan (Total Omset Penjualan, Total Piutang Baru, Total Uang Tunai Diterima, Total Pembayaran Non-Tunai, Jumlah Invoice).
  4. Pengguna dapat mengekspor laporan tersebut ke format PDF atau Excel.

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Elemen Formulir Pembayaran Piutang
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `invoice_id` | Relasi | Y | Harus invoice POS yang belum lunas |
| `payment_date` | Date | Y | Maksimal hari ini |
| `amount_paid` | Decimal | Y | Harus angka > 0 dan $\le$ sisa piutang berjalan |
| `payment_method` | Enum | Y | `Tunai`, `Transfer Bank`, `Debit/Kredit` |

## 5. Integrasi & Data Flow
- **Accounting Sync**: Pembayaran piutang akan otomatis menghasilkan jurnal penerimaan kas/bank di sisi debit, dan mengurangi akun Piutang Dagang di sisi kredit.
- **Customer Loyalty**: Pelunasan piutang dapat dikonfigurasi untuk menambahkan poin loyalty pada pelanggan saat pembayaran piutang lunas.

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [ ] Tersedia menu daftar piutang pelanggan dengan informasi sisa tagihan secara akurat.
- [ ] Riwayat cicilan pelunasan piutang tersimpan lengkap dan mengurangi nilai piutang berjalan secara real-time.
- [ ] Status invoice POS terupdate otomatis dari `Unpaid` $\rightarrow$ `Partially Paid` $\rightarrow$ `Paid` sesuai akumulasi pelunasan.
- [ ] Laporan POS harian menyajikan pembagian omset berdasarkan metode pembayaran secara akurat (Tunai vs Non-Tunai).
- [ ] Dokumen laporan POS dapat diunduh dalam format PDF.
