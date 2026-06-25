# Product Requirements Document (PRD)

Enterprise Resource Planning (ERP) Diego Music Store

# Informasi Dokumen

Dokumen ini merangkum detail penting mengenai Product Requirements Document (PRD) ERP Diego Music Store, mencakup informasi proyek, versi, dan tanggal penyusunan.

| Item | Keterangan |
| --- | --- |
| Nama Proyek | ERP Diego Music Store |
| Klien | Diego Music Store |
| Versi Dokumen | 1.0 |
| Tanggal | 21/06/2026 |
| Penyusun | Yadistira Fajar Ramadhan |

# Tujuan Sistem

Sistem ERP Diego Music Store dibangun dengan tujuan untuk menyediakan platform manajemen bisnis terintegrasi (*all-in-one*) yang mengotomatisasi seluruh lini operasional toko musik. Tujuan utama dari pengembangan sistem ini meliputi:

1. **Efisiensi Operasional Kasir (Front Desk)**: Menyederhanakan proses transaksi ritel (POS) langsung, pencatatan metode pembayaran gabungan (*mix payment*), penagihan/cetak struk, serta penerimaan dan pelacakan unit servis instrumen musik hingga siap diserahkan ke pelanggan.
2. **Sentralisasi Multi-Cabang & Inventaris**: Memfasilitasi pengelolaan stok secara real-time di seluruh cabang fisik, meminimalkan selisih stok melalui mekanisme mutasi barang dan stok opname yang terkontrol, serta menerapkan fleksibilitas penentuan harga (tingkat harga ritel dinamis/tiering master dan harga khusus cabang).
3. **Akurasi dan Otomatisasi Akuntansi**: Mengeliminasi pembukuan manual dengan mengintegrasikan transaksi POS, pembelian (PO/DO), depresiasi aset tetap, dan beban biaya operasional secara langsung ke dalam jurnal umum, buku besar, neraca, hingga laporan laba rugi (*Income Statement*).
4. **Manajemen Sumber Daya Manusia & Penggajian**: Mengotomatiskan rekapitulasi data kehadiran (integrasi fingerprint dan absensi visual foto), perhitungan denda/keterlambatan, potongan kasbon karyawan, komisi sales bertingkat, serta penyusunan slip gaji periodik yang terhubung dengan pencapaian KPI.
5. **Peningkatan CRM & Layanan Pelanggan**: Meningkatkan retensi pelanggan melalui sistem loyalitas member (poin belanja) serta integrasi WhatsApp Gateway untuk pengiriman invoice berformat PDF secara instan dan siaran informasi promosi (*broadcast*).


# User Role

Sistem ERP Diego Music Store mendefinisikan 4 peran pengguna (*user roles*) dengan pembagian hak akses (*privileges*) sebagai berikut:

### 1. Owner (Super Admin / Pemilik Toko)
*   **Deskripsi**: Pemilik entitas bisnis Diego Music Store.
*   **Hak Akses**: Hak akses penuh (*full access*) ke seluruh modul sistem tanpa batasan.
*   **Fitur Utama**:
    *   Mengakses Dashboard Owner (analisis visual konsolidasi seluruh cabang, grafik Pareto 80/20, perputaran stok, dan tren omset).
    *   Melihat dan mengelola seluruh data Back Office, termasuk laporan keuangan mendalam (Laba Rugi, Neraca, Buku Besar), inventaris, dan pengaturan sistem.
    *   Memiliki otorisasi khusus (seperti membatalkan sesi tutup kas yang keliru dan menyetujui mutasi luar biasa).

### 2. Back Office Admin (Finance, Accounting, Inventory & HR)
*   **Deskripsi**: Staf kantor pusat atau administrator yang mengelola operasional non-ritel, keuangan, dan kepegawaian.
*   **Hak Akses**: Akses penuh ke modul administrasi, akuntansi, inventaris, dan kepegawaian. Tidak memiliki otorisasi khusus Owner (seperti pembatalan tutup kas).
*   **Fitur Utama**:
    *   **Inventaris & Pembelian**: Menginput barang (tingkat harga dinamis dari master tier, bundling, jasa), mengelola Purchase Order (PO) & Delivery Order (DO), memproses mutasi stok, dan melakukan stok opname.
    *   **Akuntansi**: Menginput Jurnal Umum, memproses depresiasi/disposisi aset, mengelola COA, dan menyusun Laporan Keuangan per cabang.
    *   **Kepegawaian (HR)**: Memproses slip gaji bulanan (Payroll), menyetujui/menolak kasbon, menyetujui request kehadiran *backdate*, menyetel aturan denda (*penalty point*), dan memantau KPI karyawan.
    *   **CRM**: Mengatur integrasi WhatsApp Gateway dan sistem poin loyalty member.

### 3. Kasir / Front Desk
*   **Deskripsi**: Staf operasional garda depan yang bertugas melayani transaksi ritel dan layanan servis di toko fisik.
*   **Hak Akses**: Akses terbatas pada modul POS, manajemen servis dasar, dan sesi kasir harian cabang tempatnya bertugas.
*   **Fitur Utama**:
    *   **Point of Sale (POS)**: Melakukan transaksi penjualan ritel (pembayaran tunai, transfer, *mix payment*), memilih sales rep, menginput data pelanggan baru, dan mencetak struk.
    *   **Sesi Kasir**: Membuka dan menutup sesi kasir harian (mengisi kas awal dan kas akhir fisik).
    *   **Manajemen Servis**: Mencetak tanda terima servis masuk, memperbarui status pengerjaan servis, dan mengonversinya menjadi invoice kasir saat servis selesai.
    *   **Kehadiran**: Melihat indikator bar absensi pribadi (hari kehadiran dan sisa jatah *off day*).

### 4. Sales Representative (Staf Penjualan)
*   **Deskripsi**: Staf penjualan di toko yang bertugas melayani pelanggan secara langsung.
*   **Hak Akses**: Hanya dapat mengakses portal/dashboard sales pribadi dan tidak memiliki akses ke data keuangan atau sistem administrasi lainnya.
*   **Fitur Utama**:
    *   **Dashboard Sales**: Memantau target penjualan bulanan & harian, melacak komisi penjualan pribadi, melihat sisa target untuk membuka tier komisi berikutnya, dan melihat leaderboard internal.
    *   **Kepegawaian**: Melakukan check-in/absensi harian (termasuk absensi foto jika bertugas di luar toko), melihat sisa *off day*, dan mengajukan kasbon/cicilan kasbon.


# Sitemap

Berikut adalah struktur menu dan halaman utama (sitemap) untuk sistem ERP Diego Music Store, dikelompokkan secara terstruktur berdasarkan **Halaman** dan **Kategori** yang selaras 1:1 dengan dokumen kebutuhan bisnis:

## 1. Halaman: Front Desk
* **Kategori: POS System (Fitur Dasar & Fitur Utama)**
  * **Deskripsi**: Modul transaksi ritel langsung di kasir toko fisik, administrasi sesi, pencatatan piutang pelanggan, dan laporan harian POS.
  * **Sub-Halaman**:
    1. POS (Transaksi Penjualan Baru [Kasir bisa input penjualan berdasarkan nama sales, pelanggan, kategori penjualan, tier harga, metode pembayaran cash, transfer, mix payment, serta mencetak struk jual dan struk tagihan], Edit Penjualan, Retur Penjualan)
    2. Kas Harian (Buka Sesi Kasir, Tutup Kas/Sesi dengan penginputan kas fisik, & Otorisasi Admin/Owner untuk Cancel Tutup Kas)
    3. Pelunasan Piutang Pelanggan (Pencatatan transaksi pembayaran kredit/pelunasan hutang pelanggan)
    4. Input Data Dasar POS (Input Data Pelanggan, Input Data User, Input Satuan Barang, Input Kategori Penjualan)
    5. Laporan Harian Kasir (Laporan Penjualan, Laporan Piutang & Pelunasan Piutang, Laporan Kas Harian, Daftar Stok dan Harga)
    6. Info/Bar Absensi Kasir (Tampilan hari kehadiran & sisa off day pribadi yang berubah warna menjadi merah jika over)
    7. Utility Settings POS (Hak akses/Privilege User, Registrasi Nama Toko, Setting Struk dan Invoice, Cetak Barcode Label)
* **Kategori: CRM (Customer Relationship Management)**
  * **Deskripsi**: Modul pengelolaan loyalitas pelanggan dan notifikasi otomatis berbasis WhatsApp di area Front Office.
  * **Sub-Halaman**:
    1. Loyalty Member & Poin Pelanggan (Pelanggan otomatis terdaftar menjadi member dan mengumpulkan poin belanja)
    2. WhatsApp Broadcast & Reminder (Pengiriman pengingat tagihan piutang otomatis dan broadcast promo)
    3. WhatsApp Invoice (Mengirim struk kasir PDF secara otomatis melalui WhatsApp setelah transaksi berstatus Lunas)

## 2. Halaman: Back Office
* **Kategori: Multi Cabang & Manajemen Cabang**
  * **Deskripsi**: Pengelolaan wilayah operasional cabang, isolasi data stok, pelanggan, laporan laba rugi, serta penanganan servis instrumen.
  * **Sub-Halaman**:
    1. Registrasi Cabang Baru (Membuat cabang baru lengkap dengan hak akses setara cabang utama)
    2. Konfigurasi Hak Akses Cabang (Login berdasarkan lokasi cabang dan atur akses user untuk beberapa cabang)
    3. Manajemen Cabang Terkonsolidasi (Mengelola stok, pelanggan, dan laporan laba rugi per cabang dalam satu entitas bisnis)
    4. Offline Mode & Auto-sync Settings (Konfigurasi Service Worker untuk sinkronisasi POS pasca-offline)
    5. Manajemen Barang Service (Cetak tanda terima service, log pantau status proses service, dan konversi ke invoice POS saat selesai)
* **Kategori: Accounting & Inventory**
  * **Deskripsi**: Pengelolaan inventaris ritel, rantai pasok (purchasing/pembelian), jurnal akuntansi double-entry, pencatatan aset tetap, dan laporan keuangan komprehensif.
  * **Sub-Halaman**:
    1. Input Data Master Akuntansi (Klasifikasi Akun & Daftar Akun/COA, Satuan Produk, Input Lokasi Gudang, Master Data Barang & Varian [Input kode, nama, jenis produk, harga beli, HPP setelah ongkir, tingkat harga dinamis/tiering master, harga cabang, produk bundling, dan produk jasa dengan stok tidak terbatas], Supplier/Vendor, User Admin)
    2. Transaksi Keuangan (Jurnal Umum, Edit Jurnal, Pembelian & Retur Pembelian, Pelunasan Hutang Vendor, Stok Opname, Mutasi Barang antar cabang, Kas Harian [Petty Cash], Faktur Penjualan, Penawaran Harga)
    3. Laporan Keuangan (Balance Sheet/Neraca, Income Statement [Laporan laba rugi dengan rincian: Omset Penjualan, Laba Kotor, Biaya Operasional, Biaya Lain-lain, Laba Bersih], Buku Besar, Laporan Jurnal, Neraca Saldo, Buku Bank, Buku Vendor, & Sesi Tutup Buku Bulanan)
    4. Laporan Umum (Laporan Pembelian, Laporan Hutang & Pelunasan Hutang, Laporan Retur Pembelian, Laporan Kas)
    5. Laporan Stok (Daftar Stok, Stok Opname, Kartu Stok, Persediaan Akhir, Mutasi Barang)
    6. Manajemen Aset Tetap (Penyusutan/Depresiasi Aset & Penjualan/Disposisi Aset)
    7. Deposit Pelanggan (Downpayment untuk booking barang atau Purchase Order/PO)
    8. Retur Barang Sebagian (Retur sebagian barang tanpa mengembalikan seluruh isi invoice)
    9. Purchase Order (PO) & Delivery Order (DO) (Penguncian pesanan ke supplier dan penerimaan fisik barang ke gudang)
    10. Sinkronisasi Marketplace (Integrasi API untuk sinkronisasi stok dan penjualan ke pembukuan akuntansi)
    11. Utility Settings Back Office (Privilege User Admin, Registrasi Informasi Toko Utama, Backup & Restore Database)
* **Kategori: Payroll & Manajemen Karyawan**
  * **Deskripsi**: Sistem kepegawaian, pencatatan absensi terintegrasi, komisi staf penjualan, penilaian KPI, denda, dan penggajian otomatis bulanan.
  * **Sub-Halaman**:
    1. Kepegawaian & Absensi (Tarik data absensi fingerprint/face, check-in absensi foto dinas luar, & approval pengajuan kehadiran backdate)
    2. Pengajuan Kasbon (Approval kasbon/Cash Advance & log cicilan potong payroll otomatis)
    3. Manajemen Komisi Sales (Skema komisi flat/bertingkat, target/produk, kalkulasi otomatis, approval, & export rekap komisi)
    4. Template KPI Karyawan (Pencatatan KPI per user/jabatan berdasarkan target penjualan, ATV, tingkat absensi, ketepatan waktu datang yang terhubung dengan bonus)
    5. Penalty Points & Potongan Otomatis (Log otomatis denda telat/pulang cepat/kelebihan leave yang masuk potongan payroll)
    6. Payroll Management (Otomatisasi gaji, tunjangan, potongan, rekap gaji, cetak slip gaji PDF/Excel, rincian lembur/overtime approval, & export payroll bank)

## 3. Halaman: Dashboard (Visual & Analytics)
* **Kategori: Dashboard Owner**
  * **Deskripsi**: Pusat pemantauan performa bisnis secara keseluruhan, khusus diakses oleh Owner/Super Admin.
  * **Sub-Halaman**:
    1. Ringkasan Keuangan (Total penjualan, pengeluaran, hutang, saldo kas dan bank)
    2. Grafik Penjualan dan Pembelian (Grafik area perbandingan penjualan vs pembelian)
    3. Grafik Turn Over Stok (Kecepatan perputaran stok barang)
    4. Grafik Pareto 80/20 (Daftar produk dan pelanggan paling menguntungkan)
    5. Grafik Tren Penjualan Bulanan (Tren bisnis dan prediksi bulan berikutnya)
    6. Grafik Penjualan Per-Kategori (Kontribusi omzet dan profit per kategori barang/jasa)
    7. Grafik Penjualan Per-Cabang (Kontribusi omzet dan profit per cabang)
    8. Grafik Monthly Report (Grafik harian low dan peak pengunjung)
    9. Grafik Waktu Pengunjung (Traffic jam pengunjung per bulan / peak traffic hours)
    10. Grafik Performa Sales (Pencapaian target bulanan dan absensi sales)
    11. Filter Total Penjualan (Filter berdasarkan kategori dan seluruh penjualan)
* **Kategori: Dashboard Karyawan / Sales**
  * **Deskripsi**: Portal informasi performa penjualan individu untuk Sales Representative.
  * **Sub-Halaman**:
    1. Pantau Target Penjualan Bulanan (Target bulanan dan indikator pencapaian/progress bar)
    2. Pantau Target Penjualan Harian (Target harian berdasarkan breakdown target bulanan)
    3. Komisi Penjualan (Menampilkan nominal komisi yang didapat periode berjalan)
    4. Sisa Target Unlock Tier Komisi (Progress pencapaian menuju tier komisi berikutnya)
    5. Leaderboard (Papan peringkat Top 3 sales dalam satu bulan)
    6. Produk Fokus Bulan Ini (Daftar produk prioritas dan nominal insentif tambahan)
    7. Grafik Performa Sales (Tren omzet sales pribadi dalam 1 tahun)
    8. Info/Bar Absensi Karyawan (Jumlah kehadiran dan sisa jatah off day)


# Feature Inventory

Berikut adalah daftar inventaris fitur sistem ERP Diego Music Store yang sangat detail untuk setiap halaman dan kategori menu:

## 1. Halaman: Front Desk

### Kategori: POS System (Fitur Dasar & Fitur Utama)

#### **POS - Transaksi Penjualan Baru**
*   Pencarian Produk & Jasa (Barcode Scanner)
*   Pencarian Produk & Jasa (Ketik Nama/SKU)
*   Katalog Grid Produk & Jasa (Visual Card)
*   Filter Kategori Produk/Jasa POS
*   Tambah Item ke Keranjang Belanja
*   Hapus Item dari Keranjang Belanja
*   Edit Kuantitas Item (+ / - / Input Manual)
*   Pilihan Satuan Jual Produk (Dropdown)
*   Pemilihan Tier Harga Jual per Item (Dinamis berdasarkan Master Tier Harga Terdaftar)
*   Pemilihan Tier Harga Jual secara Global untuk Seluruh Keranjang Belanja (Dinamis berdasarkan Master Tier Harga Terdaftar)
*   Diskon per Item (Nominal atau Persentase)
*   Diskon Transaksi/Grand Total (Nominal atau Persentase)
*   Input PPN Transaksi (Manual, mendeteksi input persentase seperti '11%' maupun nominal langsung seperti '297000')
*   Atribusi Staf Sales Representative (Dropdown)
*   Pencarian Member/Pelanggan Terdaftar (Nama/No. HP)
*   Form Popup Registrasi Member Baru
*   Widget Informasi Poin Member Aktif
*   Pilihan Metode Pembayaran via Dropdown (Cash, Transfer, QRIS, Debit, Kredit)
*   Metode Pembayaran Gabungan / Mix Payment (Multi-select via Dropdown, misal: Cash & Transfer, QRIS & Debit, dll.)
*   Pilihan Mesin EDC Dinamis (Untuk tipe pembayaran Debit / Kredit)
*   Pilihan Bank Tujuan Penerima (Untuk tipe pembayaran QRIS / Transfer)
*   Input 4 Digit Terakhir Nomor Kartu (Untuk tipe pembayaran Debit / Kredit)
*   Input ID Referensi / Trace No (Untuk Debit, Kredit, QRIS, Transfer)
*   Input Nominal Pembayaran per Metode Pembayaran Terpilih (untuk Mix Payment)
*   Input Pembayaran Uang Pas / Hitung Kembalian otomatis
*   Input Catatan Transaksi / Note Kasir (Textarea untuk keterangan tambahan dari kasir)
*   Cetak Struk Penjualan Thermal (Lunas)
*   Cetak Struk Tagihan Piutang Thermal (Kredit/Piutang)
*   Pengiriman Struk Invoice PDF via WhatsApp (Otomatis)
*   Tombol Hold/Simpan Transaksi Sementara
*   Pemulihan Transaksi yang di-Hold (Recall Transaction)

#### **Edit Penjualan**
*   Pencarian Invoice Penjualan (Search Bar)
*   Filter Tanggal Transaksi
*   Pagination Daftar Histori Transaksi Penjualan
*   Detail Rincian Invoice POS (Popup/Detail View)
*   Form Edit Item Penjualan (Ubah Item/Diskon/Qty)
*   Validasi Otorisasi Admin/Supervisor (Input PIN/Password)
*   Log Audit Riwayat Perubahan Transaksi Penjualan

#### **Kas Harian (Sesi Laci Kasir)**
*   Form Buka Sesi (Input Nominal Uang Kas Awal Laci)
*   Tracker Sesi Kasir Aktif (Kasir, Waktu Buka, Waktu Tutup)
*   Tampilan Real-Time Estimasi Kas Laci (Berdasarkan transaksi kasir)
*   Form Tutup Sesi (Input Nominal Uang Kas Fisik Akhir Laci)
*   Kalkulasi Otomatis Selisih Kas (Surplus/Defisit)
*   Log Catatan Selisih Tutup Kas
*   Cetak Slip Sesi Tutup Kas (Thermal)
*   Otorisasi Owner untuk Batal Tutup Kas (Cancel Tutup Kas via PIN/Password)

#### **Pelunasan Piutang Pelanggan**
*   Pencarian Piutang Member (No. Invoice/Nama)
*   Filter Tanggal Jatuh Tempo Piutang
*   Pagination Daftar Tagihan Piutang Aktif
*   Tampilan Detail Saldo Piutang Member (Sisa Tagihan)
*   Input Nominal Pembayaran Pelunasan Piutang
*   Metode Pelunasan Piutang (Cash/Transfer/Potong Saldo Deposit)
*   Cetak Struk Bukti Pelunasan Piutang (Thermal)
*   Log Histori Pembayaran Piutang Pelanggan

#### **Input Data Dasar POS**
*   Tambah Data Pelanggan Baru
*   Edit Data Pelanggan
*   Hapus Data Pelanggan
*   Pencarian Data Pelanggan
*   Pagination Daftar Pelanggan
*   Tambah Data User Kasir Baru
*   Edit Hak Akses User Kasir
*   Hapus Data User Kasir
*   Tambah Unit Satuan Barang (pcs, pack, unit)
*   Edit Unit Satuan Barang
*   Hapus Unit Satuan Barang
*   Tambah Kategori Penjualan (Ritel, Event, Grosir)
*   Edit Kategori Penjualan
*   Hapus Kategori Penjualan

#### **Laporan Harian POS**
*   Laporan Rekap Penjualan Harian (Kasir)
*   Laporan Piutang Harian
*   Laporan Pelunasan Piutang Harian
*   Laporan Mutasi Kas Laci (Kas Harian)
*   Pencarian Stok & Varian POS
*   Cek Tingkat Harga Barang POS (Dinamis berdasarkan Master Tier Harga Terdaftar)
*   Filter Laporan per Sesi Kasir
*   Export Laporan POS ke PDF/Excel

#### **Info/Bar Absensi Kasir**
*   Tampilan Jumlah Hari Kehadiran Kasir (Bulan Berjalan)
*   Tampilan Sisa Jatah Off Day Kasir
*   Indikator Warna Batas Off Day (Hijau ke Merah jika Over Limit)
*   Widget Status Kehadiran Shift Kasir

#### **Utility Settings POS**
*   Pengaturan Privilege/Wewenang User Kasir
*   Form Registrasi Nama & Cabang Toko
*   Form Setting Header/Footer Struk
*   Pengaturan Logo Struk Thermal
*   Utilitas Cetak Barcode Label Barang (Stiker)

#### **Retur Penjualan**
*   Pencarian Invoice Asal Transaksi POS (Search Invoice)
*   Pilihan Item & Qty Barang yang Diretur
*   Input Alasan Retur Barang
*   Pilihan Metode Refund (Cash/Transfer/Voucher Kredit/Deposit)
*   Cetak Struk Bukti Retur Penjualan (Thermal)
*   Jurnal Otomatis Penyesuaian Retur Penjualan
*   Update Otomatis Stok Cabang atas Barang Retur

### Kategori: CRM (Customer Relationship Management)

#### **Loyalty Member & Poin**
*   Aturan Konversi Poin Belanja (Rasio Rupiah ke Poin)
*   Akumulasi Poin Otomatis dari POS
*   Form Penukaran Poin (Redeem Point)
*   Riwayat Log Keluar-Masuk Poin Member
*   Import Data Member Loyalty via Excel
*   Export Data Member Loyalty via Excel
*   Search & Filter Member Loyalty
*   Pagination Daftar Member Loyalty

#### **WhatsApp Broadcast & Reminder**
*   Template Pesan WhatsApp Penagihan Piutang
*   Pengiriman Tagihan Piutang Otomatis (Saat Jatuh Tempo)
*   Broadcast Promo Massal ke Member
*   Filter Target Penerima Broadcast (Berdasarkan Kategori Member)
*   Log Status Pengiriman WhatsApp (Terkirim/Gagal)
*   Pagination Log Status WhatsApp

#### **WhatsApp Invoice**
*   Konversi Invoice POS ke File PDF
*   Kirim PDF Invoice Otomatis via WA
*   Kirim Manual Ulang Invoice PDF via WA

---

## 2. Halaman: Back Office

### Kategori: Multi Cabang & Manajemen Cabang

#### **Multi-Cabang & Lokasi**
*   Registrasi Cabang Baru
*   Edit Data Cabang
*   Hapus Data Cabang
*   Konfigurasi Gudang Terisolasi per Cabang
*   Atur Akses User untuk Beberapa Cabang
*   Pembatasan Login User Berdasarkan Lokasi Cabang Aktif
*   Laba Rugi per Cabang
*   Konsolidasi Laba Rugi Seluruh Cabang

#### **Offline Mode & Auto-sync**
*   Konfigurasi Parameter Service Worker Offline
*   Deteksi Status Koneksi Internet Real-Time
*   Penyimpanan Antrean Transaksi Offline (IndexedDB)
*   Sinkronisasi Otomatis Data Penjualan saat Online berbasis Antrean FIFO (First-In, First-Out) untuk Menjaga Urutan Transaksi
*   Pencegahan Duplikasi Data Transaksi pasca-Sinkronisasi
*   Log Status Sinkronisasi Data Offline-Online

#### **Manajemen Barang Service**
*   Cetak Tanda Terima Servis Baru (PDF/Struk)
*   Pendaftaran Detail Kerusakan & Kelengkapan Barang
*   Penugasan Teknisi Servis
*   Estimasi Tanggal Selesai & Estimasi Biaya
*   Log Update Status Proses Servis (Menerima -> Pengerjaan -> Selesai)
*   Input Penggunaan Suku Cadang & Biaya Jasa Servis
*   Tombol Kirim Konversi ke Invoice POS Kasir
*   Riwayat Histori Servis Pelanggan
*   Search & Filter Data Servis
*   Pagination Daftar Servis

### Kategori: Accounting & Inventory

#### **Master Data Barang & Varian**
*   Tambah Master Barang Baru
*   Edit Master Barang
*   Hapus Master Barang
*   Pemilihan Tipe Produk (Fisik / Bundling / Jasa)
*   Input Kode SKU & Barcode Utama
*   Input Harga Beli Dasar Supplier
*   Input Estimasi Ongkos Kirim per Unit
*   Kalkulasi Otomatis HPP Awal
*   Manajemen Master Tier Harga (Tambah/Edit/Hapus Konfigurasi Tingkat Harga Ritel secara Dinamis)
*   Input Tingkat Harga Jual Ritel Dinamis (Berdasarkan Master Tier Harga Terdaftar)
*   Input Harga Jual Khusus Cabang
*   Tambah Varian Barang (Warna, Ukuran)
*   Setup Produk Bundling (Paket Barang)
*   Import Master Barang via Excel
*   Export Master Barang via Excel
*   Upload Gambar Produk
*   Search & Filter Master Barang
*   Pagination Daftar Barang

#### **Transaksi Keuangan & Jurnal**
*   Input Entri Jurnal Umum Manual
*   Edit Jurnal Umum
*   Hapus Jurnal Umum
*   Tabel Chart of Accounts (COA) / Daftar Akun
*   Klasifikasi Kelompok Akun Keuangan
*   Pencatatan Jurnal Otomatis Penjualan POS
*   Pencatatan Jurnal Otomatis Pembelian Supplier
*   Pencatatan Jurnal Otomatis Beban Payroll
*   Pencatatan Jurnal Otomatis Depresiasi Aset
*   Faktur Penjualan Nontunai (Commercial Invoice)
*   Penawaran Harga (Quotation)
*   Search & Filter Jurnal Keuangan
*   Pagination Daftar Jurnal

#### **Pembelian & Rantai Pasok (Procurement)**
*   Pembuatan Purchase Order (PO) ke Supplier
*   Penguncian Dokumen PO
*   Pembuatan Delivery Order (DO) / Penerimaan Gudang
*   Verifikasi Jumlah Fisik Barang Diterima vs PO
*   Pencatatan Retur Pembelian ke Vendor
*   Manajemen Hutang Vendor & Pelunasan Hutang
*   Log Histori Pembelian Supplier
*   Search & Filter PO/DO
*   Pagination Daftar PO/DO

#### **Deposit Pelanggan (Downpayment)**
*   Pencatatan Uang Muka (DP) Booking Barang / PO
*   Integrasi Potong Saldo DP saat Transaksi POS Lunas
*   Pengembalian Uang Muka (Refund DP)
*   Log Histori Deposit Pelanggan

#### **Retur Barang Sebagian**
*   Retur Parsial Item dari Invoice POS Lunas
*   Hitung Otomatis Nilai Refund Barang Retur
*   Update Otomatis Stok Cabang atas Barang Retur
*   Pembuatan Voucher Credit Note / Deposit untuk Refund

#### **Stok Opname & Mutasi Barang**
*   Input Form Stok Opname (Fisik vs Sistem)
*   Hitung Selisih Nilai Stok Opname
*   Jurnal Penyesuaian Selisih Stok Opname Otomatis
*   Pembuatan Mutasi Barang Antar-Cabang (Kirim)
*   Verifikasi Penerimaan Mutasi Barang Cabang Tujuan (Terima)
*   Tracker Posisi Barang Mutasi (In-Transit)
*   Cetak Kartu Stok per Barang & Gudang Cabang

#### **Manajemen Aset Tetap**
*   Pencatatan Aset Tetap Baru
*   Pengaturan Umur Ekonomis & Nilai Sisa Aset
*   Hitung Penyusutan/Depresiasi Periodik (Straight Line)
*   Jurnal Depresiasi Aset Otomatis
*   Disposisi Aset (Penjualan / Penghapusan Aset)
*   Jurnal Laba/Rugi Disposisi Aset

#### **Sinkronisasi Marketplace**
*   Integrasi API E-commerce (Tokopedia, Shopee)
*   Sinkronisasi Stok Barang Real-Time ke Marketplace
*   Penarikan Otomatis Data Penjualan Marketplace ke Pembukuan
*   Pemetaan Kategori Produk Marketplace ke COA

#### **Laporan & Tutup Buku Bulanan**
*   Laporan Balance Sheet (Neraca)
*   Laporan Neraca Saldo (Trial Balance)
*   Laporan Buku Besar per Akun (General Ledger)
*   Laporan Jurnal Umum
*   Laporan Buku Bank (Rekonsiliasi Bank)
*   Laporan Buku Pembantu Hutang Vendor
*   Laporan Buku Pembantu Piutang Pelanggan
*   Laporan Laba Rugi (Income Statement) per Cabang & Konsolidasi
*   Sesi Tutup Buku Bulanan (Mengunci Transaksi)
*   Backup Database Sistem
*   Restore Database Sistem

#### **Transaksi Kas Harian (Petty Cash)**
*   Pencatatan Pengeluaran Operasional Kantor/Cabang (Petty Cash)
*   Pencatatan Pemasukan Kas Non-penjualan
*   Log Rekap Kas Harian Back Office
*   Otorisasi Petty Cash Cabang

#### **Kalkulasi HPP Otomatis**
*   Hitung Nilai HPP Rata-rata Terbobot (Weighted Average)
*   Atribusi Ongkos Kirim Pembelian Supplier ke Nilai HPP

### Kategori: Payroll & Manajemen Karyawan

#### **Kepegawaian & Absensi**
*   Penarikan Data Absensi fingerprint/face lock
*   Check-In/Check-Out Absensi Foto Dinas Luar
*   Deteksi Geotagging/Lokasi Absensi Foto
*   Formulir Pengajuan Kehadiran Backdate
*   Approval Pengajuan Kehadiran Backdate oleh Admin/Owner
*   Papan Kalender Kehadiran Karyawan
*   Search & Filter Data Absensi
*   Pagination Daftar Absensi

#### **Pengajuan Kasbon (Cash Advance)**
*   Formulir Pengajuan Kasbon Karyawan
*   Pilihan Termin / Jumlah Bulan Cicilan Kasbon
*   Approval Pengajuan Kasbon oleh Admin/Owner
*   Integrasi Pemotongan Otomatis Cicilan Kasbon di Payroll
*   Kartu Kendali Sisa Saldo Kasbon Karyawan

#### **Manajemen Komisi Sales**
*   Konfigurasi Skema Komisi Sales (Flat / Bertingkat)
*   Penetapan Target Penjualan Bulanan Sales
*   Kalkulasi Otomatis Komisi Sales Berdasarkan POS Lunas
*   Approval Rekap Komisi Sales
*   Export Rekap Komisi Sales ke Excel

#### **KPI & Penilaian Kinerja**
*   Pembuatan Template KPI per Jabatan
*   Input Skor Kinerja Karyawan (Target Omset, ATV, Absensi, Ketepatan Waktu)
*   Kalkulasi Insentif / Bonus Kinerja Berdasarkan KPI
*   Laporan Rapor KPI Karyawan

#### **Penalty Points & Potongan Gaji**
*   Aturan Denda Pelanggaran Terlambat / Pulang Cepat
*   Aturan Denda Kelebihan Cuti / Leave
*   Log Pelanggaran Karyawan Otomatis
*   Kalkulasi Potongan Gaji Otomatis di Payroll

#### **Payroll & Slip Gaji**
*   Generate Gaji Bulanan Otomatis
*   Input Tunjangan Manual / Khusus
*   Hitung Upah Lembur (Overtime) Berdasarkan Approval Jam Kerja
*   Export Rekap Payroll Bank (File Transfer Massal)
*   Cetak Slip Gaji Detail PDF / Excel
*   Kirim Slip Gaji PDF Otomatis ke WhatsApp Karyawan

---

## 3. Halaman: Dashboard (Visual & Analytics)

### Kategori: Dashboard Owner
*   Widget Total Omset Penjualan Global
*   Widget Total Pengeluaran Kas Operasional
*   Widget Total Saldo Kas & Bank Aktif
*   Widget Total Piutang Pelanggan
*   Widget Total Hutang Supplier
*   Grafik Area Penjualan vs Pembelian Bulanan
*   Grafik Kecepatan Perputaran Stok (Turn Over)
*   Diagram Pareto 80/20 Produk Paling Menguntungkan
*   Diagram Pareto 80/20 Pelanggan Paling Menguntungkan
*   Grafik Tren Penjualan Bulanan & Prediksi Periode Berikutnya
*   Grafik Donat Kontribusi Omzet & Profit per Kategori
*   Grafik Donat Kontribusi Omzet & Profit per Cabang
*   Grafik Monthly Report Kunjungan Pengunjung Harian (Low/Peak)
*   Grafik Batang Waktu Terpadat Pengunjung (Peak Hours)
*   Grafik Performa Sales Representative (Target vs Realisasi)
*   Filter Pencarian Global Dashboard (Tanggal & Cabang)

### Kategori: Dashboard Karyawan / Sales
*   Progress Bar Target Penjualan Bulanan Pribadi
*   Tracker Target Penjualan Harian (Rekomendasi Breakdown)
*   Nominal Komisi Akumulasi Bulan Berjalan
*   Indikator Sisa Target untuk Unlock Tier Komisi Berikutnya
*   Leaderboard Internal (Papan Peringkat Top 3 Sales)
*   Daftar Produk Fokus Bulan Ini & Insentif Tambahan
*   Grafik Garis Tren Omzet Sales Pribadi 1 Tahun
*   Widget Absensi Pribadi (Kehadiran & Sisa Off Day)


# Detail Modul

Bagian ini mendetailkan spesifikasi teknis fungsional, alur kerja utama, komponen antarmuka (UI/UX), serta simulasi kasus nyata (*real-world case*) untuk modul-modul kritis dalam sistem ERP Diego Music Store.

---

## 1. Modul POS (Point of Sale) & Kasir

### A. Fitur: Transaksi Penjualan Baru (POS)
*   **Fokus Kerja**: Memproses transaksi ritel langsung di kasir fisik dengan dukungan harga bertingkat dinamis, multi-metode pembayaran (*mix payment*), atribusi sales rep, pencatatan PPN, cetak struk thermal, dan pengiriman otomatis struk PDF ke nomor WhatsApp pelanggan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Inisialisasi**: Kasir masuk ke halaman POS (sistem memastikan sesi kas harian/kasir aktif).
    2. **Pemindaian Barang**: Kasir memindai barcode produk atau mengetik manual SKU/Nama barang. Sistem menarik harga beli dan harga jual dari database.
    3. **Penerapan Tier Harga**: Secara default, sistem menampilkan harga ritel umum (Tier Default). Jika pelanggan didaftarkan/dicari sebagai member aktif, sistem otomatis mendeteksi tier harga member tersebut (misal: Tier 3/Gold) dan mengubah seluruh baris belanjaan di keranjang ke tier tersebut. Kasir juga dapat memilih tier harga per item secara manual jika diperlukan.
    4. **Atribusi Penjualan**: Kasir memilih Sales Representative yang melayani untuk perhitungan komisi.
    5. **Pembayaran & Catatan**: Kasir menyeleksi metode pembayaran pada dropdown multi-select, menginput **PPN Transaksi** secara manual (sistem mendeteksi apakah diinput sebagai persentase % atau nominal angka langsung), serta menginput **Catatan Transaksi / Note** jika terdapat detail tambahan. Jika memilih metode non-cash (Debit, Kredit, QRIS, Transfer), kasir memilih bank/EDC yang dituju dan menginput ID referensi/nomor kartu sesuai kebutuhan. Jika memilih lebih dari satu metode (Mix Payment), kasir memasukkan nominal pembayaran masing-masing metode. Sistem melakukan validasi apakah total nominal pembayaran $\ge$ Grand Total transaksi.
    6. **Finishing**: Setelah klik bayar, sistem akan:
       - Mengurangi stok barang fisik di gudang cabang aktif.
       - Menyimpan transaksi ke tabel penjualan dengan status *Lunas*.
       - Membuat entri jurnal umum double-entry secara otomatis.
       - Mengirim print job ke printer struk thermal.
       - Memicu WhatsApp Gateway untuk mengirim invoice PDF.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Dropdown Multi-select Metode Pembayaran**: Dropdown pilihan metode pembayaran yang mendukung pencentangan ganda (multi-select) untuk mengaktifkan mode *Mix Payment* (opsi: Cash, Transfer, QRIS, Debit, Kredit).
    *   **Sub-Form Informasi Pembayaran Non-Cash**: Input dinamis yang muncul di bawah metode pembayaran non-cash terpilih:
        - Dropdown Bank Penerima / EDC (BCA, Mandiri, BRI, dll.).
        - Input 4 Digit Terakhir Nomor Kartu (khusus Debit/Kredit).
        - Input ID Referensi Transaksi / Trace No.
    *   **Kolom Catatan Transaksi (Note)**: Textarea di bagian bawah panel transaksi untuk memasukkan note khusus dari kasir.
    *   **Keranjang Belanja**: Tabel responsif dengan kolom: No, SKU/Barcode, Produk, Satuan, Qty, Tier Harga (Dropdown Tier), Diskon, Subtotal, Aksi (Hapus).
    *   **Panel Total (Kanan)**: Widget teks font besar tebal menampilkan Grand Total, input PPN transaksi fleksibel (menerima input teks persen atau nominal angka langsung), serta input nominal pembayaran per jenis metode yang aktif.
*   **Studi Kasus Nyata (Real Case Scenario - Transaksi Mix Payment & Tiering)**:
    *   **Profil Produk**:
        *   Nama Produk: Gitar Akustik *Fender CD-60S Black* (SKU: `FND-CD60S-BLK`)
        *   Master Tier Harga Terdaftar:
            *   Tier 1 (Ritel Biasa/Umum): Rp3.000.000
            *   Tier 2 (Member Silver): Rp2.850.000
            *   Tier 3 (Member Gold): Rp2.700.000
    *   **Profil Pelanggan**:
        *   Nama Pelanggan: Budi Santoso (Member Gold terdaftar, No. HP: `08123456789`)
    *   **Alur Input di POS**:
        1. Kasir mencari member Budi Santoso -> Sistem menampilkan profil Budi dengan label `Gold Member`.
        2. Kasir memindai barcode Fender CD-60S Black -> Produk masuk ke keranjang belanja.
        3. Karena Budi adalah Gold Member, sistem secara otomatis mengubah dropdown **Tier Harga** produk tersebut ke **Tier 3 (Gold)**, sehingga harga satuan yang semula Rp3.000.000 berubah menjadi **Rp2.700.000**.
        4. Kasir memasukkan PPN secara manual dengan mengetik "11%" (sistem otomatis menghitung 11% dari subtotal Rp2.700.000 = **Rp297.000**).
        5. Grand Total yang harus dibayar = Rp2.700.000 + Rp297.000 = **Rp2.997.000**.
        6. Budi ingin membayar dengan cara gabungan (*Mix Payment*):
           - Kasir memilih opsi `Cash` dan `QRIS` pada dropdown multi-select metode pembayaran.
           - Muncul sub-form input nominal dan data bank penerima untuk QRIS.
           - Kasir memasukkan nominal Cash: **Rp997.000** (Budi menyerahkan uang tunai pecahan Rp100.000 sebanyak 10 lembar, kasir memasukkan bayar Rp1.000.000 dan sistem menghitung kembalian Rp3.000).
           - Kasir memasukkan nominal QRIS: **Rp2.000.000**, memilih Bank Tujuan: **BCA**, dan menginput ID Referensi: **`TRX-998877`**.
           - Kasir memasukkan Catatan Transaksi (Note): **"Paket senar cadangan gratis d'addario dimasukkan ke dalam softcase"**.
        7. Kasir menekan tombol "Bayar & Cetak".
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Sisa Stok *Fender CD-60S Black* di Cabang aktif berkurang sebanyak 1 unit.
        - Saldo Poin Budi bertambah 200 poin (dihitung khusus dari nominal non-tunai QRIS Rp2.000.000 dengan rasio Rp10.000 = 1 poin).
        - WhatsApp otomatis mengirim PDF struk ke Budi Santoso (`08123456789`).
        - **Jurnal Umum Otomatis (Double-Entry Bookkeeping)**:
          *(Asumsi HPP gitar tersebut saat itu di database adalah Rp2.100.000)*
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-01 | Kas Laci POS (Cash) | 997.000 | |
          | 1112-05 | Kas Bank QRIS (EDC/E-Wallet) | 2.000.000 | |
          | 4111-01 | Pendapatan Penjualan Ritel | | 2.700.000 |
          | 2115-01 | Hutang Pajak Keluaran (PPN 11%) | | 297.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP) | 2.100.000 | |
                    | 1131-01 | Persediaan Barang Dagang | | 2.100.000 |

---

### B. Fitur: Sesi Tutup Kas / Tutup Sesi Shift Kasir (POS)
*   **Fokus Kerja**: Mengamankan pencatatan aliran uang tunai harian di toko fisik dengan mencocokkan nominal fisik kas laci vs kalkulasi teoritis sistem, melaporkan selisih kurang/lebih kas (*cash short/over*), dan menghentikan input sesi kasir secara otomatis untuk mencegah fraud.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Inisiasi Sesi**: Di awal shift, kasir memasukkan nominal modal awal kas (*opening cash*, misal Rp500.000) dan sistem membuka sesi laci kasir.
    2. **Pencatatan Berjalan**: Seluruh penjualan tunai, non-tunai, dan pengeluaran kas operasional kecil (*petty cash*) terikat pada sesi kasir aktif tersebut.
    3. **Penutupan Sesi (Reconciliation)**: Di akhir shift, kasir memicu menu "Tutup Kas". Sistem menyembunyikan nominal penjualan teoritis agar kasir melakukan penghitungan fisik secara buta (*blind counting*).
    4. **Input Fisik**: Kasir menghitung dan menginput lembaran uang tunai riil di dalam laci.
    5. **Kalkulasi Selisih**: Sistem membandingkan total fisik riil dengan kalkulasi sistem:
       $$\text{Kas Teoritis} = \text{Kas Awal} + \text{Penjualan Tunai} - \text{Pengeluaran Petty Cash}$$
       Jika terdapat selisih, sistem mencatat status *Selisih Kurang (Shortage)* atau *Selisih Lebih (Overage)*.
    6. **Locking & Posting**: Sistem mencetak struk *Z-Report* ringkasan shift, mengunci transaksi sesi tersebut, dan memposting jurnal penyesuaian selisih kas secara otomatis. Sesi kasir berstatus *Closed*.
    7. **Otorisasi Khusus**: Jika kasir salah input uang fisik, penyesuaian hanya bisa dilakukan melalui menu *Cancel Tutup Kas* yang membutuhkan otorisasi supervisor/Owner (memasukkan PIN Owner).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Input Uang Fisik (Blind Count)**: Input dinamis pecahan uang rupiah (Lembar Rp100k, Rp50k, Rp20k, Rp10k, Rp5k, Rp2k, Rp1k, dan koin). Sistem menjumlahkan total kas fisik secara otomatis dari perkalian jumlah lembar yang diinput.
    *   **Voucher Petty Cash POS**: Tombol popup untuk mencatat pengeluaran darurat tokom (misal: beli lakban/sapu) dengan kolom: Nominal Pengeluaran, Keperluan/Deskripsi, dan Upload Foto Nota.
    *   **Status Panel**: Menampilkan status sesi (`Open` / `Closed`), nama kasir aktif, jam buka, dan jam tutup.
*   **Studi Kasus Nyata (Real Case Scenario - Penutupan Shift Kasir & Selisih Kurang)**:
    *   **Profil Kasir & Sesi**:
        *   Nama Kasir: Maya Anggraini (Kasir Shift Pagi, Cabang Depok)
        *   Modal Kas Awal (*Opening Cash*): **Rp500.000**
    *   **Aktivitas Transaksi Selama Shift**:
        *   Total Penjualan Ritel Tunai (Cash): Rp3.200.000
        *   Total Penjualan Ritel QRIS: Rp1.500.000
        *   Pengeluaran Petty Cash (Darurat): Maya membeli tisu toilet & sapu sebesar **Rp50.000** (Maya menginput voucher Petty Cash di POS dan mengunggah foto nota).
    *   **Alur Rekonsiliasi Tutup Kas**:
        1. Di akhir shift, Maya menekan tombol "Tutup Kas & Tutup Sesi".
        2. Maya menghitung uang fisik di laci kasir dan menginput jumlah lembarnya:
           - Rp100.000 = 30 lembar (Rp3.000.000)
           - Rp50.000 = 12 lembar (Rp600.000)
           - Rp20.000 = 2 lembar (Rp40.000)
           - Koin pecahan = Rp5.000
           - Total Kas Fisik Riil Terhitung = **Rp3.645.000**.
        3. Sistem melakukan kalkulasi internal teoritis:
           $$\text{Kas Laci Teoritis} = \text{Kas Awal (Rp500.000)} + \text{Penjualan Tunai (Rp3.200.000)} - \text{Petty Cash (Rp50.000)} = \mathbf{Rp3.650.000}$$
        4. Sistem mendeteksi selisih:
           $$\text{Selisih Kas} = \text{Kas Fisik Rill (Rp3.645.000)} - \text{Kas Laci Teoritis (Rp3.650.000)} = \mathbf{-Rp5.000} \text{ (Selisih Kurang)}$$
        5. Maya menekan tombol "Konfirmasi & Tutup Sesi". Sistem mencetak *Z-Report* dan mengunci POS.
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Sesi ditutup dengan catatan denda selisih kurang Rp5.000 yang akan dibebankan ke penanggung jawab kasir (atau diakui sebagai beban selisih).
        - **Jurnal Umum Akuntansi (Pengakuan Beban Operasional Petty Cash)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 5311-05 | Beban Perlengkapan & Kebersihan Toko | 50.000 | |
          | 1111-01 | Kas Laci POS (Depok) | | 50.000 |

        - **Jurnal Umum Akuntansi (Pengakuan Selisih Kurang Kas)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 5999-01 | Beban Selisih Kas (Kerugian) | 5.000 | |
          | 1111-01 | Kas Laci POS (Depok) | | 5.000 |

        - **Jurnal Umum Akuntansi (Setoran Kas Fisik Laci ke Brankas Utama Cabang)**:
          *(Mentransfer saldo sisa fisik riil Rp3.645.000 dari laci ke brankas aman)*
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-02 | Kas Brankas Utama (Cabang Depok) | 3.645.000 | |
          | 1111-01 | Kas Laci POS (Depok) | | 3.645.000 |

---

### C. Fitur: Manajemen Barang Service Musik
*   **Fokus Kerja**: Melacak siklus hidup perbaikan instrumen musik pelanggan (mulai dari registrasi tanda terima masuk, pelacakan proses teknisi, pemakaian sparepart gudang, hingga penagihan dan konversi otomatis menjadi invoice POS saat serah terima unit).
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Registrasi Servis**: Kasir menginput detail unit masuk (merek, tipe, serial number, deskripsi kerusakan, kelengkapan aksesoris bawaan, dan estimasi selesai). Sistem menerbitkan dokumen *Tanda Terima Servis* dengan QR-Code pelacakan.
    2. **Penugasan**: Sistem menugaskan unit servis ke teknisi internal terdaftar. Status berubah menjadi *In-Service*.
    3. **Diagnosa & Pemakaian Sparepart**: Teknisi mendiagnosis kerusakan. Jika membutuhkan penggantian sparepart, teknisi meminta persetujuan pelanggan via WhatsApp Link, lalu mengambil suku cadang dari stok gudang cabang. Sistem mencatat HPP sparepart tersebut.
    4. **Finishing**: Teknisi menyelesaikan perbaikan, menulis laporan tindakan servis, dan mengubah status ke *Siap Diambil (Ready for Pickup)*. WhatsApp otomatis memberi notifikasi ke pelanggan.
    5. **Konversi ke POS**: Saat pelanggan datang mengambil unit, kasir memindai QR-Code tanda terima servis. Sistem menarik data jasa servis + sparepart yang digunakan, mengkonversinya langsung menjadi draf invoice di POS, memotong stok barang fisik di database gudang, dan memproses pembayaran.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Registrasi Servis Baru**: Input nama pelanggan (search member), merek & tipe instrumen, nomor seri, checklist kelengkapan (Tas/Hardcase, Adaptor, Strap, Dus), dan textarea keluhan kerusakan.
    *   **Kanban Board Pelacakan Teknisi**: Kolom visual perpindahan status: `[ Antrean ]` $\rightarrow$ `[ Sedang Dikerjakan ]` $\rightarrow$ `[ Menunggu Part ]` $\rightarrow$ `[ Siap Diambil ]` $\rightarrow$ `[ Sudah Diserahkan (Closed) ]`.
*   **Studi Kasus Nyata (Real Case Scenario - Alur Servis Masuk Hingga Pembayaran POS)**:
    *   **Penerimaan Barang (Registrasi Servis)**:
        *   Nama Pelanggan: Rian Hidayat (No. HP: `087766554433`)
        *   Unit Masuk: Gitar Elektrik *Gibson Les Paul Standard Cherry Sunburst* (No. Seri: `GBS-LP-7788`)
        *   Keluhan Kerusakan: Kelistrikan mati total (suara tidak keluar di ampli), fretboard kotor & buzz parah di fret 3-5.
        *   Kelengkapan: Hardcase Gibson Coklat Original & Strap Kulit Hitam.
        *   Kasir mencetak Tanda Terima Servis ber-QR Code dengan status *Antrean*.
    *   **Pengerjaan & Pemakaian Suku Cadang (Teknisi)**:
        *   Teknisi yang ditugaskan: Joko (Teknisi Senior)
        *   Diagnosa: Potensio volume aus dan berkarat (perlu ganti 2 unit). Fretboard perlu dibersihkan & re-setup action senar.
        *   Sparepart dari Stok Gudang Cabang:
            - CTS Potensiometer 500k Short Shaft (SKU: `CTS-POT-500K`) = **2 unit**
            - HPP Dasar Sparepart di Gudang: Rp80.000 per unit (Total HPP = Rp160.000).
            - Harga Jual Ritel ke Pelanggan: Rp120.000 per unit (Total Harga Sparepart = Rp240.000).
        *   Tarif Jasa Servis (Setup & Kelistrikan): **Rp250.000** (Kategori Jasa - tanpa memotong stok/HPP).
        *   Joko mengubah status servis di sistem ke **Siap Diambil**. WhatsApp Gateway otomatis mengirim rincian tagihan estimasi Rp490.000 ke Rian Hidayat.
    *   **Penyerahan Unit & Pembayaran di POS**:
        1. Rian datang menyerahkan tanda terima servis. Kasir memindai QR Code struk tersebut.
        2. POS memunculkan invoice penjualan draf yang otomatis terisi:
           - Jasa Servis Kelistrikan & Setup Gitar = Rp250.000
           - 2x CTS Potensiometer 500k = Rp240.000
           - Grand Total Tagihan = **Rp490.000**.
        3. Rian membayar dengan uang tunai (Cash) Rp490.000 (Pas). Kasir memproses pembayaran dan mencetak struk invoice lunas.
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Stok barang `CTS-POT-500K` di cabang tersebut berkurang sebanyak **2 unit**.
        - Status servis berubah menjadi *Sudah Diserahkan (Closed)*.
        - **Jurnal Umum Akuntansi (Saat Transaksi Servis Selesai Dibayar di POS)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-01 | Kas Laci POS (Cash) | 490.000 | |
          | 4112-01 | Pendapatan Jasa Servis Musik | | 250.000 |
          | 4111-01 | Pendapatan Penjualan Sparepart | | 240.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP Sparepart) | 160.000 | |
          | 1131-01 | Persediaan Barang Dagang | | 160.000 |

---

### D. Fitur: Retur Penjualan (Sebagian / Total)
*   **Fokus Kerja**: Mengelola pengembalian barang dari pelanggan secara tertib, memulihkan kuantitas persediaan di database cabang jika layak jual kembali, memproses pengembalian dana (*refund*) tunai/non-tunai secara akurat, serta menjurnal pemotongan omset dan PPN keluaran.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Verifikasi Struk/Invoice**: Kasir memindai barcode struk atau mencari nomor invoice asli pelanggan untuk memverifikasi keabsahan transaksi dan memastikan masih dalam batas waktu garansi retur (misal: maks 7 hari).
    2. **Pemilihan Item & Jumlah**: Kasir memilih barang spesifik dari daftar belanja invoice asal dan menginput jumlah Qty yang akan diretur (mendukung retur sebagian atau total).
    3. **Penilaian Kondisi Fisik**: Kasir memeriksa fisik barang retur:
       - *Layak Jual Kembali (Return to Inventory)*: Stok barang di gudang cabang aktif bertambah secara otomatis.
       - *Rusak/Cacat Pabrik (Write-off)*: Barang dikeluarkan dari stok aktif dan langsung dibebankan sebagai kerugian barang rusak.
    4. **Proses Refund**:
       - Transaksi Asal Cash: Kasir menyerahkan dana tunai langsung dari laci kasir (*Refund Cash*).
       - Transaksi Asal Non-Cash: Kasir menerbitkan voucher belanja (*Store Credit*) atau mentransfer refund.
       - Transaksi Asal Kredit (Piutang): Sistem memotong nominal saldo piutang aktif pelanggan secara otomatis.
    5. **Finishing**: Kasir mencetak Struk Bukti Retur. Sistem memperbarui database stok dan memposting jurnal penyesuaian penjualan otomatis.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Detail Retur**: Dialog pencarian invoice dengan checklist item barang, dropdown alasan retur (Cacat Pabrik, Salah Beli, Kerusakan Pengiriman), dan radio button status kondisi fisik barang (Bagus/Kembali ke Stok vs Rusak/Write-off).
    *   **Panel Refund & Bukti**: Menampilkan kalkulasi total nominal refund (harga item + proporsi PPN), dropdown opsi refund (Cash/Voucher/Potong Piutang), dan tombol cetak bukti retur.
*   **Studi Kasus Nyata (Real Case Scenario - Retur Sebagian Barang Cacat)**:
    *   **Transaksi Pembelian Asal (Invoice Terdaftar)**:
        *   Nomor Invoice: `INV-20260615-009`
        *   Metode Pembayaran: Lunas Tunai (Cash)
        *   Barang Dibeli:
            - 1 unit Gitar Listrik *Yamaha Pacifica 112V* = Rp2.700.000 (HPP: Rp2.100.000)
            - 1 unit Kabel Instrumen *Fender Deluxe 3m* (SKU: `FND-CBL-DLX-3M`) = Rp300.000 (HPP: Rp180.000)
            - PPN 11% Terkait = Rp330.000
            - Total Grand Total Bayar = **Rp3.330.000**.
    *   **Proses Retur**:
        1. Pelanggan kembali setelah 2 hari karena Kabel Fender Deluxe cacat suara (mati total). Gitar Pacifica dalam kondisi bagus dan disimpan pelanggan.
        2. Kasir mencari `INV-20260615-009` di sistem, lalu mencentang item **Kabel Fender Deluxe** untuk diretur (Qty: 1).
        3. Kasir memilih Alasan: **Cacat Pabrik**, dan Status Kondisi: **Rusak / Write-off** (barang langsung dipisahkan untuk dimusnahkan/diklaim ke distributor, bukan dijual kembali).
        4. Sistem menghitung nominal refund:
           $$\text{Subtotal Kabel} = \text{Rp300.000}$$
           $$\text{PPN 11\% Terkait} = \text{Rp33.000}$$
           $$\text{Total Dana Refund (Uang Kembali)} = \text{Rp300.000} + \text{Rp33.000} = \mathbf{Rp333.000}$$
        5. Maya (Kasir) menyerahkan uang tunai Rp333.000 dari laci kasir kepada pelanggan dan menekan tombol "Posting Retur".
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Stok barang `FND-CBL-DLX-3M` di gudang cabang dipotong (keluar dari persediaan barang dagang aktif karena berstatus rusak/dimusnahkan).
        - Uang kas fisik laci kasir berkurang sebesar Rp333.000.
        - **Jurnal Umum Akuntansi (Saat Konfirmasi Retur Selesai Terposting)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 4115-01 | Retur & Potongan Penjualan | 300.000 | |
          | 2115-01 | Hutang Pajak Keluaran (PPN 11%) | 33.000 | |
          | 1111-01 | Kas Laci POS | | 333.000 |
          | 5115-99 | Beban Kerugian Barang Rusak/Musnah | 180.000 | |
          | 1131-01 | Persediaan Barang Dagang | | 180.000 |

---

### E. Fitur: Deposit Pelanggan (Downpayment) & Booking Barang
*   **Fokus Kerja**: Memfasilitasi pencatatan penerimaan uang muka (Downpayment/DP) dari pelanggan untuk pemesanan (*booking*) barang indent/inden, melacak saldo deposit aktif per pelanggan, dan mengotomatiskan pemotongan saldo deposit saat pelunasan transaksi penjualan di POS.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Penerimaan Uang Muka (Deposit)**: Pelanggan menyetorkan nominal DP untuk memesan barang yang belum tersedia (inden). Kasir membuat entri penerimaan deposit baru.
    2. **Pencatatan Liabilitas**: Sistem mencatat setoran uang muka ini sebagai liabilitas jangka pendek (*Hutang Uang Muka Pelanggan*) di neraca saldo, bukan pendapatan penjualan langsung. Saldo deposit ditambahkan ke profil data member.
    3. **Pemicu Pelunasan POS**: Saat barang pesanan tiba di toko, kasir memproses penjualan barang tersebut melalui menu POS.
    4. **Opsi Pemotongan Deposit**: Saat checkout, jika member terpilih memiliki saldo deposit aktif, sistem otomatis memunculkan pilihan "Potong Saldo Uang Muka". Kasir dapat menginput nominal saldo yang ingin digunakan untuk memotong tagihan.
    5. **Pelunasan Sisa Tagihan**: Sisa tagihan setelah pemotongan deposit dilunasi dengan metode pembayaran lain (Cash/Debit/QRIS). Sistem memperbarui saldo deposit member menjadi berkurang/nol, dan mencetak Invoice Final terintegrasi.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Registrasi Deposit**: Dialog sederhana berisi input nama pelanggan, nominal uang muka, catatan detail barang indent (nama/spesifikasi barang), pilihan bank penerima dana, dan tombol cetak struk tanda terima deposit.
    *   **Checkout POS - Panel Deposit**: Panel pembayaran yang mendeteksi saldo deposit member terpilih secara otomatis, menyajikan tombol toggle "Gunakan Deposit", input field nominal pemotongan, dan informasi sisa saldo pasca-transaksi.
*   **Studi Kasus Nyata (Real Case Scenario - Alur Booking Gitar Inden)**:
    *   **Fase 1: Penyetoran DP Booking Inden (05 Juni 2026)**:
        *   Pelanggan: **Budi Santoso** (Gold Member)
        *   Barang Inden: Gitar Listrik *Fender American Professional II Stratocaster* (Estimasi tiba: 3 minggu)
        *   Nominal Uang Muka disetor: **Rp5.000.000** (Budi membayar lunas via Transfer Bank BCA).
        *   **Output Finansial & Jurnal Akuntansi (Penerimaan DP)**:
          - Saldo deposit Budi Santoso tercatat Rp5.000.000 di profil database.
          - **Jurnal Umum Akuntansi (Saat Kasir Posting Setoran DP)**:
            
            | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
            | :--- | :--- | :--- | :--- |
            | 1112-01 | Kas Bank BCA Utama | 5.000.000 | |
            | 2119-01 | Hutang Uang Muka Pelanggan (Deposit) | | 5.000.000 |

    *   **Fase 2: Kedatangan Barang & Pelunasan Akhir di POS (25 Juni 2026)**:
        *   Gitar pesanan Fender tiba di Cabang Depok.
        *   Harga Jual Gitar Fender: **Rp30.000.000** (Sebelum Pajak).
        *   Kasir Maya menginput PPN manual 11% = **Rp3.300.000**.
        *   Total Nilai Invoice = **Rp33.300.000**.
        *   Maya memilih profil member **Budi Santoso** -> Mengeklik opsi potong deposit sebesar **Rp5.000.000** (Saldo deposit Budi di database terpotong habis menjadi Rp0).
        *   Sisa Tagihan yang harus dilunasi Budi = $\text{Rp33.300.000} - \text{Rp5.000.000} = \mathbf{Rp28.300.000}$ (Budi menggesek kartu debit di EDC Mandiri senilai Rp28.300.000).
        *   **Output Finansial & Jurnal Akuntansi (Pelunasan & Serah Terima Unit)**:
          - Stok Gitar Fender di Cabang Depok resmi berkurang 1 unit.
          - **Jurnal Umum Akuntansi (Saat Invoice Lunas Diposting)**:
            *(Asumsi nilai HPP Gitar Fender = Rp22.000.000)*
            
            | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
            | :--- | :--- | :--- | :--- |
            | 1113-01 | Kas Bank Mandiri EDC (Debit) | 28.300.000 | |
            | 2119-01 | Hutang Uang Muka Pelanggan (Deposit) | 5.000.000 | |
            | 4111-01 | Pendapatan Penjualan Ritel | | 30.000.000 |
            | 2115-01 | Hutang Pajak Keluaran (PPN 11%) | | 3.300.000 |
            | 5111-01 | Harga Pokok Penjualan (HPP) | 22.000.000 | |
            | 1131-01 | Persediaan Barang Dagang | | 22.000.000 |

---

### F. Fitur: Pelunasan Piutang Pelanggan
*   **Fokus Kerja**: Mengelola proses penerimaan pelunasan pembayaran piutang berjalan dari pelanggan member atas transaksi kredit sebelumnya, memperbarui sisa saldo piutang member secara real-time, dan mencatat jurnal penerimaan kas serta pengurangan saldo piutang usaha.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Pencarian Piutang Aktif**: Kasir membuka menu "Pelunasan Piutang", lalu mencari nama member atau nomor invoice kredit asal. Sistem menampilkan daftar tagihan yang belum lunas beserta tanggal jatuh temponya.
    2. **Input Nominal Pembayaran**: Kasir memasukkan nominal pembayaran (mendukung pelunasan sebagian atau pelunasan total).
    3. **Pilihan Metode Pelunasan**: Kasir memilih metode pembayaran pelunasan (Cash, Debit, Transfer, QRIS, atau memotong saldo deposit pelanggan jika ada).
    4. **Finishing**: Kasir mengeklik "Posting Pelunasan". Sistem mencetak *Kuitansi Bukti Pelunasan Piutang*, memotong saldo piutang member di database, dan memposting jurnal umum akuntansi.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Dashboard Piutang Pelanggan**: Daftar baris invoice piutang dengan indikator status hari keterlambatan (hijau jika aman, merah jika melewati jatuh tempo).
    *   **Form Pembayaran Pelunasan**: Popup dialog yang menyajikan informasi Sisa Piutang, kolom input Nominal Pembayaran, dropdown Pilihan Metode Pembayaran, dan tombol konfirmasi cetak struk thermal.
*   **Studi Kasus Nyata (Real Case Scenario - Pelunasan Sebagian Piutang Member Gold)**:
    *   **Kondisi Awal (Piutang Tergantung)**:
        *   Pelanggan: **Budi Santoso** (Gold Member) memiliki saldo piutang jatuh tempo sebesar **Rp5.000.000** atas pembelian gitar tempo hari (Invoice: `INV-20260510-002`). Status overdue: 5 hari.
    *   **Transaksi Pelunasan**:
        *   Budi datang ke Cabang Depok dan ingin membayar cicilan piutangnya sebesar **Rp3.000.000** (Pembayaran sebagian).
        *   Budi melunasi menggunakan Transfer bank ke rekening BCA Diego Music Store.
        *   Maya (Kasir) menginput Nominal Bayar: Rp3.000.000, memilih bank BCA, menginput Trace No/Ref No Transfer: `TRX-998822`, lalu klik "Konfirmasi Pembayaran".
    *   **Output Finansial & Jurnal Akuntansi**:
        - Sisa piutang Budi Santoso di profil member berkurang menjadi **Rp2.000.000** ($\text{Rp5.000.000} - \text{Rp3.000.000}$).
        - Alert alarm merah *overdue* Budi di dashboard Owner terupdate otomatis.
        - **Jurnal Umum Akuntansi (Saat Pelunasan Diposting)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1112-01 | Kas Bank BCA Utama | 3.000.000 | |
          | 1121-01 | Piutang Usaha - Pelanggan Ritel | | 3.000.000 |

---

### G. Fitur: Edit Penjualan (Koreksi Invoice & Log Audit)
*   **Fokus Kerja**: Mengamankan proses koreksi kesalahan input transaksi kasir setelah invoice terlanjur diposting (Lunas) dengan mewajibkan otorisasi supervisor/owner, menghitung nominal selisih pengembalian/kekurangan, serta mencatat log audit riwayat perubahan transaksi secara transparan guna mencegah penyalahgunaan (*fraud*).
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Pengajuan Koreksi**: Kasir membuka invoice yang ingin dikoreksi melalui menu Histori Penjualan, lalu mengeklik tombol "Koreksi Transaksi".
    2. **Otorisasi Supervisor**: Sistem mengunci halaman edit dan memunculkan pop-up prompt otorisasi. Supervisor/Owner wajib memasukkan PIN atau password khusus untuk membuka akses edit.
    3. **Pengubahan Item & Kalkulasi Ulang**: Kasir mengubah item, kuantitas, diskon, atau nilai PPN. Sistem secara otomatis menghitung selisih antara nilai invoice lama dengan nilai invoice baru.
       - Jika nilai baru *lebih kecil*, sistem menampilkan nominal uang yang wajib dikembalikan (*refund*) tunai dari laci kasir atau dipindahkan menjadi saldo deposit member.
       - Jika nilai baru *lebih besar*, kasir memproses sisa kekurangan pembayaran di kasir menggunakan metode pembayaran terpilih.
    4. **Reversal Jurnal Akuntansi**: Untuk menjaga keakuratan buku besar, sistem secara otomatis melakukan pembalikan (*reversal*) penuh atas entri jurnal lama dan memposting entri jurnal baru hasil koreksi.
    5. **Pencatatan Log Audit**: Sistem mencatat identitas operator, nama supervisor pemberi otorisasi, waktu perubahan, alasan perubahan, serta salinan detail data sebelum & sesudah koreksi ke dalam database Log Audit (tidak dapat dihapus).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Prompt Otorisasi PIN**: Popup dialog modal berwarna merah-oranye peringatan yang meminta input kredensial supervisor.
    *   **Perbandingan Transaksi (Audit View)**: Tampilan visual *split-screen* yang membandingkan keranjang belanja asal vs keranjang belanja setelah diedit, lengkap dengan panel hitung selisih kembalian/kurang bayar.
*   **Studi Kasus Nyata (Real Case Scenario - Salah Input Kuantitas Kabel)**:
    *   **Kondisi Awal (Invoice `INV-20260620-001`)**:
        *   Kasir Maya salah menginput kuantitas Kabel Fender Deluxe (diinput 3 unit, padahal pelanggan hanya membawa 1 unit).
        *   Harga Kabel: Rp300.000/unit.
        *   Total Invoice Asal = Rp900.000 + PPN 11% (Rp99.000) = **Rp999.000** (Telah lunas dibayar tunai).
    *   **Proses Koreksi di Kasir**:
        1. Maya mengeklik "Koreksi Transaksi" pada invoice `INV-20260620-001`.
        2. Owner memasukkan PIN Otorisasi untuk membuka akses edit.
        3. Maya mengubah kuantitas Kabel Fender dari 3 unit menjadi 1 unit.
        4. Total Invoice Baru = Rp300.000 + PPN 11% (Rp33.000) = **Rp333.000**.
        5. Sistem menampilkan petunjuk pengembalian kas tunai sebesar: $\text{Rp999.000} - \text{Rp333.000} = \mathbf{Rp666.000}$. Maya menyerahkan Rp666.000 tunai dari laci kasir ke pelanggan.
    *   **Output Finansial & Jurnal Akuntansi (Otomatis)**:
        - Stok Kabel Fender di sistem dikoreksi kembali bertambah 2 unit di Cabang Depok.
        - **Jurnal Umum Reversal (Membalik Transaksi Asal Otomatis)**:
          *(Asumsi HPP Kabel Fender = Rp180.000 per unit)*
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 4111-01 | Pendapatan Penjualan Ritel | 900.000 | |
          | 2115-01 | Hutang Pajak Keluaran (PPN 11%) | 99.000 | |
          | 1111-01 | Kas Laci POS - Depok | | 999.000 |
          | 1131-01 | Persediaan Barang Dagang | 540.000 | |
          | 5111-01 | Harga Pokok Penjualan (HPP) | | 540.000 |

        - **Jurnal Umum Baru (Posting Hasil Koreksi Terbaru)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-01 | Kas Laci POS - Depok | 333.000 | |
          | 4111-01 | Pendapatan Penjualan Ritel | | 300.000 |
          | 2115-01 | Hutang Pajak Keluaran (PPN 11%) | | 33.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP) | 180.000 | |
          | 1131-01 | Persediaan Barang Dagang | | 180.000 |

---

### H. Fitur: Manajemen Master Data Dasar POS
*   **Fokus Kerja**: Menyediakan fungsi pengelolaan CRUD (Create, Read, Update, Delete) data referensi dasar (Pelanggan/Member, User/Pengguna Sistem, Satuan Unit Barang, dan Kategori Penjualan) guna mendukung validitas relasi data transaksi operasional kasir secara berkelanjutan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Manajemen Data Pelanggan & Keanggotaan**:
       - Admin/Kasir menginput Nama Pelanggan, Nomor Telepon/WhatsApp (Wajib Unik), Email, Alamat, serta mengeset Tipe Member (Gold, Silver, Umum).
       - Setiap registrasi member baru, sistem menginisialisasi saldo poin belanja dari nol. Sistem mencegah penyimpanan jika nomor WhatsApp sudah terpakai di database.
    2. **Manajemen User Pengguna (Kasir & Supervisor)**:
       - Owner mendaftarkan akun staf dengan menginput Nama, Username unik, Password (dienkripsi otomatis menggunakan bcrypt), Cabang Penugasan, dan Level Privilege (Owner, Supervisor, Kasir).
       - Akun berlevel Supervisor/Owner wajib dikonfigurasi dengan PIN Otorisasi 6 digit yang nantinya digunakan untuk menyetujui transaksi sensitif (koreksi invoice, retur, dsb).
    3. **Manajemen Satuan Unit Barang (UOM)**:
       - Admin mendaftarkan unit satuan jual barang (misal: `Pcs`, `Unit`, `Set`, `Pack`). Satuan ini akan otomatis muncul sebagai dropdown pilihan tipe satuan saat membuat master produk/barang dagang ritel.
    4. **Manajemen Kategori Penjualan**:
       - Admin membuat klasifikasi tipe penjualan toko (misal: Ritel Toko, Penjualan Event/Grosir, Jasa Servis).
       - Kategori ini menentukan pemetaan otomatis akun pendapatan pada jurnal akuntansi POS (misal Kategori Ritel dijurnal ke akun `4111-01`, sedangkan Kategori Servis Musik dijurnal ke akun `4112-01`).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Dashboard CRUD Terpusat**: Halaman berisi tab navigasi (Pelanggan, User, Satuan, Kategori) dengan tabel data interaktif, kolom pencarian cepat, tombol aksi edit (ikon pensil), dan hapus (ikon tempat sampah).
    *   **Form Popup Modal**: Dialog input dinamis yang muncul di atas halaman POS, memungkinkan kasir mendaftarkan member baru secara cepat di tengah-tengah antrean belanja tanpa memuat ulang (*reload*) halaman.
*   **Studi Kasus Nyata (Real Case Scenario - Registrasi Member Gold & Akun Staf)**:
    *   **Kasus 1: Pendaftaran Member Baru**:
        *   Kasir Maya mendaftarkan pelanggan bernama **Rian Hidayat** (No. WA: `081234567890`) melalui tombol cepat pendaftaran member di POS.
        *   Sistem memvalidasi keunikan nomor WA Rian di database pusat. Setelah valid, Rian resmi terdaftar sebagai **Gold Member** dan sistem menerbitkan kartu loyalitas digital berbasis kode QR unik.
    *   **Kasus 2: Penambahan User Kasir Baru**:
        *   Owner mendaftarkan staf baru bernama **Maya Anggraini** dengan Username: `maya.depok` dan Role: `Kasir` untuk Cabang Depok.
        *   Sistem menyimpan password terenkripsi Maya, membatasi hak aksesnya hanya untuk menu operasional kasir (tidak bisa mengakses laporan keuangan atau pengaturan master), dan menetapkan bahwa Maya tidak memiliki PIN otorisasi supervisor.

---

### I. Fitur: Manajemen Servis Gitar & Barang Musik
*   **Fokus Kerja**: Mengelola penerimaan barang servis pelanggan (gitar, keyboard, amp), mencetak tanda terima fisik/digital, melacak progres pengerjaan teknisi, merinci biaya jasa & suku cadang, serta mengonversinya menjadi invoice penjualan resmi di POS saat diserahkan ke pelanggan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Penerimaan Servis (Tanda Terima)**: Kasir menginput data pelanggan, jenis instrumen, nomor seri, keluhan kerusakan, dan estimasi biaya. Sistem menerbitkan *Tanda Terima Servis* dengan QR Code unik.
    2. **Pelacakan Status Progres**: Status servis diperbarui secara berurutan oleh teknisi:
       - `Antrean (Queue)`: Barang baru diterima dan menunggu pemeriksaan.
       - `Diagnosis (Diagnosing)`: Teknisi membongkar dan mengidentifikasi suku cadang yang rusak.
       - `Pengerjaan (In-Progress)`: Servis sedang dilakukan (teknisi dapat menambahkan suku cadang dari stok toko ke dalam *service bill*).
       - `Selesai (Done)`: Servis selesai, pelanggan dihubungi otomatis via WhatsApp Gateway.
       - `Diserahkan (Collected)`: Serah terima barang kepada pelanggan.
    3. **Konversi ke Invoice POS**:
       - Saat serah terima, kasir melakukan scan QR Code pada tanda terima servis.
       - Sistem memuat detail biaya jasa servis ditambah harga suku cadang yang digunakan ke keranjang POS.
       - Kasir memproses pembayaran (Lunas/Kredit), mencetak struk resmi, dan stok suku cadang terpotong resmi.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Papan Kanban Servis (Service Board)**: Board visual drag-and-drop kolom status (Queue, Diagnosing, In-Progress, Done, Collected) untuk mempermudah teknisi memindahkan barang servis.
    *   **Form Input Servis Baru**: Form input keluhan, kondisi fisik barang (dengan checklist goresan/cacat awal), dan estimasi tanggal selesai.
*   **Studi Kasus Nyata (Real Case Scenario - Servis Gitar Gibson Les Paul & Ganti Senar)**:
    *   **Penerimaan Barang**:
        *   Pelanggan: **Budi Santoso**. Barang: Gitar *Gibson Les Paul Standard* (Keluhan: Kerusakan wiring pickup & ganti senar).
        *   Tanda Terima Servis diterbitkan: `SRV-DEP-20260621-0005`. PDF dikirim ke WhatsApp Budi.
    *   **Progres Servis & Penggunaan Suku Cadang**:
        *   Teknisi mengambil barang dan mengubah status ke *In-Progress*.
        *   Teknisi mengganti senar menggunakan 1 set senar *D'Addario EXL110* (diambil dari stok toko Cabang Depok seharga Rp90.000, HPP: Rp60.000) dan mencatat jasa perbaikan kelistrikan sebesar Rp150.000.
        *   Teknisi memindahkan status ke *Done*. Sistem mengirim WhatsApp otomatis: *"Halo Budi, gitar Gibson Anda telah selesai diservis dengan total biaya Rp240.000."*
    *   **Serah Terima & Jurnal POS**:
        *   Budi datang ke toko, kasir melakukan scan QR `SRV-DEP-20260621-0005` -> POS otomatis terisi item: Jasa Servis (Rp150.000) + Senar D'Addario (Rp90.000).
        *   Budi membayar tunai Rp240.000. Status servis diubah ke *Collected*.
        *   **Jurnal Umum Akuntansi (Saat Kasir Posting Penyerahan Servis)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-01 | Kas Laci POS - Depok | 240.000 | |
          | 4112-01 | Pendapatan Jasa Servis | | 150.000 |
          | 4111-01 | Pendapatan Penjualan Ritel (Suku Cadang) | | 90.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP Senar) | 60.000 | |
          | 1131-01 | Persediaan Barang Dagang - Depok | | 60.000 |

---

### J. Fitur: Laporan Operasional POS & Cetak Barcode Barang
*   **Fokus Kerja**: Menyediakan akses laporan operasional harian (laporan penjualan harian, pelacakan piutang & pelunasan pelanggan, log kas laci harian, serta pencarian stok & harga cabang) dan menyediakan utilitas cetak stiker barcode fisik (Code 128) untuk mendukung kelancaran pemindaian barang di kasir POS.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Penyajian Laporan POS**:
       - *Laporan Penjualan*: Rekapitulasi omset harian yang terfilter per sales rep, per kategori (ritel/servis), dan per metode pembayaran.
       - *Laporan Piutang & Pelunasan*: Daftar aging piutang berjalan per pelanggan beserta histori cicilan pelunasan.
       - *Laporan Kas Harian*: Log mutasi kas masuk/keluar dari awal buka sesi laci kasir hingga tutup kasir.
       - *Daftar Stok & Harga*: Katalog grid pencarian sisa unit barang per cabang lengkap dengan informasi 5 level harga jual.
    2. **Utilitas Cetak Label Barcode**:
       - Staf memilih SKU produk dari inventaris, memasukkan jumlah label stiker yang ingin dicetak, dan memilih layout ukuran stiker.
       - Sistem memformat data produk menjadi instruksi bahasa printer termal (seperti ZPL atau EPL) yang memuat informasi: Kode SKU, Nama Barang, Harga Jual, dan Gambar Barcode Batang 1D (Standard Code 128).
       - Perintah dikirimkan langsung ke printer barcode mini lokal untuk mencetak stiker perekat yang siap ditempel di fisik gitar atau kemasan aksesoris musik.
    3. **Utilitas Backup Database (System Admin Utility)**:
       - Menyediakan fungsi backup database relasional (PostgreSQL) secara manual atau terjadwal otomatis setiap hari ke penyimpanan cloud terenkripsi (misal AWS S3).
       - File backup berformat kompresi `.sql.gz` dapat diunduh langsung oleh Owner untuk keperluan arsip lokal.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Tab Laporan Operasional POS**: Dashboard rekap ringkas dengan filter tanggal harian, tombol "Print PDF", dan tombol eksport data ke Excel.
    *   **Form Generator Barcode**: Tampilan popup modal preview label stiker barcode secara visual ($3 \times 1.5$ cm) sebelum dicetak, lengkap dengan input field Qty Label dan tombol aksi "Cetak via Raw Printer Driver".
    *   **Panel Backup Manager**: Halaman pengaturan sistem untuk memicu pencadangan database manual ("Backup Sekarang"), menampilkan tabel riwayat cadangan (Waktu Backup, Ukuran File, Status, Link Download), dan konfigurasi jadwal otomatis harian (Cron Job).
*   **Studi Kasus Nyata (Real Case Scenario - Cetak Barcode, Kas Sesi, & Backup Database)**:
    *   **Kasus 1: Cetak Barcode Gitar Baru Masuk**:
        *   Staf menerima kiriman 10 unit Gitar Akustik *Fender CD-60S Black* (SKU: `FND-CD60S-BLK`).
        *   Staf membuka menu Utility Cetak Barcode, memanggil SKU `FND-CD60S-BLK`, memasukkan Qty = 10, dan klik "Print".
        *   Printer stiker thermal mencetak 10 lembar stiker perekat. Setiap stiker berisi visual barcode 1D Code 128, teks SKU, nama produk, dan harga jual `Rp2.500.000`. Staf menempelkan stiker ke belakang headstock gitar. Saat transaksi POS, kasir cukup memindai stiker ini untuk menginput barang instan ke keranjang.
    *   **Kasus 2: Cetak Laporan Kas Sesi Akhir Shift**:
        *   Kasir Maya mengakhiri shift kerjanya. Sebelum menekan tombol Tutup Sesi, Maya membuka Laporan Kas Harian untuk memverifikasi pencocokan kas fisik.
        *   Laporan menampilkan total transaksi tunai masuk laci sebesar Rp3.500.000. Maya menghitung uang fisik di laci dan mencocokkannya. Maya mencetak struk ringkasan kas harian termal dan melampirkannya bersama setoran uang tunai ke amplop kasir untuk diserahkan ke Supervisor.
    *   **Kasus 3: Pencadangan Database Mingguan oleh Owner**:
        *   Sebelum melakukan pemeliharaan server bulanan, Owner membuka menu Backup Manager dan mengeklik tombol "Backup Sekarang".
        *   Sistem memproses pencadangan database PostgreSQL, mengunggah file terkompresi `db_backup_20260621_2145.sql.gz` (Ukuran: 45 MB, Status: Sukses) ke server S3, dan Owner mengunduh file tersebut ke laptop pribadinya sebagai salinan cadangan offline.

---

## 2. Modul Inventaris & Rantai Pasok (Procurement)

### A. Fitur: Kalkulasi HPP Otomatis & Penerimaan DO
*   **Fokus Kerja**: Memperbarui nilai Harga Pokok Penjualan (HPP) barang secara otomatis menggunakan metode rata-rata tertimbang (*Weighted Average Method*) setiap kali barang masuk diterima dari supplier, dengan memperhitungkan harga beli dasar ditambah proporsi ongkos kirim pembelian.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Penerimaan Barang**: Staf gudang menerima barang fisik dari supplier berdasarkan dokumen Purchase Order (PO).
    2. **Input DO (Delivery Order)**: Staf menginput jumlah unit riil yang diterima, harga beli dasar dari supplier, dan total biaya ongkos kirim ekspedisi yang ditagihkan untuk pengiriman tersebut.
    3. **Kalkulasi Ongkir per Unit**: Sistem membagi total ongkos kirim dengan jumlah unit barang yang dikirim untuk mendapatkan biaya ongkir per unit.
    4. **Formula Weighted Average**: Sistem mengambil stok barang yang masih tersisa di gudang beserta nilai HPP lamanya, lalu menghitung HPP baru menggunakan rumus:
       $$\text{HPP Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Baru} \times (\text{Harga Beli Dasar} + \text{Ongkir per Unit}))}{\text{Stok Lama} + \text{Qty Baru}}$$
    5. **Database Update**: Nilai HPP baru ini disimpan pada master barang dan akan digunakan sebagai acuan nilai HPP pada transaksi POS penjualan berikutnya.
*   **Studi Kasus Nyata (Real Case Scenario - Penerimaan Barang & Perubahan HPP)**:
    *   **Kondisi Stok Awal (Sebelum Barang Masuk)**:
        *   Nama Produk: Gitar Listrik *Yamaha Pacifica 112V Sunburst* (SKU: `YMH-PAC-112V-SB`)
        *   Stok Tersisa di Sistem: **5 unit**
        *   HPP Lama di Sistem: **Rp2.800.000** per unit
    *   **Transaksi Penerimaan Barang Baru (DO)**:
        *   Supplier: PT Yamaha Musik Indonesia
        *   Jumlah Diterima (Qty Baru): **10 unit**
        *   Harga Beli Dasar Supplier: **Rp2.500.000** per unit
        *   Total Biaya Ongkir Ekspedisi: **Rp1.500.000** (ditagihkan secara global untuk pengiriman 10 unit gitar ini)
    *   **Alur Hitung Sistem**:
        1. Ongkir per Unit = $\text{Rp1.500.000} \div 10 \text{ unit} = \mathbf{Rp150.000}$ per unit.
        2. Total Harga Pembelian per unit (termasuk ongkir) = $\text{Rp2.500.000 (Harga Beli)} + \text{Rp150.000 (Ongkir)} = \mathbf{Rp2.650.000}$ per unit.
        3. Total Nilai Pembelian Baru = $10 \text{ unit} \times \text{Rp2.650.000} = \mathbf{Rp26.500.000}$.
        4. Mengaplikasikan Rumus Weighted Average HPP Baru:
           $$\text{HPP Baru} = \frac{(5 \text{ unit} \times \text{Rp2.800.000}) + (10 \text{ unit} \times \text{Rp2.650.000})}{5 \text{ unit} + 10 \text{ unit}}$$
           $$\text{HPP Baru} = \frac{\text{Rp14.000.000 (Nilai Stok Lama)} + \text{Rp26.500.000 (Nilai Pembelian Baru)}}{15 \text{ unit}}$$
           $$\text{HPP Baru} = \frac{\text{Rp40.500.000}}{15 \text{ unit}} = \mathbf{Rp2.700.000}$$
    *   **Output Sistem & Jurnal Akuntansi**:
        - Stok barang *Yamaha Pacifica 112V Sunburst* bertambah menjadi **15 unit**.
        - Nilai HPP di master barang diperbarui menjadi **Rp2.700.000** per unit (turun dari Rp2.800.000 karena harga beli baru lebih murah).
        - **Jurnal Umum Otomatis (Saat Konfirmasi DO Diterima)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1131-01 | Persediaan Barang Dagang | 26.500.000 | |
          | 2111-01 | Hutang Dagang Supplier (Yamaha) | | 25.000.000 |
          | 2112-02 | Hutang Biaya Ongkir (Ekspedisi) | | 1.500.000 |

---

### B. Fitur: Mutasi Stok Antar-Cabang
*   **Fokus Kerja**: Memfasilitasi pemindahan inventaris barang fisik secara aman antara gudang cabang yang berbeda, mengendalikan pelacakan status barang selama dalam perjalanan (*In-Transit*), dan mencatat jurnal penyesuaian reklasifikasi lokasi persediaan secara otomatis untuk mencegah penyusutan barang tanpa dokumen.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Pengajuan Permintaan (Request)**: Cabang tujuan (misal Cabang Depok) mengajukan permintaan mutasi barang ke Cabang asal (Cabang Jakarta Pusat) melalui sistem karena stok habis. Status: *Requested*.
    2. **Persetujuan & Pengiriman (Transit)**: Kepala Gudang Cabang asal memverifikasi stok fisik, lalu menyetujui pengiriman. Saat klik "Kirim":
       - Kuantitas barang di database Cabang asal langsung dipotong.
       - Kuantitas barang di database Cabang tujuan belum bertambah.
       - Sistem memasukkan barang tersebut ke akun penampung sementara *Persediaan Dalam Perjalanan (In-Transit)*.
       - Sistem mencetak berkas *Surat Jalan Mutasi*. Status: *In-Transit*.
    3. **Penerimaan & Verifikasi**: Setelah barang tiba di tujuan, staf Cabang tujuan memverifikasi kesesuaian fisik dan kuantitas barang dengan Surat Jalan mutasi.
    4. **Konfirmasi Masuk (Closed)**: Staf Cabang tujuan mengeklik "Konfirmasi Penerimaan". Saat klik terima:
       - Sistem menambah kuantitas barang di database Cabang tujuan secara permanen.
       - Sistem menghapus pencatatan barang dari akun penampung *Persediaan Dalam Perjalanan*. Status: *Received (Closed)*.
       - Sistem membuat jurnal double-entry otomatis reklasifikasi nilai persediaan dari akun cabang pengirim ke cabang penerima.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Formulir Permintaan Mutasi**: Form dengan dropdown pilihan Cabang Asal (Gudang Sumber), Cabang Tujuan (Gudang Target), pencarian barang berbasis auto-complete SKU/Nama, input Qty mutasi, dan textarea catatan alasan mutasi.
    *   **Status Badge Warna**: Label status visual pada tabel pelacakan mutasi: `[ Requested (Orange) ]` $\rightarrow$ `[ In-Transit (Blue) ]` $\rightarrow$ `[ Received (Green) ]`.
*   **Studi Kasus Nyata (Real Case Scenario - Alur Mutasi Barang Antar Cabang)**:
    *   **Profil Barang**:
        *   Nama Produk: Gitar Akustik *Yamaha FS800 Natural* (SKU: `YMH-FS800-NAT`)
        *   Harga Pokok Penjualan (HPP) Barang: **Rp2.000.000** per unit.
    *   **Cabang Terkait**:
        *   Cabang Asal (Pengirim): Jakarta Pusat
        *   Cabang Tujuan (Penerima): Depok
    *   **Alur Proses & Perhitungan**:
        1. Cabang Depok kehabisan stok FS800. Staf Depok membuat permintaan mutasi sebanyak **3 unit** ke Jakarta Pusat.
        2. Supervisor Jakarta Pusat menyetujui mutasi, lalu memproses pengiriman 3 unit gitar FS800.
           - Stok FS800 di Jakarta Pusat berkurang 3 unit.
           - Nilai barang yang keluar = $3 \text{ unit} \times \text{Rp2.000.000} = \mathbf{Rp6.000.000}$.
           - Sistem memindahkan status mutasi menjadi **In-Transit**.
        3. **Jurnal Umum Otomatis (Saat Barang Keluar dari Jakarta Pusat)**:
           
           | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
           | :--- | :--- | :--- | :--- |
           | 1131-99 | Persediaan Dalam Perjalanan (In-Transit) | 6.000.000 | |
           | 1131-01 | Persediaan Barang Dagang - Jakarta Pusat | | 6.000.000 |

        4. Kurir mengantarkan barang. Staf Depok memverifikasi fisik 3 unit gitar FS800 dalam keadaan aman, lalu mengeklik "Konfirmasi Penerimaan" di sistem.
           - Stok FS800 di Cabang Depok bertambah 3 unit.
           - Sistem menutup sesi mutasi dengan status **Received**.
        5. **Jurnal Umum Otomatis (Saat Barang Masuk ke Cabang Depok)**:
           
           | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
           | :--- | :--- | :--- | :--- |
           | 1131-02 | Persediaan Barang Dagang - Depok | 6.000.000 | |
           | 1131-99 | Persediaan Dalam Perjalanan (In-Transit) | | 6.000.000 |

---

### C. Fitur: Offline Mode & Sinkronisasi Auto-sync
*   **Fokus Kerja**: Menjamin kelancaran transaksi penjualan kasir POS di toko fisik saat terjadi pemutusan jaringan internet, merekam data transaksi secara lokal di browser, dan melakukan rekonsiliasi data otomatis berbasis antrean FIFO (First-In, First-Out) setelah terdeteksi online tanpa risiko duplikasi data.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Deteksi Koneksi**: Sistem menggunakan Event Listener Service Worker (`window.addEventListener('offline' / 'online')`) untuk memantau status jaringan secara real-time. Jika internet putus, sistem mengubah mode ke *Offline Mode*.
    2. **Penyimpanan Transaksi Lokal (IndexedDB)**:
       - Transaksi baru yang diinput kasir disimpan secara lokal ke dalam database browser *IndexedDB*.
       - Nomor invoice sementara dihasilkan secara lokal menggunakan kode cabang + penanda offline + timestamp unik (misal: `INV-DEP-OFF-171892015`).
       - Stok barang di IndexedDB lokal langsung dikurangi agar kasir mendapat visualisasi sisa stok real-time saat offline.
    3. **Penyusunan Antrean FIFO**: Transaksi disimpan dalam tabel antrean lokal dengan status *Pending*. Urutan pengiriman diatur secara ketat berdasarkan urutan waktu input (First-In, First-Out / FIFO).
    4. **Rekonsiliasi Otomatis (Auto-sync)**: Saat terdeteksi *Online*, Service Worker otomatis memicu sinkronisasi:
       - Mengirimkan transaksi offline satu per satu sesuai urutan antrean FIFO ke API server.
       - Server memvalidasi keunikan kunci transaksi lokal (*Idempotency Key* / Nomor Invoice Offline) untuk mencegah duplikasi entri jika pengiriman terputus di tengah jalan.
       - Server mengurangi stok gudang pusat secara resmi, menyimpan invoice resmi, memposting jurnal akuntansi, dan mengirim PDF Invoice ke nomor WhatsApp pelanggan.
       - Setelah sukses, transaksi di IndexedDB ditandai sebagai *Synced* dan dibersihkan dari antrean lokal.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Indikator Koneksi POS (Header)**: Badge sinyal di header antarmuka POS dengan status: `[ Online (Hijau) ]` atau `[ Mode Offline (Oranye) ]`.
    *   **Panel Antrean Offline**: Popup modal khusus Owner untuk memantau status sinkronisasi: menampilkan daftar Invoice Offline, waktu transaksi, status pengiriman (Pending / Terkirim), serta log error jika sinkronisasi gagal karena konflik data.
*   **Studi Kasus Nyata (Real Case Scenario - Sinkronisasi Penjualan Offline ke Buku Besar Pusat)**:
    *   **Kondisi Awal**:
        *   Jaringan internet di Cabang Depok mati total pada pukul 14:00. POS berubah ke status **Mode Offline (Oranye)**.
    *   **Transaksi yang Terjadi (Saat Offline)**:
        *   Pelanggan Umum membeli 1 unit *Squier Affinity Stratocaster* (SKU: `SQR-AFF-STR-3TS`) seharga **Rp3.000.000** secara tunai (Cash).
        *   Maya (Kasir) memproses pembayaran di POS -> Sistem mencatat data transaksi ke IndexedDB dengan ID Invoice Sementara: `INV-DEP-OFF-171892015`.
        *   Printer POS mencetak struk thermal lokal (koneksi fisik USB/LAN). Stok gitar Squier di database lokal browser berkurang dari 2 unit menjadi 1 unit.
    *   **Proses Sinkronisasi FIFO Pasca-Online**:
        1. Pukul 14:30, koneksi internet Cabang Depok kembali aktif. POS berubah ke status **Online (Hijau)**.
        2. Service Worker mendeteksi koneksi dan segera mengirimkan transaksi `INV-DEP-OFF-171892015` (transaksi pertama dalam antrean FIFO) ke server pusat.
        3. Server pusat menerima transaksi, memvalidasi nomor invoice offline agar tidak duplikat, lalu mengeksekusi proses server:
           - Mengurangi stok `SQR-AFF-STR-3TS` di database pusat Cabang Depok sebanyak 1 unit.
           - Mengubah status transaksi menjadi *Synced / Lunas*.
           - Memicu WhatsApp Gateway untuk mengirim invoice PDF ke pelanggan.
           - Memposting jurnal pembukuan umum otomatis ke Buku Besar.
        4. Sistem menghapus data antrean lokal dari IndexedDB browser.
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Stok gitar Squier di server pusat terupdate resmi berkurang 1 unit.
        - **Jurnal Umum Akuntansi (Saat Sinkronisasi Sukses Terposting di Pusat)**:
          *(Asumsi HPP Squier = Rp2.200.000)*
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1111-01 | Kas Laci POS - Depok | 3.000.000 | |
          | 4111-01 | Pendapatan Penjualan Ritel | | 3.000.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP) | 2.200.000 | |
          | 1131-01 | Persediaan Barang Dagang | | 2.200.000 |

---

### D. Fitur: Sinkronisasi Marketplace (Omnichannel)
*   **Fokus Kerja**: Menghubungkan inventaris toko fisik Diego Music Store dengan platform e-commerce (Tokopedia & Shopee) secara real-time via API, mencegah terjadinya penjualan berlebih (*overselling*), serta mengotomatiskan pencatatan transaksi dan potongan biaya admin platform ke pembukuan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Sinkronisasi SKU & Stok**: SKU produk di database ERP dipetakan secara identik dengan SKU di marketplace. Setiap perubahan stok fisik di cabang utama/gudang pusat langsung memicu webhook API untuk meng-update stok di semua toko online secara real-time.
    2. **Penarikan Pesanan Otomatis**: Ketika terjadi penjualan di Tokopedia/Shopee, API menarik detail pesanan, memotong stok gudang asal, dan mencatat transaksi sebagai penjualan kredit dengan akun penampung *Piutang Penampung Marketplace* (karena uang mengendap sementara di saldo platform).
    3. **Penyelesaian Transaksi & Rekonsiliasi Dana**:
       - Saat dana ditarik (*disburse*) ke rekening bank utama toko, sistem melakukan rekonsiliasi.
       - Sistem memotong otomatis biaya administrasi platform/layanan (e.g. 2% biaya admin merchant) ke akun beban operasional dan mendebit sisa dana bersih ke kas bank toko.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Halaman Koneksi Channel**: Dashboard visual untuk setup kredensial API Tokopedia/Shopee, pemetaan gudang pengirim default untuk setiap toko online, dan tombol sinkronisasi paksa (*force sync*).
    *   **Log Aktivitas API**: Tabel riwayat pembaruan stok dan status order masuk (Pending, Terkirim, Dibatalkan, Selesai) lengkap dengan penanda status penjurnalan.
*   **Studi Kasus Nyata (Real Case Scenario - Penjualan Gitar Fender via Tokopedia)**:
    *   **Kondisi Awal**:
        *   Barang: Gitar Akustik *Fender CD-60S Black* (SKU: `FND-CD60S-BLK`, HPP: Rp1.500.000).
        *   Stok terdaftar di Cabang Jakarta Pusat: **3 unit**. Stok online Tokopedia sinkron di angka 3 unit.
    *   **Transaksi Penjualan Tokopedia (18 Juni 2026)**:
        *   Pelanggan membeli 1 unit Fender CD-60S Black seharga **Rp2.500.000** di Tokopedia.
        *   API mendeteksi pesanan, memotong stok Jakarta Pusat menjadi 2 unit, mengirim update stok ke Shopee, dan memposting jurnal otomatis.
        *   **Jurnal Umum Akuntansi (Saat Order Selesai & Dana Masuk Saldo Tokopedia)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1121-05 | Piutang Penampung Tokopedia | 2.500.000 | |
          | 4111-01 | Pendapatan Penjualan Ritel | | 2.500.000 |
          | 5111-01 | Harga Pokok Penjualan (HPP) | 1.500.000 | |
          | 1131-01 | Persediaan Barang Dagang - Jakarta | | 1.500.000 |

    *   **Pencairan Dana (Disbursement) ke Bank BCA (19 Juni 2026)**:
        *   Owner menarik dana Rp2.500.000 dari Tokopedia ke rekening BCA toko. Tokopedia mengenakan potongan admin merchant 2% (Rp50.000). Dana bersih masuk ke bank sebesar **Rp2.450.000**.
        *   **Jurnal Umum Akuntansi (Saat Pencairan Dana Diposting)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1112-01 | Kas Bank BCA Utama | 2.450.000 | |
          | 5311-05 | Beban Administrasi & Layanan Platform | 50.000 | |
          | 1121-05 | Piutang Penampung Tokopedia | | 2.500.000 |

---

### E. Fitur: Pelunasan Hutang Vendor
*   **Fokus Kerja**: Mengelola proses pencatatan pelunasan kewajiban hutang dagang kepada supplier atas penerimaan pesanan barang (Delivery Order/DO) tempo kredit, memotong saldo hutang vendor di Buku Pembantu Hutang, serta memposting jurnal pengeluaran kas bank.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Penyaringan Tagihan Outstanding**: Staf finance membuka menu "Pelunasan Hutang Vendor", menyaring berdasarkan nama supplier. Sistem menyajikan daftar faktur pembelian (*Supplier Bill*) yang belum lunas beserta tanggal jatuh temponya.
    2. **Pencatatan Nominal Pembayaran**: Staf menginput nominal pembayaran (mendukung pelunasan cicilan sebagian atau pelunasan total/lunas).
    3. **Pilihan Rekening Pengirim**: Staf memilih rekening bank sumber dana toko (misal rekening BCA Utama).
    4. **Posting & Rekonsiliasi**: Staf mengeklik "Posting Pelunasan". Sistem memotong saldo hutang vendor di sub-ledger pembantu hutang, mengubah status invoice pembelian menjadi *Paid*, menghapus notifikasi pengingat jatuh tempo, dan memposting jurnal umum.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Daftar Hutang Outstanding (Supplier Bill Grid)**: Tabel berisi kolom Nama Vendor, No. Invoice PO, Tanggal Invoice, Batas Jatuh Tempo, Total Hutang, Sisa Hutang, dan kolom aksi "Bayar".
    *   **Modal Form Pembayaran**: Input field untuk memilih Bank Toko, Tanggal Transfer, Nominal Pembayaran, Nomor Referensi Bank (Trace No), dan kolom upload bukti transfer.
*   **Studi Kasus Nyata (Real Case Scenario - Pelunasan Hutang ke PT Yamaha Musik Indonesia)**:
    *   **Kondisi Awal (Outstanding Supplier Bill)**:
        *   Vendor: PT Yamaha Musik Indonesia
        *   Faktur Pembelian: `INV-YMH-20260601-99` (Untuk pembelian 10 unit Gitar Pacifica)
        *   Sisa Nilai Hutang Dagang: **Rp25.000.000**. Jatuh tempo: 24 Juni 2026.
    *   **Transaksi Pelunasan (22 Juni 2026)**:
        *   Staf finance mentransfer pelunasan penuh senilai **Rp25.000.000** via KlikBCA Bisnis ke rekening PT Yamaha Musik Indonesia.
        *   Staf menginput data pelunasan di ERP: Sumber Bank: BCA Utama (`1112-01`), Nominal: Rp25.000.000, Nomor Referensi Transfer: `TRX-YMH-88990`.
    *   **Output Finansial & Jurnal Akuntansi**:
        - Sisa saldo hutang PT Yamaha Musik Indonesia di Buku Pembantu Hutang terupdate menjadi **Rp0** (Lunas).
        - Notifikasi alarm jatuh tempo vendor di Dashboard Owner terhapus otomatis.
        - **Jurnal Umum Akuntansi (Saat Pelunasan Diposting)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 2111-01 | Hutang Dagang Supplier | 25.000.000 | |
          | 1112-01 | Kas Bank BCA Utama | | 25.000.000 |

---

### F. Fitur: Manajemen Multi-Cabang & Konfigurasi Prefiks Dokumen
*   **Fokus Kerja**: Mengelola pendaftaran cabang fisik baru beserta inisialisasi gudang cabang, mengatur konfigurasi penomoran prefiks dokumen unik per cabang untuk menghindari konflik transaksi, serta mengimplementasikan pembatasan akses dan login user berdasarkan cabang aktif.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Registrasi Cabang & Isolasi Gudang**: Owner mendaftarkan cabang baru. Sistem otomatis membuat entitas gudang terpisah khusus untuk cabang tersebut untuk mengisolasi mutasi stok ritel.
    2. **Pengaturan Prefiks Dokumen per Cabang**:
       - Setiap cabang diizinkan mendefinisikan kode prefiks penomoran dokumen sendiri.
       - Tipe dokumen meliputi: Invoice POS Ritel (`INV`), Bon Piutang (`BON`), Penawaran Harga (`QTN`), dan Tanda Terima Servis (`SRV`).
       - Urutan penomoran otomatis (*auto-increment*) berjalan secara terpisah untuk setiap prefiks di masing-masing cabang.
    3. **Penugasan User Multi-Cabang (Atur Akses)**: Owner menentukan daftar cabang fisik yang boleh diakses oleh masing-masing user akun karyawan (staf kasir, supervisor, admin gudang). User dapat dibatasi hanya ke 1 cabang, atau diberikan akses ke beberapa cabang.
    4. **Login Berdasarkan Lokasi Cabang Aktif**:
       - Saat masuk ke sistem, pengguna memasukkan username dan password.
       - Jika user hanya ditugaskan di 1 cabang, sistem langsung meloginkannya ke lokasi tersebut.
       - Jika user ditugaskan di multi-cabang, halaman login menampilkan dropdown pilihan cabang aktif. Begitu cabang aktif dipilih, sesi user dikunci hanya untuk memproses transaksi dan melihat persediaan di cabang terpilih agar data tidak bercampur. User dapat berpindah cabang aktif via fungsi *Switch Branch* di panel profil (memerlukan re-otentikasi sesi).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Setting Cabang**: Halaman manajemen cabang untuk mengisi profil kontak toko serta form pengisian format prefiks penomoran dokumen.
    *   **Dropdown Seleksi Cabang (Login Page)**: Dialog seleksi cabang yang dinamis pada antarmuka login utama.
*   **Studi Kasus Nyata (Real Case Scenario - Registrasi Cabang Depok & Login Terkontrol)**:
    *   **Fase 1: Pendaftaran Cabang Depok**:
        *   Owner mendaftarkan cabang baru: **Diego Music Store - Cabang Depok** (Kode: `DEP`).
        *   Prefiks dokumen di-set unik di database cabang Depok:
            - Invoice POS = `INV-DEP-{YYYYMMDD}-{URUT}` (Contoh: `INV-DEP-20260621-0001`)
            - Struk Piutang/Kredit = `BON-DEP-{YYYYMMDD}-{URUT}`
            - Penawaran/Quotation = `QTN-DEP-{YYYYMMDD}-{URUT}`
            - Nota Terima Servis = `SRV-DEP-{YYYYMMDD}-{URUT}`
    *   **Fase 2: Pemetaan Wewenang Karyawan**:
        *   **Maya Anggraini** (Role: Kasir) -> Hanya diberi hak akses ke Cabang **Depok**.
        *   **Budi Setiawan** (Role: Supervisor) -> Diberi hak akses ke **Jakarta Pusat** dan **Depok**.
    *   **Fase 3: Alur Sesi Login**:
        *   *Maya Login*: Maya login dengan kredensialnya. Sistem mendeteksi Maya hanya terafiliasi dengan Cabang Depok. Sistem langsung membuka Dashboard POS Depok dengan prefiks `INV-DEP-`. Maya tidak bisa melihat stok Jakarta Pusat.
        *   *Budi Login*: Budi login di browser. Sistem mendeteksi hak akses ganda, memunculkan dropdown lokasi, dan Budi memilih "Cabang Depok". Sesi Budi terkunci ke Depok. Setelah selesai inspeksi, Budi memilih "Switch Branch" ke "Jakarta Pusat" via header profile, sistem me-reload data dan mengubah seluruh filter data, stok, dan prefiks dokumen default ke Jakarta Pusat (`INV-JKT-`).

---

### G. Fitur: Retur Pembelian Barang (Purchase Return)
*   **Fokus Kerja**: Mengelola pengembalian barang persediaan dagang yang rusak, cacat produksi, atau tidak sesuai pesanan kepada supplier (vendor), melakukan pengurangan stok fisik gudang cabang secara otomatis, memotong saldo hutang dagang outstanding pada Buku Pembantu Hutang, serta memposting jurnal akuntansi pembalikan hutang vs persediaan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Identifikasi Faktur Penerimaan (DO)**: Staf gudang memilih nomor Delivery Order (DO) asal pembelian barang yang ingin diretur untuk memastikan keaslian harga beli awal.
    2. **Input Kuantitas & Alasan Retur**: Staf memilih item barang yang akan dikembalikan, menginput kuantitas retur (Qty), dan menuliskan alasan pengembalian (misal: cacat kayu gitar, kelistrikan mati).
    3. **Pengurangan Stok & Saldo Hutang**: Saat dokumen Retur Pembelian diposting:
       - Sistem memotong kuantitas persediaan fisik barang di gudang cabang aktif.
       - Sistem mengurangi saldo kewajiban hutang dagang toko kepada supplier bersangkutan di sub-ledger Buku Pembantu Hutang berdasarkan harga beli per unit pada DO asal.
       - Jika pembelian asal dilakukan secara tunai (lunas), nominal retur dicatat sebagai Piutang Vendor atau dikembalikan ke kas bank toko (tergantung kesepakatan dengan supplier).
    4. **Auto-posting Jurnal Akuntansi**: Sistem mendebit akun Hutang Dagang Supplier (mengurangi liabilitas) dan mengkredit akun Persediaan Barang Dagang (mengurangi aset persediaan).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Input Retur Pembelian**: Tabel baris barang yang terhubung dengan DO asal, kolom Qty Beli, input field Qty Retur (dengan validasi maks $\le$ Qty Beli), kolom dropdown alasan retur, dan tombol posting.
*   **Studi Kasus Nyata (Real Case Scenario - Retur 1 Unit Gitar Cacat ke PT Yamaha Musik Indonesia)**:
    *   **Kondisi Awal (Outstanding Bill)**:
        *   Toko memiliki kewajiban hutang ke PT Yamaha Musik Indonesia atas faktur `INV-YMH-20260601-99` (DO Pembelian 10 unit Gitar Pacifica) dengan harga beli Rp2.500.000/unit. Total hutang berjalan = **Rp25.000.000**.
    *   **Transaksi Retur**:
        *   Staf menemukan 1 unit gitar memiliki retak halus pada bodi kayu. Staf membuat draf retur pembelian terikat ke faktur tersebut dengan Qty Retur = 1 unit.
        *   Staf mengeklik "Post Retur".
    *   **Output Keuangan & Jurnal Akuntansi**:
        - Stok fisik Gitar Pacifica di gudang Depok berkurang **1 unit**.
        - Saldo hutang dagang toko kepada PT Yamaha Musik Indonesia di Buku Pembantu Hutang terpotong otomatis sebesar **Rp2.500.000**, sisa hutang berjalan menjadi **Rp22.500.000**.
        - **Jurnal Umum Akuntansi (Saat Retur Pembelian Diposting)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 2111-01 | Hutang Dagang Supplier | 2.500.000 | |
          | 1131-01 | Persediaan Barang Dagang - Depok | | 2.500.000 |

---

## 3. Modul Payroll & Kepegawaian (HR)

### A. Fitur: Slip Gaji Otomatis & Payroll Processing
*   **Fokus Kerja**: Mengotomatiskan penghitungan bulanan Take Home Pay (THP) karyawan dengan menggabungkan komponen Gaji Pokok, Tunjangan, Komisi Sales Bertingkat, Potongan Absensi (Mangkir/Terlambat), dan Cicilan Kasbon Aktif, serta menyusun pembukuan jurnal akuntansi terkait beban gaji secara instan.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Tarik Data Presensi**: HR Admin menarik log kehadiran bulanan karyawan dari database absensi terintegrasi (fingerprint dan absensi foto). Sistem menghitung jumlah hari mangkir dan poin penalty keterlambatan secara otomatis.
    2. **Kalkulasi Komisi Sales**: Sistem mengekstrak total omset penjualan riil yang dicapai sales representative dari POS, membandingkannya dengan target bulanan karyawan, dan menghitung komisi bertingkat berdasarkan persentase tiering komisi.
    3. **Pemberlakuan Potongan Kasbon**: Sistem memeriksa saldo kasbon aktif karyawan. Jika karyawan memiliki cicilan berjalan, nominal cicilan bulan berjalan secara otomatis diplot sebagai pengurang gaji.
    4. **Penyusunan Payroll Draft**: Sistem menggabungkan seluruh komponen penambah dan pengurang gaji ke dalam kalkulator slip gaji. HR Admin melakukan review lemburan (overtime) sebelum menyetujui draft payroll.
    5. **Finishing & Post Jurnal**: Begitu HR Admin melakukan klik "Approve & Post Payroll":
       - Sistem menerbitkan Slip Gaji digital berformat PDF.
       - Sistem mengirimkan pemberitahuan slip gaji otomatis ke WhatsApp karyawan.
       - Saldo sisa kasbon karyawan dikurangi otomatis di database.
       - Sistem membentuk jurnal double-entry otomatis untuk mencatat beban gaji, potongan kasbon, pendapatan denda, dan hutang gaji.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Dashboard Payroll Bulanan**: Tabel rekapitulasi gaji karyawan dengan kolom: No, Nama Karyawan, Gaji Pokok, Total Komisi, Total Potongan, Net Salary (THP), Status Slip (Draft/Posted), Aksi (Detail & Edit).
    *   **Form Detail Slip Gaji (Modal Popup)**:
        - *Section Kiri (Pendapatan/Earnings)*: Input teks Gaji Pokok, Tunjangan Makan, Tunjangan Transport, Kolom Komisi Sales (read-only, dihitung otomatis), dan Tunjangan Lembur.
        - *Section Kanan (Potongan/Deductions)*: Kolom Potongan Kasbon (read-only dari sisa cicilan), Potongan Denda Kehadiran (read-only dari poin absensi), dan Potongan BPJS/Pajak PPh21.
*   **Studi Kasus Nyata (Real Case Scenario - Pemrosesan Slip Gaji & Potongan Komprehensif)**:
    *   **Profil Karyawan**:
        *   Nama Karyawan: Adi Pratama (Sales Representative, Cabang Jakarta Pusat)
        *   Gaji Pokok Dasar: Rp4.000.000
        *   Tunjangan Tetap (Makan & Transport): Rp500.000
    *   **Kondisi Absensi & Poin Denda (Bulan Berjalan)**:
        *   Hari Kerja Wajib: 26 Hari Kerja
        *   Kehadiran Riil: 22 Hari Kerja (2 Hari Cuti Resmi, 2 Hari Mangkir/Tanpa Keterangan)
        *   Aturan Denda Absensi Toko:
            - Mangkir/Tidak hadir tanpa keterangan = Denda potong gaji Rp150.000 per hari mangkir.
            - Keterlambatan masuk kerja > 15 menit (dari fingerprint) = Denda 1 Poin Penalty senilai Rp20.000.
        *   Catatan Pelanggaran Adi: 2 Hari Mangkir dan 3 kali terdeteksi terlambat.
        *   Kalkulasi Poin Denda:
            - Denda Mangkir: $2 \text{ Hari} \times \text{Rp150.000} = \mathbf{Rp300.000}$.
            - Denda Keterlambatan: $3 \text{ Kali} \times \text{Rp20.000} = \mathbf{Rp60.000}$.
            - Total Denda Kehadiran (Pengurang): $\text{Rp300.000} + \text{Rp60.000} = \mathbf{Rp360.000}$.
    *   **Pencapaian Komisi Sales (Bulan Berjalan)**:
        *   Target Omset Pribadi Adi: Rp50.000.000
        *   Omset Riil POS yang Diatribusikan ke Adi: Rp60.000.000 (Melampaui target sebesar 120%)
        *   Skema Komisi Bertingkat Ritel Diego Music Store:
            - Pencapaian $\le$ Target: Komisi flat sebesar 1% dari omset tercapai.
            - Pencapaian $>$ Target: Komisi tambahan sebesar 1,5% khusus untuk selisih nominal di atas target.
        *   Kalkulasi Komisi:
            - Komisi Dasar: $1\% \times \text{Rp50.000.000} = \mathbf{Rp500.000}$.
            - Komisi Kelebihan Target: $1,5\% \times (\text{Rp60.000.000} - \text{Rp50.000.000}) = 1,5\% \times \text{Rp10.000.000} = \mathbf{Rp150.000}$.
            - Total Komisi Sales (Penambah): $\text{Rp500.000} + \text{Rp150.000} = \mathbf{Rp650.000}$.
    *   **Status Kasbon / Cash Advance Aktif**:
        *   Sisa Total Hutang Kasbon Adi di Sistem: Rp1.200.000
        *   Nominal Cicilan Bulanan Tetap (Sesuai Kontrak): **Rp400.000** per bulan.
        *   Sistem memotong otomatis **Rp400.000** dari payroll bulan berjalan.
    *   **Kalkulasi Take Home Pay (THP) Bersih Adi**:
        $$\text{THP Bersih} = (\text{Gaji Pokok} + \text{Tunjangan} + \text{Komisi}) - (\text{Total Denda Kehadiran} + \text{Cicilan Kasbon})$$
        $$\text{THP Bersih} = (\text{Rp4.000.000} + \text{Rp500.000} + \text{Rp650.000}) - (\text{Rp360.000} + \text{Rp400.000})$$
        $$\text{THP Bersih} = \text{Rp5.150.000} - \text{Rp760.000} = \mathbf{Rp4.390.000}$$
    *   **Output Finansial & Jurnal Akuntansi Otomatis**:
        - Sisa hutang kasbon Adi Pratama terpotong di database menjadi **Rp800.000** ($\text{Rp1.200.000} - \text{Rp400.000}$).
        - PDF Slip Gaji dikirim ke WhatsApp Adi Pratama dengan rincian pendapatan Rp5.150.000 dan potongan Rp760.000.
        - **Jurnal Umum Akuntansi (Saat Persetujuan Slip Gaji / Posting Payroll)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 5211-01 | Beban Gaji Pokok & Tunjangan Karyawan | 4.500.000 | |
          | 5211-02 | Beban Komisi Sales Karyawan | 650.000 | |
          | 1135-02 | Piutang Kasbon Karyawan | | 400.000 |
          | 2118-01 | Pendapatan Operasional Lain (Denda Karyawan) | | 360.000 |
          | 2121-01 | Hutang Gaji Karyawan | | 4.390.000 |

        - **Jurnal Umum Akuntansi (Saat Transfer Pembayaran Gaji dari Bank Toko)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 2121-01 | Hutang Gaji Karyawan | 4.390.000 | |
          | 1112-01 | Kas Bank BCA Utama | | 4.390.000 |

---

### B. Fitur: Absensi & Penalty Point Karyawan
*   **Fokus Kerja**: Mengelola pencatatan jam kehadiran karyawan secara real-time via fingerprint/absensi foto geotagging, menghitung durasi keterlambatan dan mangkir secara otomatis, menyusun akumulasi poin denda penalty bulanan, serta menyinkronkannya sebagai pengurang otomatis pada payroll.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Perekaman Log Kehadiran**: Karyawan melakukan absen masuk dan pulang di tablet cabang. Jam check-in dibandingkan dengan jadwal shift yang diatur di master database.
    2. **Kalkulasi Toleransi & Terlambat**: Batas keterlambatan adalah 15 menit dari jam shift (misal shift jam 09:00, keterlambatan dihitung sejak 09:16).
    3. **Penentuan Poin Penalti**:
       - *Keterlambatan (Late Arrival)*: Dikenakan denda **20 poin penalti** per kejadian terlambat.
       - *Mangkir (Absent without leave)*: Jika tidak ada log masuk sama sekali tanpa surat dokter atau pengajuan izin disetujui, dikenakan denda **100 poin penalti** per hari mangkir.
    4. **Konversi Nominal Poin**: Setiap 1 poin denda dikonversikan ke nominal uang sebesar **Rp2.000** (misal: 20 poin = Rp40.000).
    5. **Sinkronisasi Otomatis ke Payroll**: Akumulasi nominal denda ditarik otomatis oleh kalkulator payroll di akhir bulan sebagai pengurang Take Home Pay (THP) karyawan.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Kalender Absensi Karyawan**: Grid visual bulanan dengan indikator warna harian: Hijau (Hadir tepat waktu), Kuning (Terlambat), Merah (Mangkir/Bolos), Biru (Cuti Resmi), Abu-abu (Libur/Off Day).
    *   **Profil Penalty Point**: Bagian di halaman data staf yang menampilkan grafik akumulasi poin bulan berjalan dan nilai denda rupiah estimasi.
*   **Studi Kasus Nyata (Real Case Scenario - Akumulasi Pelanggaran Absensi)**:
    *   **Karyawan**:
        *   Nama: **Adi Pratama** (Sales Representative Cabang Jakarta Pusat)
        *   Periode Gaji: 1 Juni s.d 30 Juni 2026 (26 Hari Kerja Wajib)
    *   **Catatan Log Kehadiran Riil**:
        *   Adi tercatat masuk kerja tepat waktu sebanyak 21 hari, mengambil cuti tahunan resmi 2 hari.
        *   Adi tercatat terlambat masuk (check-in jam 09:20, 09:25, dan 09:30) sebanyak **3 kali**.
        *   Adi tercatat mangkir/tidak absen sama sekali tanpa keterangan sebanyak **1 kali** (pada 12 Juni 2026).
    *   **Hitung Akumulasi Poin Penalty oleh Sistem**:
        1. Poin Terlambat = $3 \text{ kejadian} \times 20 \text{ poin} = \mathbf{60 \text{ poin}}$.
        2. Poin Mangkir = $1 \text{ hari mangkir} \times 100 \text{ poin} = \mathbf{100 \text{ poin}}$.
        3. Total Poin Penalty Adi = $60 \text{ poin} + 100 \text{ poin} = \mathbf{160 \text{ poin}}$.
        4. Nominal Potongan Rupiah = $160 \text{ poin} \times \text{Rp2.000} = \mathbf{Rp320.000}$.
    *   **Output Integrasi Payroll**:
        - Saat HR memproses draf slip gaji Adi Pratama, kolom *Potongan Denda Absensi* otomatis terisi nilai **Rp320.000** (mengurangi total gaji bruto Adi).

---

### C. Fitur: Request Absensi Backdate & Kehadiran Foto Geotagging
*   **Fokus Kerja**: Mengakomodasi kebutuhan koreksi kehadiran karyawan akibat lupa melakukan fingerprint atau saat ditugaskan di luar area toko (dinas luar) menggunakan absensi berbasis foto selfie & deteksi koordinat GPS (geotagging), dengan mewajibkan alur persetujuan (*approval*) berjenjang oleh Owner/Supervisor.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Pengajuan Backdate**: Karyawan membuka dashboard mandiri dan mengajukan "Request Backdate" dengan menginput Tanggal Kejadian, Jam Masuk/Pulang yang terlewat, serta kolom alasan logis (misal: mesin fingerprint mati / mati lampu).
    2. **Pengajuan Dinas Luar (Absen Foto & Geotagging)**:
       - Karyawan melakukan absen masuk melalui smartphone ketika ditugaskan di luar cabang.
       - Kamera smartphone mengambil foto selfie sebagai bukti visual (verifikasi wajah).
       - GPS mendeteksi koordinat latitude & longitude secara real-time. Koordinat dicocokkan dengan radius toleransi lokasi tujuan dinas (misal maks 100 meter dari titik lokasi klien/gudang logistik).
    3. **Alur Validasi & Approval**: Permintaan masuk ke antrean persetujuan Supervisor/Owner. Sebelum disetujui, log kehadiran di sistem belum terhitung (masih berstatus *Pending*). Setelah disetujui, status berubah menjadi *Approved* dan data absensi masuk ke tabel rekap bulanan secara resmi.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Request Kehadiran (Mobile UX)**: Input form tanggal, dropdown shift, input waktu, kolom alasan, tombol upload foto, dan widget peta kecil penunjuk titik lokasi GPS saat check-in.
    *   **Halaman Persetujuan HR (Desktop Admin)**: Daftar antrean pengajuan backdate/absen foto karyawan lengkap dengan kolom Nama, Cabang, Tipe Request, Detail Waktu, Peta Geotagging, Foto Selfie, Alasan, dan tombol aksi "Setujui" / "Tolak".
*   **Studi Kasus Nyata (Real Case Scenario - Absen Backdate Lupa Fingerprint & Dinas Luar)**:
    *   **Kasus 1: Request Backdate Lupa Absen**:
        *   Staf Ritel **Adi Pratama** lupa melakukan fingerprint pulang karena tergesa-gesa pada tanggal 15 Juni 2026.
        *   Keesokan harinya, Adi mengajukan Backdate Request: Tanggal: 15 Juni 2026, Jam Pulang: 18:05, Alasan: *"Lupa fingerprint karena melayani pelanggan transaksi besar di jam tutup toko"*.
        *   Supervisor melihat pengajuan tersebut, memvalidasi CCTV toko/invoice penjualan pada jam tersebut, lalu mengeklik "Setujui". Hari kerja Adi pada tanggal 15 Juni dicatat penuh (terhindar dari denda mangkir Rp150.000).
    *   **Kasus 2: Absen Dinas Luar Menggunakan Foto & GPS**:
        *   Sales Rep **Budi Setiawan** ditugaskan mengirim barang dan melakukan demo gitar Gibson di sekolah musik rekanan di BSD pada 18 Juni 2026.
        *   Pukul 09:00, Budi membuka aplikasi, mengambil foto selfie di depan sekolah musik, dan GPS mendeteksi koordinatnya di lokasi BSD.
        *   Sistem memverifikasi foto dan mencocokkan koordinat GPS. Pengajuan dinas luar disetujui otomatis oleh sistem, menandai kehadiran Budi hari itu sebagai "Hadir (Dinas Luar)".

---

### D. Fitur: Template KPI Staf & Dashboard Karyawan
*   **Fokus Kerja**: Menyediakan dashboard mandiri bagi masing-masing karyawan/sales untuk memantau performa kerja secara transparan, menghitung pencapaian Key Performance Indicator (KPI) berdasarkan metrik target penjualan pribadi/cabang, melacak komisi penjualan bertingkat yang telah diraih, serta melihat jatah sisa cuti dan kehadiran harian.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Template KPI Dinamis**: Owner menetapkan bobot metrik KPI untuk masing-masing jabatan di database:
       - Sales Representative: Target Omset Penjualan (Bobot 50%), Average Transaction Value/ATV (Bobot 30%), dan Ketepatan Kehadiran (Bobot 20%).
        - Setiap pencapaian KPI akhir bulan dihubungkan langsung dengan insentif/bonus bulanan (e.g. Pencapaian KPI > 90% membuka hak bonus prestasi Rp1.000.000).
    2. **Kalkulasi Target & Komisi Real-Time**:
       - Sistem POS mendeteksi kode sales yang bertransaksi dan langsung memperbarui diagram progres pencapaian target penjualan bulanan di dashboard personal sales yang bersangkutan.
       - Target Bulanan diturunkan menjadi Target Harian otomatis berdasarkan sisa hari kerja aktif bulanan (misal target bulanan Rp50.000.000 dibagi 26 hari kerja = target harian Rp1.923.000) untuk mempermudah pemantauan harian.
       - Sistem menghitung komisi berjalan berdasarkan skema bertingkat (flat 1% jika di bawah target, tambahan 1,5% untuk selisih di atas target) dan menampilkan progress sisa target yang dibutuhkan untuk "unlock" tier komisi berikutnya.
    3. **Indikator Absensi & Info Bar**:
       - Dashboard menampilkan bar ringkasan kehadiran: Jumlah Hari Hadir, Terlambat, Mangkir, dan Cuti/Off Day yang telah diambil.
       - Jika jumlah Off Day yang diambil melebihi batas hak bulanan (misal jatah cuti 12 hari/tahun, terpakai melebihi proporsi bulanan), bar absensi berubah warna menjadi **merah** sebagai peringatan otomatis denda penalty.
    4. **Produk Fokus Bulan Ini (Insentif Khusus)**:
       - Owner menetapkan beberapa produk tertentu sebagai "Produk Fokus" di sistem (misal gitar akustik brand lokal premium yang stoknya sedang menumpuk di gudang atau memiliki margin keuntungan tinggi).
       - Setiap kali sales berhasil menjual 1 unit Produk Fokus ini di POS, sales berhak mendapatkan insentif tunai tambahan tetap (flat cash incentive) di luar komisi standar (misal Rp50.000 per unit gitar).
       - Daftar Produk Fokus terupdate otomatis pada dashboard karyawan lengkap dengan status sisa target kuota penjualan dan besaran nominal bonus per produk.
    5. **Grafik Performa Sales (Tren Omset 1 Tahun)**:
       - Menyediakan visualisasi chart garis (*line chart*) kontribusi omset pribadi karyawan selama 12 bulan terakhir untuk menganalisis perkembangan efektivitas kerja dari bulan ke bulan.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Employee Portal Dashboard (Mobile Responsive)**:
        - *Widget Penjualan (Header)*: Progress bar melingkar (gauge chart) yang menampilkan persentase pencapaian target penjualan bulanan (misal: "Rp45.000.000 / Rp50.000.000 - 90%") serta label kecil target harian berjalan ("Target Hari Ini: Rp1.923.000").
        - *Kartu Informasi Finansial*: Menampilkan estimasi nominal komisi terkumpul berjalan (Rp) dan jumlah penjualan tersisa untuk masuk ke tier komisi 1.5%.
        - *Attendance Bar*: Grafik horizontal berwarna hijau-kuning-merah yang merepresentasikan log kehadiran dan off-day.
        - *Widget Leaderboard*: Menampilkan avatar Top 3 Sales bulan berjalan untuk memacu kompetisi sehat antar-karyawan.
        - *Widget Produk Fokus*: Kartu list produk berisi Nama Barang, Foto, Besaran Insentif Tambahan (Rp/unit), Target Penjualan Karyawan, dan Qty terjual (misal: "Gitar Cort AD810 - Insentif Rp50.000/Unit - Terjual: 4/5 unit").
        - *Grafik Tren Omset Pribadi*: Visualisasi chart garis performa omset penjualan sales bersangkutan selama 1 tahun terakhir.
*   **Studi Kasus Nyata (Real Case Scenario - Pemantauan Target, Fokus Produk, & Unlock Tier Komisi oleh Sales)**:
    *   **Karyawan**: Adi Pratama (Sales Rep)
    *   **Kondisi Awal (Tengah Bulan - 15 Juni 2026)**:
        *   Target Omset Bulanan Adi: **Rp50.000.000**.
        *   Omset Penjualan Terkumpul Sementara (dari transaksi POS): **Rp40.000.000** (80% tercapai).
        *   Komisi berjalan terkumpul: Rp400.000 (Asumsi flat 1% dari Rp40.000.000).
    *   **Fungsi Interaksi Dashboard**:
        - Dashboard menampilkan pesan interaktif: *"Kurang Rp10.000.000 lagi untuk mencapai target Anda dan meng-unlock komisi 1,5% untuk nominal kelebihan target!"*
        - Menampilkan info bar absensi: Hadir: 12 hari, Terlambat: 2 kali, Cuti diambil: 1 hari (Indikator warna bar: Hijau Aman).
    *   **Akhir Bulan (30 Juni 2026)**:
        - Adi berhasil menjual tambahan gitar senilai Rp20.000.000 (Total penjualan akhir: Rp60.000.000).
        - Selama bulan berjalan, Adi juga tercatat sukses menjual **5 unit** *Gitar Cort AD810* yang masuk kategori **Produk Fokus Bulan Ini** (Insentif: Rp50.000/unit). Dashboard menampilkan rincian insentif tambahan Produk Fokus sebesar **Rp250.000** (Rp50.000 $\times$ 5 unit).
        - Dashboard otomatis memperbarui status target Adi menjadi **120% (Tercapai)** dan melabelinya dengan status bonus komisi tier atas sebesar total **Rp650.000** (Rp500.000 + Rp150.000) ditambah insentif produk fokus Rp250.000 (Total komisi/insentif = Rp900.000).
        - Sistem menetapkan skor KPI kehadiran Adi di angka 95%, memicu bonus performa KPI senilai Rp1.000.000 pada slip gaji akhir bulannya.

---

### E. Fitur: Manajemen Kasbon & Pengajuan Overtime Karyawan
*   **Fokus Kerja**: Mengelola pengajuan pinjaman kasbon (*Cash Advance*) karyawan, menetapkan batas limit pinjaman dinamis, menyusun tenor cicilan bulanan, mencatat pengajuan jam kerja lembur (*Overtime*), memverifikasi lembur dengan log absensi keluar, serta mengotomatisasi pemotongan kasbon dan penambahan upah lembur pada slip gaji.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Pengajuan Kasbon & Limit Kontrol**:
       - Karyawan mengajukan nominal kasbon via portal karyawan.
       - Sistem menerapkan aturan limit: Maksimal nominal kasbon aktif tidak boleh melebihi **50% Gaji Pokok** karyawan untuk meminimalkan risiko gagal bayar.
       - Pengajuan memerlukan approval bertingkat (Supervisor Cabang $\rightarrow$ Owner/HR Manager).
       - Setelah disetujui, dana dicairkan via kas toko, dan saldo Piutang Kasbon Karyawan (`COA 1135-02`) bertambah di sub-ledger pembantu piutang karyawan.
       - Karyawan memilih tenor cicilan (misal 3 bulan/3 kali potong gaji).
    2. **Pengajuan & Validasi Overtime**:
       - Karyawan menginput klaim lembur: Tanggal, Jam Mulai, Jam Selesai, dan Deskripsi Aktivitas Kerja Lembur.
       - Sistem memvalidasi kesesuaian jam lembur dengan data log keluar mesin fingerprint/GPS foto cabang aktif untuk memastikan keaslian klaim.
       - Pengajuan berstatus *Pending* hingga disetujui (*Approved*) oleh Supervisor Cabang/Owner.
       - Tarif lembur per jam dihitung berdasarkan upah lembur normatif.
    3. **Integrasi Slip Gaji**:
       - Saat slip gaji bulanan diproses, kalkulator payroll menarik cicilan kasbon berjalan sebagai komponen potongan tetap.
       - Kalkulator payroll menarik total jam lembur disetujui (Approved Overtime) bulan berjalan dan mengalikannya dengan tarif lembur sebagai komponen penambah penghasilan.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Request Kasbon & Overtime (Mobile Portal)**: Antarmuka mobile-responsive berisi form pilihan pengajuan (Kasbon/Lembur), isian nominal/durasi jam, kolom alasan, dan log status pengajuan berjalan (`Pending`, `Approved`, `Rejected`).
    *   **Halaman Manajemen HR (Desktop)**: Dashboard verifikasi log kasbon & lembur dengan filter status, tombol tolak/setujui, dan rekapitulasi tenor sisa cicilan aktif karyawan.
*   **Studi Kasus Nyata (Real Case Scenario - Pengajuan Kasbon & Klaim Lembur Akhir Shift)**:
    *   **Kasus 1: Pengajuan Kasbon Berjalan**:
        *   Staf kasir **Maya Anggraini** (Gaji Pokok: Rp3.000.000) mengajukan kasbon darurat sebesar **Rp1.200.000** dengan tenor 3 bulan (Cicilan Rp400.000/bulan).
        *   Sistem memvalidasi nominal Rp1.200.000 $\le$ 50% Gaji Pokok (Rp1.500.000), sehingga status valid dan diteruskan ke Owner.
        *   Owner menyetujui pengajuan. Kasir Depok mengeluarkan uang kas toko senilai Rp1.200.000.
        *   **Jurnal Akuntansi (Saat Pencairan Uang Kasbon Karyawan)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 1135-02 | Piutang Kasbon Karyawan (Maya) | 1.200.000 | |
          | 1111-02 | Kas Laci POS - Depok | | 1.200.000 |

    *   **Kasus 2: Klaim Lembur Acara Event Toko**:
        *   Sales Rep **Adi Pratama** ditugaskan lembur melayani pembongkaran display gitar baru pada tanggal 20 Juni 2026. Jadwal shift reguler selesai pukul 17:00. Adi bekerja lembur hingga pukul 20:00 (3 jam lembur).
        *   Adi mengajukan klaim lembur: Mulai: 17:00, Selesai: 20:00.
        *   Sistem memverifikasi jam check-out fingerprint Adi tercatat pukul 20:05 (Valid).
        *   Supervisor menyetujui klaim lembur 3 jam tersebut. Tarif lembur disepakati Rp30.000 per jam. Total upah lembur Adi sebesar **Rp90.000** otomatis ditambahkan ke slip gaji akhir bulan Juni Adi sebagai "Tunjangan Lembur".

---

## 4. Modul Akuntansi & Laporan Keuangan

### A. Fitur: Laporan Laba Rugi (Income Statement) Otomatis
*   **Fokus Kerja**: Menyediakan laporan laba rugi berkala (bulanan/tahunan) secara otomatis dan real-time baik untuk cabang individu maupun konsolidasi seluruh cabang, guna menganalisis kinerja profitabilitas bersih bisnis Diego Music Store.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Ekstraksi Data Akuntansi**: Sistem menarik seluruh saldo dari Chart of Accounts (COA) yang diklasifikasikan ke dalam kelompok tipe akun: Pendapatan (*Revenue*), Beban Pokok Penjualan (*Cost of Goods Sold/COGS*), Beban Operasional (*Operating Expenses*), serta Pendapatan & Beban Lain-lain (*Other Income/Expenses*).
    2. **Pemberlakuan Filter Cabang**:
       - *Filter Cabang Tertentu*: Mengagregasi saldo jurnal umum yang memiliki tag cabang terpilih saja.
       - *Konsolidasi Seluruh Cabang*: Mengagregasi dan mengkonsolidasikan data keuangan dari seluruh cabang utama dan cabang fisik tanpa batasan tag.
    3. **Penghitungan Laba Kotor (Gross Profit)**:
       $$\text{Laba Kotor} = \text{Total Pendapatan (POS Ritel + Servis)} - \text{Total Beban Pokok Penjualan (HPP)}$$
    4. **Penghitungan Laba Bersih Sebelum Pajak (Net Income)**:
       $$\text{Laba Bersih} = \text{Laba Kotor} - \text{Total Beban Operasional (Payroll + Petty Cash + Depresiasi)} + \text{Net Lain-lain (Denda - Selisih Kas)}$$
    5. **Exporting**: Pengguna (Owner/Admin) dapat mengeklik tombol export untuk mengunduh laporan berformat PDF/Excel yang siap dipresentasikan.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Filter Header**: Area atas berisi Dropdown Pilihan Cabang (Jakarta Pusat / Depok / Seluruh Cabang) dan Datepicker Range Tanggal Laporan.
    *   **Layout Laporan Standar Akuntansi**:
        - Baris-baris terstruktur bertingkat (*indented hierarchy*) dengan pemisah garis horizontal tegas.
        - Angka negatif ditampilkan dalam tanda kurung, misal: `(Rp15.000.000)`.
    *   **Aksi Dokumen**: Tombol cetak langsung dan ikon unduh PDF/Excel di pojok kanan atas laporan.
*   **Studi Kasus Nyata (Real Case Scenario - Penyusunan Laba Rugi Juni 2026)**:
    *   **Parameter Laporan**:
        *   Periode Laporan: 1 Juni 2026 s.d 30 Juni 2026
        *   Tipe Cabang Terpilih: **Konsolidasi Seluruh Cabang**
    *   **Data Saldo COA yang Diekstrak**:
        1. **Kelompok Pendapatan (Revenue)**:
           - Pendapatan Ritel Jakarta Pusat (COA `4111-01`): Rp120.000.000
           - Pendapatan Ritel Depok (COA `4111-02`): Rp80.000.000
           - Pendapatan Jasa Servis Jakarta Pusat (COA `4112-01`): Rp15.000.000
           - Pendapatan Jasa Servis Depok (COA `4112-02`): Rp8.000.000
           - *Total Pendapatan (A)* = **Rp223.000.000**.
        2. **Kelompok Beban Pokok Penjualan (COGS / HPP)**:
           - HPP Penjualan Ritel Jakarta Pusat (COA `5111-01`): Rp85.000.000
           - HPP Penjualan Ritel Depok (COA `5111-02`): Rp55.000.000
           - *Total HPP (B)* = **Rp140.000.000**.
        3. **Kelompok Beban Operasional (Operating Expenses)**:
           - Beban Gaji & Komisi Staff (COA `5211-01` & `5211-02`): Rp35.000.000
           - Beban Petty Cash Cabang Jakarta Pusat (COA `5311-01`): Rp6.000.000 (biaya operasional bulanan toko)
           - Beban Petty Cash Cabang Depok (COA `5311-02`): Rp4.000.000 (biaya operasional bulanan toko)
           - Beban Depresiasi/Penyusutan Aset Tetap Toko (COA `5411-01`): Rp2.500.000
           - *Total Beban Operasional (C)* = **Rp47.500.000**.
        4. **Kelompok Pendapatan & Beban Lain-lain (Other Income / Expenses)**:
           - Pendapatan Denda Kehadiran Karyawan (COA `2118-01`): +Rp720.000
           - Beban Selisih Kurang Kas Sesi POS (COA `5999-01`): (Rp80.000)
           - *Total Lain-lain Bersih (D)* = $\text{Rp720.000} - \text{Rp80.000} = \mathbf{+Rp640.000}$.
    *   **Kalkulasi Angka Laporan**:
        - Laba Kotor (Gross Profit) = $\text{Rp223.000.000 (A)} - \text{Rp140.000.000 (B)} = \mathbf{Rp83.000.000}$.
        - Total Beban Operasional = **Rp47.500.000 (C)**.
        - Laba Bersih Operasional = $\text{Rp83.000.000} - \text{Rp47.500.000} = \mathbf{Rp35.500.000}$.
        - Laba Bersih Akhir Sebelum Pajak = $\text{Rp35.500.000} + \text{Rp640.000 (D)} = \mathbf{Rp36.140.000}$.
    *   **Output Tampilan Laporan Laba Rugi Hasil Olahan Sistem**:
        
        ```text
        ========================================================================
        DIEGO MUSIC STORE - LAPORAN LABA RUGI (KONSOLIDASI)
        Periode: 01 Jun 2026 - 30 Jun 2026
        ========================================================================

        PENDAPATAN USAHA
          Pendapatan Penjualan Ritel                      200.000.000
          Pendapatan Jasa Servis                           23.000.000
        ------------------------------------------------------------------------
        TOTAL PENDAPATAN                                                223.000.000

        BEBAN POKOK PENJUALAN
          Harga Pokok Penjualan (HPP) Ritel              (140.000.000)
        ------------------------------------------------------------------------
        TOTAL BEBAN POKOK PENJUALAN                                    (140.000.000)

        ------------------------------------------------------------------------
        LABA KOTOR (GROSS PROFIT)                                        83.000.000

        BEBAN OPERASIONAL
          Beban Gaji & Komisi Karyawan                     35.000.000
          Beban Petty Cash Cabang Jakarta Pusat             6.000.000
          Beban Petty Cash Cabang Depok                     4.000.000
          Beban Depresiasi Aset Tetap                       2.500.000
        ------------------------------------------------------------------------
        TOTAL BEBAN OPERASIONAL                                         (47.500.000)

        PENDAPATAN & BEBAN LAIN-LAIN
          Pendapatan Denda Kehadiran Karyawan                 720.000
          Beban Selisih Kurang Kas Sesi POS                   (80.000)
        ------------------------------------------------------------------------
        TOTAL LAIN-LAIN BERSIH                                              640.000

        ------------------------------------------------------------------------
        LABA BERSIH SEBELUM PAJAK                                        36.140.000
        ========================================================================
        ```

### B. Fitur: Manajemen Aset Tetap & Depresiasi
*   **Fokus Kerja**: Mengelola inventarisasi aset tetap kantor/toko (peralatan, kendaraan, AC, display instrumen), mengotomatiskan perhitungan beban penyusutan bulanan menggunakan metode garis lurus (*straight-line method*), dan memposting jurnal penyusutan bulanan secara terjadwal.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Registrasi Aset Tetap**: Admin mencatat aset baru dengan menginput Tanggal Perolehan, Harga Perolehan, Estimasi Umur Ekonomis (dalam bulan/tahun), dan Nilai Sisa (Residual Value).
    2. **Kalkulasi Garis Lurus**: Sistem menghitung penyusutan bulanan flat menggunakan rumus:
       $$\text{Beban Penyusutan Bulanan} = \frac{\text{Harga Perolehan} - \text{Nilai Sisa}}{\text{Umur Ekonomis (Bulan)}}$$
    3. **Posting Depresiasi Otomatis**: Setiap akhir bulan, berbarengan dengan proses tutup buku bulanan, sistem memicu pembentukan jurnal penyesuaian otomatis untuk mendebit Beban Penyusutan Aset dan mengkredit Akumulasi Penyusutan Aset.
    4. **Pelepasan / Disposisi Aset**: Saat aset dijual, rusak total, atau dihibahkan, sistem menghitung Laba/Rugi Pelepasan Aset berdasarkan selisih harga jual disposisi vs Nilai Buku berjalan ($\text{Nilai Buku} = \text{Harga Perolehan} - \text{Akumulasi Penyusutan}$).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Tabel Register Aset Tetap**: Daftar aset dengan kolom Kode Aset, Nama Aset, Lokasi Cabang, Harga Perolehan, Total Akumulasi Penyusutan, Nilai Buku, dan Status.
    *   **Form Input Aset Tetap**: Form isian data spesifikasi aset beserta kolom hitungan amortisasi/penyusutan.
*   **Studi Kasus Nyata (Real Case Scenario - Penyusutan Komputer Kasir Toko)**:
    *   **Data Pembelian Aset**:
        *   Nama Aset: Paket Komputer Kasir & Barcode Scanner Depok (Kode Aset: `AST-DEP-COMP-001`)
        *   Tanggal Perolehan: 1 Januari 2026
        *   Harga Perolehan (Awal): **Rp12.000.000**
        *   Umur Ekonomis: **3 Tahun** (36 Bulan)
        *   Estimasi Nilai Residu/Sisa: **Rp1.200.000**
    *   **Alur Hitung Depresiasi Bulanan**:
        *   Beban Depresiasi per Bulan = $\frac{\text{Rp12.000.000} - \text{Rp1.200.000}}{36 \text{ bulan}} = \frac{\text{Rp10.800.000}}{36} = \mathbf{Rp300.000}$ per bulan.
    *   **Output Finansial & Jurnal Akuntansi (Posting Periode Juni 2026)**:
        - Nilai Buku Aset setelah disusutkan 6 bulan (Januari s.d Juni) = $\text{Rp12.000.000} - (6 \text{ bulan} \times \text{Rp300.000}) = \mathbf{Rp10.200.000}$.
        - **Jurnal Umum Akuntansi (Otomatis saat Tutup Buku Bulanan Akhir Juni 2026)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 5411-01 | Beban Penyusutan Peralatan Toko | 300.000 | |
          | 1211-99 | Akumulasi Penyusutan Peralatan Toko | | 300.000 |

---

### C. Fitur: Stok Opname & Penawaran Harga (Quotation)
*   **Fokus Kerja**: Mengelola proses pencocokan stok fisik barang di gudang/toko dengan stok yang tercatat di sistem (Stok Opname), menjurnal otomatis beban selisih kerugian/keuntungan persediaan, serta menyusun draf penawaran harga (*Quotation*) resmi kepada pelanggan instansi/proyek dengan alur konversi instan ke invoice komersial.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Stok Opname & Adjustment**:
       - Admin Gudang membuat dokumen *Stok Opname* per cabang. Sistem mengunci sementara mutasi stok untuk barang yang sedang dihitung agar tidak terjadi bias data.
       - Admin memasukkan nilai Stok Fisik riil yang dihitung. Sistem menghitung selisih (`Selisih = Stok Fisik - Stok Sistem`).
       - *Jika Selisih Kurang (Negatif)*: Sistem mengurangi stok di database dan memposting jurnal penyesuaian otomatis untuk mendebit akun Beban Selisih Stok dan mengkredit Persediaan Barang Dagang.
       - *Jika Selisih Lebih (Positif)*: Sistem menambah stok di database dan mengkredit Pendapatan Selisih Stok.
    2. **Pembuatan Penawaran Harga (Quotation)**:
       - Sales Admin membuat penawaran harga formal ke pelanggan instansi (seperti sekolah musik atau penyelenggara event) dengan menginput item barang, harga penawaran khusus, diskon proyek, dan masa berlaku dokumen.
       - Status Quotation dikelola: `Draft` $\rightarrow$ `Sent` $\rightarrow$ `Approved` / `Rejected`.
       - Saat status disetujui (`Approved`), kasir dapat mengonversi dokumen tersebut langsung menjadi invoice penjualan POS ritel/kredit tanpa perlu input ulang item barang.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Input Stok Opname**: Tabel entri isian Qty Fisik bersanding dengan Qty Sistem, lengkap dengan kolom alasan selisih (misal: barang rusak/pecah, hilang).
    *   **Daftar Penawaran Harga (Quotation Grid)**: Tabel monitoring penawaran dengan kolom No. Penawaran, Nama Pelanggan/Instansi, Tanggal Pembuatan, Nilai Penawaran, Status Approval, dan tombol aksi "Convert to Invoice".
*   **Studi Kasus Nyata (Real Case Scenario - Stok Opname Selisih Kurang & Quotation Event)**:
    *   **Kasus 1: Stok Opname Selisih Kurang Gitar**:
        *   Barang: Gitar *Yamaha FS800* (HPP: Rp2.000.000). Stok sistem mencatat **3 unit**.
        *   Saat opname fisik di gudang Depok, hanya ditemukan **2 unit** (1 unit pecah retak akibat kecelakaan pemindahan gudang).
        *   Staf mencatat Qty Fisik = 2, Selisih = -1 unit, Alasan: *"Barang Rusak Cacat / Write-off"*.
        *   **Jurnal Umum Akuntansi (Otomatis saat Posting Stok Opname)**:
          
          | Kode Akun | Nama Akun (COA) | Debit (Rp) | Kredit (Rp) |
          | :--- | :--- | :--- | :--- |
          | 5511-05 | Beban Kerugian Selisih Persediaan | 2.000.000 | |
          | 1131-01 | Persediaan Barang Dagang - Depok | | 2.000.000 |

    *   **Kasus 2: Penawaran Harga (Quotation) untuk Sekolah Musik**:
        *   Sales membuat penawaran 5 unit Gitar Akustik Fender CD-60S ke *Sekolah Musik Harmoni* dengan harga khusus Rp2.200.000 per unit (Harga ritel normal Rp2.500.000).
        *   Nomor Quotation: `QTN-DEP-20260621-002` dengan total nilai **Rp11.000.000**.
        *   Sekolah Musik menyetujui penawaran tersebut. Kasir di POS Cabang Depok memanggil nomor `QTN-DEP-20260621-002`, sistem menarik detail barang ke POS, memproses pembayaran DP Rp2.000.000 dan sisa pelunasan transfer, serta otomatis menerbitkan invoice penjualan resmi.

---

### D. Fitur: Tutup Buku Bulanan, Jurnal Umum, & Neraca (Balance Sheet)
*   **Fokus Kerja**: Menyediakan fungsi pembuatan entri jurnal umum manual untuk transaksi non-kasir, memproses penguncian transaksi keuangan bulanan (Tutup Buku Bulanan), serta menyusun Laporan Neraca (*Balance Sheet*) secara otomatis guna menyajikan posisi Keuangan (Aset, Kewajiban, Modal) Diego Music Store secara real-time.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Entri Jurnal Umum Manual**: Admin Finance dapat membuat jurnal double-entry manual untuk mencatat transaksi non-operasional (seperti penyetoran modal awal, koreksi akun pembukuan, pembayaran pajak tahunan, dll) dengan syarat nominal Debit dan Kredit wajib seimbang (*balance*).
    2. **Tutup Buku Bulanan (Period Closing)**:
       - Di akhir bulan, setelah seluruh penyusutan aset tetap diposting dan rekonsiliasi kas selesai, Owner/Finance memicu fungsi "Tutup Buku Bulanan".
       - Sistem mengunci (*lock*) seluruh transaksi pada periode bulan tersebut. Seluruh user (termasuk kasir & admin) tidak dapat lagi menambah, mengedit, atau menghapus invoice/jurnal pada periode yang telah dikunci.
    3. **Penyusunan Laporan Neraca (Balance Sheet)**:
       - Sistem mengagregasi saldo akhir dari seluruh kelompok akun untuk periode berjalan:
         - **Aset (Aktiva)**: Aset Lancar (Kas, Bank, Piutang Usaha, Piutang Kasbon, Persediaan Barang) + Aset Tetap (Peralatan, Akumulasi Penyusutan).
         - **Kewajiban (Passiva - Liabilitas)**: Hutang Dagang Supplier, Hutang Uang Muka Pelanggan (Deposit), Hutang Gaji.
         - **Modal (Passiva - Ekuitas)**: Modal Disetor + Laba Ditahan + Laba Tahun Berjalan (diambil dari Laba Bersih Laba Rugi).
       - Sistem memverifikasi bahwa total rumus Neraca wajib seimbang: $\text{Aset} = \text{Kewajiban} + \text{Ekuitas}$.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Jurnal Umum Manual**: Form input baris akun COA dinamis dengan kolom Debit dan Kredit, dilengkapi indikator warna hijau (jika balance) dan merah (jika tidak balance, tombol posting terkunci).
    *   **Tampilan Neraca Keuangan**: Layout laporan standar akuntansi dua kolom berdampingan (*T-Account Style*) atau vertikal bertingkat yang membedakan klasifikasi Aktiva dan Passiva secara tegas.
*   **Studi Kasus Nyata (Real Case Scenario - Neraca Konsolidasi Pasca Tutup Buku Juni 2026)**:
    *   **Tutup Buku Periode Juni 2026**:
        *   Owner memicu Tutup Buku Bulanan per 30 Juni 2026 pukul 23:59.
        *   Sistem mengunci periode transaksi Juni 2026. Semua transaksi yang diinput kemudian hari otomatis masuk ke periode Juli 2026.
    *   **Data Saldo Buku Besar untuk Neraca**:
        *   *Kas & Bank* = Rp120.000.000
        *   *Persediaan Barang Dagang* = Rp450.000.000
        *   *Piutang Dagang* = Rp12.000.000
        *   *Aset Tetap Peralatan* = Rp50.000.000
        *   *Akumulasi Penyusutan* = (Rp5.000.000)
        *   **Total Aset (Aktiva)** = $\text{Rp120.000.000} + \text{Rp450.000.000} + \text{Rp12.000.000} + (\text{Rp50.000.000} - \text{Rp5.000.000}) = \mathbf{Rp627.000.000}$.
        *   *Hutang Dagang Vendor* = Rp150.000.000
        *   *Hutang Deposit Pelanggan* = Rp15.000.000
        *   **Total Kewajiban** = $\text{Rp150.000.000} + \text{Rp15.000.000} = \mathbf{Rp165.000.000}$.
        *   *Modal Awal Disetor* = Rp425.860.000
        *   *Laba Bersih Bulan Berjalan* = Rp36.140.000 (Sesuai Laporan Laba Rugi Juni 2026)
        *   **Total Ekuitas** = $\text{Rp425.860.000} + \text{Rp36.140.000} = \mathbf{Rp462.000.000}$.
        *   **Total Passiva (Kewajiban + Ekuitas)** = $\text{Rp165.000.000} + \text{Rp462.000.000} = \mathbf{Rp627.000.000}$ *(BALANCE dengan Aktiva)*.

---

## 5. Modul CRM & Notifikasi WhatsApp

### A. Fitur: Otomatisasi Tagihan Piutang & Broadcast Promo WhatsApp
*   **Fokus Kerja**: Mengurangi angka piutang macet dengan mengirimkan pesan pengingat tagihan secara otomatis ke WhatsApp pelanggan menjelang tanggal jatuh tempo, serta menyediakan fitur pengiriman pesan siaran massal (*broadcast*) promosi ke basis pelanggan loyal berdasarkan filter tertentu.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Notifikasi Tagihan Otomatis**:
       - Cron Job sistem berjalan setiap hari pukul 08:00 untuk memindai transaksi piutang/booking tempo yang belum lunas.
       - Sistem memfilter tagihan yang berstatus belum lunas dan jatuh temponya kurang dari 3 hari ($\le 3$ hari) atau yang sudah lewat jatuh tempo (*overdue*).
       - Sistem memicu WhatsApp Gateway (menggunakan API Fonnte) untuk mengirimkan chat pesan pengingat tagihan beserta tautan pembayaran digital.
    2. **WhatsApp Struk/Invoice PDF**: Begitu kasir memposting transaksi lunas di POS, sistem secara latar belakang mengonversi invoice menjadi file PDF dan mengirimkannya langsung sebagai lampiran chat WhatsApp ke nomor WA pelanggan yang terdaftar.
    3. **Broadcast Promo Tersegmentasi**: Owner/Marketing dapat membuat campaign broadcast promosi baru:
       - Memilih template pesan promo dari database.
       - Memfilter penerima pesan, misal: hanya dikirimkan ke pelanggan dengan Tipe Member = **Gold Member** (guna meminimalkan biaya kuota pengiriman API).
       - Klik "Kirim Broadcast". Sistem mengirimkan pesan secara berkala (*throttling/antrean delay* 5-10 detik per nomor) agar nomor WhatsApp Gateway tidak terblokir oleh WhatsApp.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Form Pembuat Campaign Broadcast**: Input field nama campaign, area teks isi pesan dengan tag dinamis (misal: `{Nama}`, `{Tipe_Member}`), pemilih filter member, dan tombol kalkulasi perkiraan biaya kuota kirim.
    *   **Tabel Riwayat Pengiriman**: Laporan log WhatsApp yang dikirimkan berisi kolom Waktu, Nomor Tujuan, Isi Pesan, Status Pengiriman (Sent / Failed), dan log error jika gagal terkirim.
*   **Studi Kasus Nyata (Real Case Scenario - Notifikasi Tagihan Piutang Otomatis)**:
    *   **Kondisi Awal**:
        *   Pelanggan: **Budi Santoso** (Gold Member). Sisa Piutang: **Rp2.000.000**.
        *   Jatuh Tempo Tagihan: 24 Juni 2026.
    *   **Pemicu Otomatis (21 Juni 2026 - H-3 Jatuh Tempo)**:
        1. Pukul 08:00, cron job mendeteksi tagihan Budi Santoso akan jatuh tempo dalam waktu 3 hari.
        2. Sistem memicu pengiriman API WhatsApp ke nomor Budi Santoso (`081122334455`).
        3. Budi menerima pesan di WhatsApp:
           > *"Halo Kak Budi Santoso, kami dari Diego Music Store mengingatkan bahwa tagihan Piutang Rp2.000.000 untuk pembelian gitar Anda akan jatuh tempo pada 24 Juni 2026. Anda dapat melakukan pelunasan melalui transfer ke BCA 123-456-789. Terima kasih."*
    *   **Kirim Struk Penjualan**:
        *   Rian Hidayat melakukan pembelian gitar Fender CD-60S Black tunai di POS. Setelah lunas, WhatsApp Rian otomatis menerima pesan: *"Terima kasih Rian Hidayat atas pembelian Anda di Diego Music Store. Berikut kami lampirkan file invoice resmi Anda: [Tautan Invoice PDF]"*.

---

### B. Fitur: Loyalty Member & Skema Poin Belanja
*   **Fokus Kerja**: Mengelola program loyalitas pelanggan melalui otomatisasi pendaftaran member, akumulasi poin belanja secara presisi berdasarkan nominal transaksi POS, serta pemanfaatan poin sebagai metode pembayaran (diskon belanja) atau penentu tingkatan (*tiering*) diskon member.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Registrasi Member Otomatis**: Setiap pelanggan baru yang diinput WhatsApp uniknya di kasir POS secara otomatis terdaftar sebagai member (status default: **Bronze Member**).
    2. **Aturan Akumulasi Poin**:
       - Setiap pembelanjaan dengan kelipatan **Rp50.000** (setelah diskon promo, di luar biaya PPN/ongkir) menghasilkan **1 Poin Belanja**.
       - Angka pecahan di bawah Rp50.000 dibulatkan ke bawah (tidak menghasilkan poin parsial).
       - Poin ditambahkan otomatis ke profil pelanggan setelah transaksi POS berstatus *Posted* (lunas).
    3. **Penukaran/Redemption Poin**:
       - Poin dapat digunakan langsung di POS untuk memotong total tagihan belanja dengan nilai tukar: **1 Poin = Rp1.000**.
       - Kasir dapat memasukkan jumlah poin yang ingin digunakan (maksimal sejumlah total poin terkumpul). Poin yang ditukar langsung didebit dari saldo poin pelanggan.
    4. **Tingkatan (Tiering) Member & Diskon Level**:
       - **Bronze Member** (0 s.d 50 poin): Harga jual retail normal.
       - **Silver Member** (51 s.d 200 poin): Diskon otomatis *flat 2%* untuk seluruh item retail (atau harga tier level Silver).
       - **Gold Member** (Lebih dari 200 poin): Diskon otomatis *flat 5%* untuk seluruh item retail (atau harga tier level Gold).
       - Perubahan tier berjalan otomatis secara real-time saat akumulasi poin bersih pelanggan melewati batas ambang tier.
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Widget Customer POS**: Area di antarmuka kasir yang menampilkan Nama Pelanggan, Nomor WhatsApp, Tier Member (Bronze/Silver/Gold), dan jumlah Saldo Poin Berjalan. Terdapat tombol "Gunakan Poin" yang memicu pop-up input jumlah poin.
*   **Studi Kasus Nyata (Real Case Scenario - Belanja Senilai 3 Juta Rupiah & Tukar Poin)**:
    *   **Kondisi Awal**:
        *   Pelanggan: **Rian Hidayat** (Status: *Silver Member*, Saldo Poin: **30 Poin**).
    *   **Transaksi Belanja**:
        *   Rian membeli gitar akustik senilai **Rp3.000.000** (termasuk PPN).
        *   Rian ingin memotong pembayarannya menggunakan seluruh poin yang dimilikinya (30 Poin).
    *   **Kalkulasi Kasir POS**:
        - Nilai Pemotong Poin = $30 \text{ Poin} \times \text{Rp1.000} = \mathbf{Rp30.000}$.
        - Total Bayar Bersih = $\text{Rp3.000.000} - \text{Rp30.000} = \mathbf{Rp2.970.000}$.
        - Rian membayar lunas Rp2.970.000.
    *   **Perhitungan Poin Baru & Update Tier**:
        - Poin yang didapatkan dari transaksi baru = $\text{Rp2.970.000} \div \text{Rp50.000} = 59,4 \rightarrow \mathbf{59 \text{ Poin}}$.
        - Saldo Poin Akhir Rian = $\text{Saldo Lama (30)} - \text{Ditukar (30)} + \text{Dapat Baru (59)} = \mathbf{59 \text{ Poin}}$.
        - Total Akumulasi Poin Sepanjang Waktu Rian melewati ambang batas 50 poin. Sistem secara otomatis memperbarui status Rian tetap aman sebagai **Silver Member** dan mendekati tier Gold.

---

## 6. Modul Dashboard Owner (Super Admin)

### A. Fitur: Dashboard Analytics & Pareto 80/20
*   **Fokus Kerja**: Menyajikan ringkasan visual performa finansial multi-cabang, analisis efisiensi perputaran persediaan menggunakan metode Pareto 80/20, serta papan peringkat (*leaderboard*) kontribusi staf penjualan guna membantu pengambilan keputusan taktis Owner.
*   **Alur Kerja Utama & Logika Bisnis**:
    1. **Agregasi Data Real-Time**: Sistem menarik seluruh data omset penjualan ritel & jasa, biaya operasional cabang (petty cash), status tagihan piutang, dan log absensi dari seluruh cabang secara terpusat.
    2. **Kalkulasi Pareto 80/20 Produk**:
       - Sistem memfilter seluruh produk terjual pada periode berjalan.
       - Sistem mengurutkan produk dari kontribusi nominal penjualan tertinggi ke terendah.
       - Sistem menghitung persentase kumulatif penjualan untuk setiap produk.
       - Produk yang berada di dalam ambang batas kumulatif $\le 80\%$ ditandai sebagai **Kelas A (Fast Moving / Pareto 80%)**, sedangkan sisanya dilabeli sebagai **Kelas B (Slow Moving)**.
    3. **Leaderboard & Komisi Sales**: Sistem mengagregasi omset yang dihasilkan masing-masing Sales Representative (berdasarkan atribusi sales rep di POS) dan menampilkan peringkat kontribusi beserta status pencapaian target kerja mereka.
    4. **Sistem Pengingat Jatuh Tempo (Alert System)**:
       - Memindai database tagihan piutang pelanggan dan hutang vendor.
       - Menghitung sisa hari menuju tanggal jatuh tempo (*due date*).
       - Menampilkan notifikasi merah mencolok jika ada tagihan yang melewati batas tanggal (*overdue*) atau akan jatuh tempo dalam jangka waktu dekat (misal $\le 3$ hari).
*   **Desain Tampilan & Elemen UI/UX**:
    *   **Kartu KPI Ringkasan (Top Cards)**: Menampilkan total Omset Konsolidasi, Laba Kotor, Nilai Piutang Aktif, dan Sisa Saldo Kas Bank. Dilengkapi indikator persentase tren naik/turun berwarna hijau/merah dibandingkan bulan sebelumnya.
    *   **Chart Visual Tren Penjualan**: Grafik garis (*line chart*) interaktif yang membandingkan performa omset bulanan antarcabang secara berdampingan.
    *   **Tabel Analisis Kontribusi Pareto**: Tabel interaktif berlabel badge Kelas A/B dengan kolom: No, SKU, Nama Barang, Total Terjual, Kontribusi Omset (Rp), Kumulatif Omset (%), dan Status Pareto.
    *   **Widget Notifikasi Alarm (Alert Feed)**: Panel notifikasi melayang di kanan atas yang menampilkan alarm merah untuk tagihan overdue dan oranye untuk tagihan jatuh tempo dekat.
*   **Studi Kasus Nyata (Real Case Scenario - Analisis Dashboard Owner Akhir Bulan)**:
    *   **Parameter Filter**:
        *   Periode Analisis: 1 Juni s.d 30 Juni 2026
        *   Cabang: Seluruh Cabang (Konsolidasi)
    *   **Kondisi Finansial Ringkas**:
        *   Total Omset Tergabung: Rp223.000.000
        *   Laba Kotor Tergabung: Rp83.000.000
    *   **Hasil Analisis Pareto 80/20 Persediaan**:
        *   Dari total 50 SKU barang aktif yang terjual, sistem menyortir kontribusi omset per produk dan menemukan bahwa **3 SKU** teratas menyumbang Rp178.400.000 (80% dari total omset):
          1. Gitar *Gibson Les Paul Cherry Sunburst* (SKU `GBS-LP-7788`) = Rp120.000.000 (Kontribusi: 53.8%)
          2. Gitar *Yamaha Pacifica 112V Sunburst* (SKU `YMH-PAC-112V-SB`) = Rp40.500.000 (Kontribusi: 18.2%)
          3. Gitar *Fender CD-60S Black* (SKU `FND-CD60S-BLK`) = Rp17.900.000 (Kontribusi: 8.0%)
        *   Sistem secara otomatis melabeli ketiga produk di atas dengan badge hijau **[ Kelas A (Fast Moving) ]** di dashboard, dan merekomendasikan Owner untuk menetapkan stok minimum aman (*safety stock*) sebesar 5 unit per cabang agar tidak kehabisan barang terlaris ini.
    *   **Papan Peringkat (Leaderboard) Sales**:
        1. **Adi Pratama**: Omset tercapai Rp60.000.000 (Target Rp50M, Persentase 120%, Peringkat 1, Komisi Rp650.000).
        2. **Budi Setiawan**: Omset tercapai Rp30.000.000 (Target Rp40M, Persentase 75%, Peringkat 2, Komisi Rp300.000).
    *   **Status Notifikasi Kritis (Due Date Reminder)**:
        *   Sistem mendeteksi dan menampilkan 2 alert di panel kanan dashboard Owner:
          - **[ALARM MERAH - OVERDUE 5 HARI]** Piutang atas nama pelanggan *Budi Santoso* (Member Gold) sebesar Rp5.000.000 (Sisa pelunasan les musik/transaksi kredit) telah melewati batas jatuh tempo.
          - **[ALARM ORANYE - DUE IN 3 DAYS]** Hutang pembelian kepada vendor *PT Yamaha Musik Indonesia* sebesar Rp25.000.000 (Dari DO barang masuk) akan jatuh tempo pada 24 Juni 2026.

---
