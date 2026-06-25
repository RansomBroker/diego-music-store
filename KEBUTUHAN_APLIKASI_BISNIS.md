# KEBUTUHAN FITUR APLIKASI BISNIS ALL IN ONE

## HALAMAN: FRONT DESK

### KATEGORI: POS SYSTEM - FITUR DASAR - FITUR UTAMA

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Transaksi                Transaksi Penjualan, Pelunasan
                                        Hutang, Kas Harian, Edit
                                        Penjualan, Retur Penjualan, Tutup
                                        Kas/Sesi, Print
                                        Invoice/Bon/Struk, Cancel Tutup
                                        Kas

  2            Input Data               Input Data Pelanggan, Input Data
                                        User, Input Satuan Barang, Input
                                        Kategori Penjualan

  3            Laporan                  Laporan Penjualan, Laporan
                                        Piutang, Laporan Pelunasan
                                        Piutang, Laporan Kas Harian,
                                        Daftar Stok dan Harga

  4            Utility Setting          Privilage User, Register Nama
                                        Toko, Setting Struk dan Invoice,
                                        Cetak Barcode

  6            Info/Bar Absensi         Tampilkan jumlah hari kehadiran
                                        dan jumlah off day yang sudah
                                        diambil karyawan. Jika sudah over
                                        akan berubah warna menjadi merah
  5            Multi Cabang             Membuat cabang baru lengkap
                                        dengan seluruh akses seperti
                                        cabang utama, login berdasarkan
                                        lokasi cabang, atur akses user
                                        untuk beberapa cabang

  6            Manajemen Cabang         Mengelola stok, pelanggan dan
                                        laporan laba rugi per cabang
                                        dalam satu entitas bisnis

  7            Offline Mode dan         Bisa digunakan offline (ketika
               Auto-sync                internet off) dan sinkronisasi
                                        kembali ketika online, contoh
                                        menggunakan komponen Service
                                        Worker

  8            Manajemen Barang Service Cetak tanda terima service,
                                        pantau proses service hingga
                                        selesai, konversi ke invoice
                                        ketika servis sudah selesai
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: PAYROLL

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Slip Gaji                Generate slip gaji karyawan
                                        otomatis per periode dengan
                                        rincian komponen pendapatan dan
                                        potongan

  2            Slip Gaji Detail         Slip gaji menampilkan rincian
               (Termasuk Overtime)      lembur (mulai, selesai, status
                                        approval) dan komponen gaji lebih
                                        jelas

  3            Kasbon / Cash Advance    Karyawan bisa ajukan kasbon, ada
               (Auto Potong Payroll)    approval & status cicilan; saat
                                        payroll dibuat bisa otomatis jadi
                                        pengurangan

  4            Request Kehadiran        Karyawan bisa ajukan presensi
               (Backdate)               backdate; setelah disetujui, data
                                        kehadiran dibuat sesuai
                                        tanggal/jam yang disepakati

  5            Integrasi Fingerprint /  Tarik data dari mesin absensi
               Mesin Absensi            (fingerprint/face) ke sistem
                                        untuk jadi data kehadiran

  7            Kehadiran dengan Foto    Opsi absen dan check-in pakai
                                        foto untuk bukti visual saat
                                        absensi jika sedang ditugaskan di
                                        luar toko

  8            Manajemen Komisi         Skema komisi sales
                                        (flat/bertingkat), per
                                        target/produk, kalkulasi
                                        otomatis, approval, dan export
                                        rekap komisi
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: MANAJEMEN KARYAWAN

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Payroll Management       Otomatisasi gaji,
                                        tunjangan/potongan, rekap, slip
                                        gaji PDF/Excel, dan export
                                        payroll bank

  2            KPI (Key Performance     Template KPI per user/jabatan
               Indicator)               dengan indikator Target
                                        Penjualan, ATV, tingkat absensi,
                                        ketepatan waktu datang, terhubung
                                        dengan insentif/bonus

  3            Penalty Point & Potongan Aturan pelanggaran (telat/pulang
               Otomatis                 cepat/leave tertentu), log
                                        otomatis, bisa masuk ke payroll
                                        sebagai potongan
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: ACCOUNTING & INVENTORY

### HALAMAN: BACK OFFICE

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Input Data               Klasifikasi Akun, Daftar Akun,
                                        Satuan Produk, Input Lokasi,
                                        Input Data Barang,
                                        Supplier/Vendor, User

  2            Transaksi                Jurnal Umum, Edit Jurnal,
                                        Pembelian, Retur Pembelian,
                                        Pelunasan Hutang, Stok Opname,
                                        Mutasi Barang, Kas Harian, Faktur
                                        Penjualan, Penawaran Harga

  3            Laporan Keuangan         Balance Sheet, Income Statement,
                                        Buku Besar, Laporan Jurnal,
                                        Neraca Saldo, Buku Bank, Buku
                                        Vendor, Tutup Buku Bulanan

  4            Laporan Umum             Laporan Pembelian, Hutang,
                                        Pelunasan Hutang, Retur
                                        Pembelian, Kas

  5            Laporan Stok             Daftar Stok, Stok Opname, Kartu
                                        Stok, Persediaan Akhir, Mutasi
                                        Barang

  6            Manajemen Aset           Mengelola nilai aset dari waktu
               (Depresiasi Aset)        ke waktu termasuk penyusutan dan
                                        nilai buku

  7            Utility Setting          Privilage User, Registrasi
                                        Informasi Toko, Backup Database

  8            Manajemen Aset           Mengelola penjualan aset atau
               (Disposisi Aset)         penghapusan aset

  9            Deposit Pelanggan        Downpayment untuk booking barang
                                        atau PO

  10           Return Barang Sebagian   Retur sebagian tanpa
               (Retur)                  mengembalikan seluruh barang

  11           Purchasing Order (PO)    Penguncian pesanan ke supplier
               dan Delivery Order (DO)  dan penerimaan barang ke gudang

  12           Sinkronisasi Marketplace Integrasi API untuk sinkronisasi
                                        stok dan penjualan ke pembukuan
                                        akuntansi
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: CRM (CUSTOMER RELATIONSHIP MANAGEMENT)

### HALAMAN: FRONT OFFICE

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Loyalty Member dan Poin  Pelanggan otomatis menjadi member
               Pelanggan                dan mendapatkan poin belanja

  2            WhatsApp Broadcast and   Mengirim tagihan otomatis dan
               Reminder                 broadcast promo

  3            WhatsApp Invoice         Mengirim struk kasir melalui
                                        WhatsApp dalam format PDF
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: DASHBOARD OWNER

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Ringkasan Keuangan       Total penjualan, pengeluaran,
                                        hutang, saldo kas dan bank

  2            Grafik Penjualan dan     Grafik area perbandingan
               Pembelian                penjualan dan pembelian

  3            Grafik Turn Over Stok    Kecepatan perputaran stok

  4            Grafik Pareto 80/20      Produk dan pelanggan paling
                                        menguntungkan

  5            Grafik Tren Penjualan    Tren bisnis dan prediksi bulan
               Bulanan                  berikutnya

  6            Grafik Penjualan         Kontribusi omzet dan profit per
               Per-Kategori             kategori

  7            Grafik Penjualan         Kontribusi omzet dan profit per
               Per-Cabang               cabang

  8            Grafik Monthly Report    Grafik harian low dan peak
                                        pengunjung

  9            Grafik Waktu Pengunjung  Traffic jam pengunjung per bulan

  10           Grafik Performa Sales    Pencapaian target dan absensi
                                        sales

  11           Filter Total Penjualan   Filter berdasarkan kategori dan
                                        seluruh penjualan
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## KATEGORI: DASHBOARD KARYAWAN / SALES

  -----------------------------------------------------------------------
  No           Modul                    Ringkasan
  ------------ ------------------------ ---------------------------------
  1            Pantau Target Penjualan  Target bulanan dan indikator
               Bulanan                  pencapaian

  2            Pantau Target Penjualan  Target harian berdasarkan target
               Harian                   bulanan

  3            Komisi Penjualan         Menampilkan nominal komisi

  4            Sisa Target Unlock Tier  Progress menuju tier komisi
               Komisi                   berikutnya

  5            Leaderboard              Top 3 sales dalam satu bulan

  6            Produk Fokus Bulan Ini   Produk prioritas dan insentif
                                        tambahan

  7            Grafik Performa Sales    Tren omzet sales dalam 1 tahun

  8            Info/Bar Absensi         Jumlah kehadiran dan off day
  -----------------------------------------------------------------------

------------------------------------------------------------------------

# FITUR YANG BERTANDA MERAH

## 1. Transaksi Penjualan

Kasir bisa input penjualan berdasarkan nama sales, pelanggan, kategori
penjualan, tier harga, metode pembayaran (cash, transfer, mix payment),
serta mencetak struk jual dan struk tagihan.

## 2. Input Data Barang

Input kode, nama, jenis produk, harga beli, HPP setelah ongkir, 5
tingkat harga, harga cabang, produk bundling, dan produk jasa dengan
stok tidak terbatas.

## 3. Income Statement

Laporan laba rugi dengan rincian: - Omset Penjualan - Laba Kotor - Biaya
Operasional - Biaya Lain-lain - Laba Bersih

------------------------------------------------------------------------

# NOTE

1.  Sebagian fitur yang ditampilkan merupakan fitur yang sudah ada di
    aplikasi lama.
2.  Jika developer memiliki konsep dan arsitektur yang lebih baik dan
    efisien, dapat diajukan.
3.  Jika membutuhkan detail penjelasan fitur lain, silakan
    diinformasikan.
