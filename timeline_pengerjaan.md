# Rencana & Timeline Pengerjaan ERP Diego Music Store
*Dokumen Rencana Kerja dan Distribusi Fitur Berdasarkan Kompleksitas*

---

> [!NOTE]
> **Metrik Utama Proyek:**
> - **Total Durasi:** 2 Bulan 21 Hari (81 Hari Kalender)
> - **Alokasi Kerja:** 74 Hari Pengembangan (Development) + 7 Hari Pengujian (Testing & UAT)
> - **Metodologi:** Agile Scrum dengan pembagian 5 Sprint Pengembangan dan 1 Sesi Rilis/Testing Akhir.

---

## 1. Visualisasi Timeline (High-Level Gantt Chart)

Berikut adalah visualisasi alur pengerjaan ERP Diego Music Store menggunakan diagram Gantt setelah dilakukan reprioritisasi:

```mermaid
gantt
    title Timeline ERP Diego Music Store (81 Hari)
    dateFormat  YYYY-MM-DD
    axisFormat  W%W
    
    section Sprint 1: Master Data (Mudah)
    DB Schema & Core Setup       :active, sp1_1, 2026-06-25, 4d
    Master Data CRUD & Cabang    :active, sp1_2, after sp1_1, 5d
    Basic CRM & Utility Settings :active, sp1_3, after sp1_2, 3d
    
    section Sprint 2: BO Procurement & Inventory (Sedang-Sulit)
    Satuan Produk, PO & DO       :sp2_1, after sp1_3, 7d
    Mutasi Cabang, Opname & HPP  :sp2_2, after sp2_1, 7d
    
    section Sprint 3: Front Desk & POS Dasar (Sedang)
    Sesi Kasir (Buka/Tutup Kas)  :sp3_1, after sp2_2, 4d
    POS Dasar (Barcode, Catalog) :sp3_2, after sp3_1, 5d
    Pelunasan Piutang & Laporan  :sp3_3, after sp3_2, 5d
    
    section Sprint 4: POS Lanjut & Service (Sulit)
    Mix Payment & Dynamic Tier   :sp4_1, after sp3_3, 6d
    Service Management & Kanban  :sp4_2, after sp4_1, 5d
    Offline Mode & WA Gateway    :sp4_3, after sp4_2, 5d
    
    section Sprint 5: HR, Payroll & Accounting (Sedang-Sulit)
    Kehadiran, Kasbon, Komisi    :sp5_1, after sp4_3, 8d
    Jurnal Otomatis & Laporan    :sp5_2, after sp5_1, 8d
    
    section Testing & Go-Live (7 Hari)
    System Integration Testing   :crit, test_1, after sp5_2, 2d
    User Acceptance Testing (UAT):crit, test_2, after test_1, 2d
    Security & Load Tuning       :crit, test_3, after test_2, 1d
    Data Migration & Go-Live     :crit, test_4, after test_3, 2d
```

---

## 2. Kategorisasi Kompleksitas Fitur

Fitur-fitur dikelompokkan berdasarkan estimasi tingkat kesulitan teknis dan dependensinya. Proses pengembangan dimulai dari **Mudah** untuk membangun pondasi database yang kokoh, berlanjut ke **Sedang** untuk operasional, hingga **Sulit** untuk logika bisnis kompleks (seperti akuntansi, integrasi API pihak ketiga, sinkronisasi offline, dan otomasi payroll).

| Tingkat Kesulitan | Kategori Modul / Fitur | Deskripsi Fungsional | Dependensi Utama |
| :--- | :--- | :--- | :--- |
| **Mudah (Easy)** | Data Master, CRUD Dasar, & Konfigurasi | - CRUD Pelanggan, User, Supplier, Gudang, Satuan<br>- CRUD Barang & Varian (Master stok dasar)<br>- Registrasi Cabang & Akses Cabang<br>- Setting Informasi Toko & Template Struk<br>- Tampilan Bar Absensi Kasir (Read-only) | Skema Database Inti |
| **Sedang (Medium)** | POS Dasar, Absensi, Kasbon, & Dashboard Dasar | - Sesi Kasir Harian (Buka/Tutup Kas, Modal Awal)<br>- Core POS (Scan barcode, add to cart, cash payment)<br>- Pelunasan Piutang Pelanggan<br>- Check-in Absensi Foto (Geotagging) & Face Lock<br>- Pengajuan Kasbon & Cicilan Karyawan<br>- Dashboard Sales & Leaderboard sederhana | - Data Master<br>- Database Sesi Kasir |
| **Sulit (Hard)** | POS Lanjutan, Akuntansi, Rantai Pasok, Payroll, Servis, Offline, & API | - POS: Mix Payment, Dynamic Pricing Tier, PPN otomatis<br>- Deposit Pelanggan (DP/Booking)<br>- Retur Penjualan Parsial/Total & Jurnal Retur<br>- Servis Musik: Alur Kanban, Sparepart HPP, POS integration<br>- Accounting: Jurnal Otomatis POS/PO/Payroll, Buku Besar, Neraca, Laba Rugi per Cabang & Konsolidasi, Depresiasi Aset<br>- Procurement & Stock: PO/DO Matching, Mutasi Antar-Cabang (In-Transit), Stok Opname & Auto-Journal Selisih, Average Weighted HPP<br>- Payroll: Rekap Gaji + KPI + Potongan Penalty Point Denda Telat, Komisi Sales Bertingkat, Slip PDF & WA Auto-send<br>- Integrasi: WA Gateway API, Shopee & Tokopedia Sync, Offline Mode (Service Worker + IndexedDB) | - Integrasi API Eksternal<br>- Jurnal Accounting Engine<br>- Browser Service Worker |

---

## 3. Rencana Detail Sprint (74 Hari Pengembangan)

### Sprint 1: Master Data & Basic Settings (Hari 1 - 12)
*Fokus pada perancangan database, hak akses user, registrasi multi-cabang, dan CRUD master data dasar.*
- **Hari 1 - 4: Database Schema & Core Setup**
  - Desain skema database SQL terenkripsi dan multi-tenant (pemisahan data cabang).
  - Setup routing dasar, otentikasi User (Owner, Admin, Kasir, Sales), dan manajemen Role-Based Access Control (RBAC).
- **Hari 5 - 9: Master Data CRUD & Multi-Cabang**
  - Implementasi CRUD Data Pelanggan, Supplier/Vendor, Satuan Barang, Kategori Penjualan, Lokasi Gudang, dan Chart of Accounts (COA) dasar.
  - Modul Registrasi Cabang Baru & Konfigurasi Hak Akses Cabang (pembatasan login berdasarkan lokasi cabang).
  - CRUD Master Barang & Varian (Input SKU/Barcode, HPP dasar, gambar produk, tipe produk fisik/bundling/jasa).
- **Hari 10 - 12: Basic CRM & Utility Settings**
  - Modul Loyalty Member & Poin Pelanggan (pencatatan rasio poin dasar).
  - Pengaturan informasi toko utama, konfigurasi header/footer struk thermal, dan fitur cetak barcode label.
- **Deliverables Sprint 1:** Panel admin back-office yang mampu mengelola cabang, user, dan seluruh master data produk siap pakai.

---

### Sprint 2: Back Office Procurement & Inventory (Hari 13 - 26)
*Fokus pada modul pengelolaan rantai pasok (pembelian dari supplier), mutasi stok antar-cabang, dan perhitungan HPP berbasis data pembelian rill.*
- **Hari 13 - 19: Satuan Produk & Pengelolaan PO/DO**
  - CRUD Satuan Produk (UoM) di Filament Back Office dan integrasi Select UoM ke dalam form input barang (`ProductResource`).
  - Pembuatan dokumen Purchase Order (PO) ke supplier dengan status penguncian (*Approved*).
  - Pembuatan Delivery Order (DO) / Penerimaan Barang untuk verifikasi jumlah fisik barang masuk vs PO.
  - Penambahan stok fisik di cabang penerima secara otomatis saat DO diselesaikan.
- **Hari 20 - 26: Mutasi Cabang, Stok Opname, & Perhitungan HPP**
  - Mutasi barang antar-cabang dengan tracker status pengiriman (*In-Transit*).
  - Formulir Stok Opname (fisik vs sistem) dengan jurnal penyesuaian selisih otomatis.
  - Kalkulasi HPP Rata-rata Terbobot (Weighted Average) otomatis yang diatribusikan dengan ongkos kirim saat barang masuk (DO).
  - Laporan Kartu Stok per barang per gudang cabang.
- **Deliverables Sprint 2:** Modul inventory Back Office yang lengkap untuk mencatat barang masuk, memindahkan barang antar-cabang, dan menghitung nilai HPP rill yang akurat.

---

### Sprint 3: Front Desk & POS Dasar (Hari 27 - 40)
*Fokus pada modul POS standar, sesi kas harian, penanganan piutang, dan pelaporan POS menggunakan stok rill.*
- **Hari 27 - 30: Sesi Kas Laci (Kas Harian)**
  - Fitur Buka Sesi (Input uang kas awal laci).
  - Tracking estimasi kas real-time di laci kasir selama transaksi berjalan.
  - Fitur Tutup Sesi (Blind counting uang fisik riil, kalkulasi otomatis selisih kurang/lebih kas, cetak Z-Report).
  - Otorisasi Supervisor/Owner (PIN/Password) untuk pembatalan/cancel tutup kas.
- **Hari 31 - 35: POS Core (Point of Sale)**
  - Antarmuka POS Kasir (Katalog visual, Scan Barcode, Keranjang Belanja, Edit Qty, Pemilihan Sales Rep).
  - Transaksi Penjualan Tunai/Single Payment (Cash/Transfer/QRIS/EDC) dan hitung kembalian otomatis.
  - Hold/Recall Transaksi Sementara (Simpan antrean belanja).
  - Cetak struk penjualan thermal.
- **Hari 36 - 40: Pelunasan Piutang, Retur POS, & Laporan POS**
  - Modul Pelunasan Piutang Pelanggan (pembayaran kredit berjalan, cetak bukti pelunasan).
  - Laporan Harian POS (Rekap penjualan, mutasi laci, piutang, daftar stok dan harga).
  - Info/Bar Absensi Kasir di POS (indikator jatah off day hijau/merah).
- **Deliverables Sprint 3:** POS Kasir yang bisa digunakan untuk transaksi tunai langsung dengan kontrol sesi laci kasir yang aman dan berbasis stok cabang sesungguhnya.

---

### Sprint 4: POS Lanjutan, Servis, & Notifikasi CRM (Hari 41 - 56)
*Fokus pada fitur transaksi kompleks, modul servis instrumen musik, sinkronisasi offline, dan integrasi WhatsApp.*
- **Hari 41 - 46: Advanced POS Features**
  - Pilihan metode pembayaran gabungan (Mix Payment: Cash + Transfer, QRIS + Debit, dll.) beserta sub-form EDC (4 digit kartu, trace number).
  - Logika Harga Bertingkat Dinamis (tiering harga ritel global/per-item otomatis saat member terdeteksi).
  - Input PPN Transaksi fleksibel (nominal / persentase).
  - Modul Deposit Pelanggan (DP/Booking Inden barang) dan integrasi potong deposit di POS.
  - Modul Retur Penjualan Parsial/Total (input alasan retur, refund cash/voucher, status barang layak jual vs rusak).
- **Hari 47 - 51: Modul Servis Musik (Service Management)**
  - Pembuatan tiket Servis Masuk (merk, no seri, checklist kelengkapan aksesoris, estimasi biaya).
  - Kanban Board Pelacakan Status Servis (Antrean -> Dikerjakan -> Menunggu Part -> Siap Diambil -> Closed).
  - Penggunaan suku cadang (memotong stok gudang) + biaya jasa teknisi.
  - Integrasi konversi langsung tiket servis selesai menjadi invoice POS kasir.
- **Hari 52 - 56: WhatsApp Gateway & Offline POS Mode**
  - Integrasi API WhatsApp Gateway: WhatsApp Invoice PDF otomatis pasca-lunas, WhatsApp Reminder tagihan piutang, WA Broadcast promo.
  - Setup Service Worker & IndexedDB untuk Offline Mode (antrean transaksi FIFO saat koneksi mati, auto-sync tanpa duplikasi saat online).
- **Deliverables Sprint 4:** Modul POS berkemampuan penuh, alur servis musik terintegrasi, notifikasi struk WhatsApp, dan ketahanan transaksi offline.

---

### Sprint 5: HR, Payroll, & Accounting (Hari 57 - 72)
*Fokus pada kepegawaian, komisi, denda absensi, penggajian otomatis, dan penjurnalan keuangan double-entry otomatis.*
- **Hari 57 - 64: HR & Payroll Engine**
  - Penarikan data absensi fingerprint/face lock, absensi foto dinas luar + geotagging GPS, dan approval kehadiran backdate.
  - Pengajuan Kasbon Karyawan, cicilan berkala, KPI template, denda keterlambatan/denda penalty points.
  - Komisi sales bertingkat (per target/produk) dan kalkulasi penggajian otomatis (Gaji Pokok + Tunjangan + Komisi - Kasbon - Denda).
  - Ekspor slip gaji PDF/Excel & kirim otomatis slip gaji PDF ke WA karyawan.
- **Hari 65 - 72: Accounting Core & Integrasi API**
  - Jurnal Umum manual dan otomatis (posting instan dari transaksi POS, PO/DO, Payroll, dan penyusutan aset).
  - Laporan Keuangan: Neraca (Balance Sheet), Neraca Saldo, Buku Besar, Laba Rugi per Cabang & Konsolidasi (Omset, Laba Kotor, Biaya, Laba Bersih), Buku Bank, Buku Vendor, dan Tutup Buku Bulanan.
  - Integrasi API Tokopedia & Shopee (Sinkronisasi stok barang real-time dan impor otomatis data penjualan ke pembukuan).
  - Dashboard Owner Analytics (Widget omset, laba, piutang, hutang; grafik Pareto 80/20 produk/pelanggan; perputaran stok; peak traffic hours).
- **Deliverables Sprint 5:** Sistem penggajian otomatis terintegrasi absensi riil, pembukuan jurnal otomatis, laporan keuangan komprehensif, dan dashboard visual owner.

---

## 4. Pengujian & Stabilisasi (7 Hari)

Satu minggu terakhir didedikasikan penuh untuk memastikan keandalan sistem sebelum proses serah terima dan go-live.

```
Hari 73 ──► Hari 74 ──► Hari 75 ──► Hari 76 ──► Hari 77 ──► Hari 78-79
 [SIT]       [SIT]       [UAT]       [UAT]     [STRESS]     [GO-LIVE]
```

- **Hari 73 - 74: System Integration Testing (SIT) & Perbaikan Bug**
  - Skenario pengujian ujung-ke-ujung (*end-to-end*), seperti: Pembelian PO/DO -> Perhitungan HPP -> Transaksi POS -> Jurnal Umum Otomatis -> Laporan Laba Rugi per Cabang -> Pengurangan Stok Gudang.
  - Pengujian alur kegagalan: pemutusan koneksi internet secara sengaja saat transaksi POS untuk menguji IndexedDB antrean FIFO dan auto-sync kembali saat online.
- **Hari 75 - 76: User Acceptance Testing (UAT)**
  - Pengujian langsung oleh pengguna akhir (Kasir, Staf Back Office, Teknisi Servis, Sales Rep, dan Owner).
  - Validasi kecocokan tampilan, kemudahan pemindaian barcode, kegunaan interface slip gaji, dan kecocokan angka laporan keuangan.
- **Hari 77: Stress Testing & Load Tuning**
  - Simulasi transaksi POS massal secara bersamaan dari seluruh cabang fisik dan sinkronisasi pesanan e-commerce untuk menguji performa server.
  - Optimasi indeks database SQL agar loading Dashboard Owner dan Laporan Buku Besar tetap cepat di bawah 2 detik.
- **Hari 78 - 79: Migrasi Data Akhir & Go-Live**
  - Migrasi data master barang, data pelanggan, saldo awal kas/bank, dan saldo hutang/piutang berjalan dari aplikasi lama menggunakan format ekspor/impor Excel yang telah diuji.
  - Penerbitan aplikasi ke server produksi dan go-live sistem ERP Diego Music Store.

---

## 5. Rencana Manajemen Risiko & Mitigasi

> [!WARNING]
> Dalam pengerjaan proyek berskala besar seperti ERP, ada beberapa risiko teknis yang wajib diantisipasi:
>
> 1. **Risiko Selisih Pembukuan Akuntansi:**
>    - *Mitigasi:* Mengunci data jurnal transaksi jika bulan bersangkutan sudah melewati fase "Tutup Buku Bulanan" dan melakukan validasi balance (Debit = Kredit) di tingkat skema database sebelum jurnal disimpan.
> 2. **Keterlambatan Penarikan Data Absensi Fingerprint:**
>    - *Mitigasi:* Menyediakan modul unggah file rekap kehadiran cadangan (.csv / .xlsx) secara manual di Back Office jika API mesin fingerprint lokal sedang mengalami gangguan jaringan.
> 3. **Bentrok Sinkronisasi Stok E-Commerce:**
>    - *Mitigasi:* Menerapkan antrean pesan (Message Queue) pada API webhook Tokopedia/Shopee agar pembaruan stok dilakukan secara berurutan dan mencegah kondisi balapan (*race condition*).
