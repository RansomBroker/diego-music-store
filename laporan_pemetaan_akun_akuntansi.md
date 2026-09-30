# Laporan Komprehensif Pemetaan Jurnal Akuntansi Otomatis
**Sistem**: Diego Music Store ERP (Backoffice & POS)
**Tanggal Update**: 29 September 2026

Berikut adalah hasil rangkuman mendalam (exhaustive) pemetaan seluruh halaman dan modul di dalam sistem **Diego Music Store ERP** yang memicu pembuatan/penulisan Jurnal Akuntansi (`JournalEntry` & `JournalItem`) secara otomatis.

---

## 1. Modul Point of Sale (POS Kasir)

### A. Transaksi Penjualan POS (`CreatePOSSale` & `UpdatePOSSale`)
Ketika kasir menyelesaikan atau memperbarui transaksi pada layar POS.
*   **Debit**: KAS / BANK BCA / PIUTANG DAGANG / BIAYA COMPLIMENTARY (Sesuai metode pembayaran)
*   **Kredit**: PENJUALAN (Pendapatan Penjualan)
*   **Debit**: HARGA POKOK PENJUALAN (Khusus barang fisik)
*   **Kredit**: PERSEDIAAN BARANG DAGANG (Khusus barang fisik)

### B. Pelunasan Piutang via POS (`PosCustomerPayments` & `POSTransactions`)
Ketika pelanggan yang memiliki bon tagihan (piutang) datang melunasi cicilan atau tunggakan di meja kasir.
*   **Debit**: KAS / BANK (Uang yang diterima)
*   **Kredit**: PIUTANG DAGANG 

---

## 2. Modul Backoffice: Penjualan B2B & Pelanggan

### A. Penjualan B2B / Faktur Penjualan Backoffice (`PostSalesInvoice`)
Penjualan besar/partai atau proyek yang dibuat melalui Modul Faktur Penjualan Backoffice.
*   **Debit**: PIUTANG DAGANG (Kredit) atau KAS / BANK (Tunai)
*   **Kredit**: PENJUALAN (Senilai Subtotal - Diskon)
*   **Kredit**: HUTANG PPN / PPN KELUARAN (Jika terdapat PPN)
*   **Kredit**: PENJUALAN (Sebagai Pendapatan Biaya Kirim, jika ditagihkan ke pembeli)
*   **Debit**: HARGA POKOK PENJUALAN
*   **Kredit**: PERSEDIAAN BARANG DAGANG

### B. Retur Penjualan (`PostSalesReturn`)
Ketika ada klaim retur pelanggan yang disetujui, baik POS maupun B2B.
*   **Debit**: RETUR PENJUALAN (Mengurangi Pendapatan)
*   **Kredit**: KAS / BANK 
*   **Debit**: PERSEDIAAN BARANG DAGANG (Barang kembali ke stok)
*   **Kredit**: HARGA POKOK PENJUALAN (Pembalikan HPP)

### C. Titipan Dana / Deposit Pelanggan (`CreateCustomerDeposit`)
Ketika pelanggan menitipkan DP/uang muka pesanan barang.
*   **Debit**: KAS / BANK
*   **Kredit**: HUTANG PENITIPAN DANA (Liabilitas)

### D. Penyelesaian/Settlement Deposit Pelanggan (`SettleCustomerDeposit`)
Ketika barang indent sudah datang dan diserahkan (pelanggan lunas).
*   **Debit**: HUTANG PENITIPAN DANA
*   **Debit**: KAS / BANK (Jika ada sisa pelunasan/kurang bayar)
*   **Kredit**: PENJUALAN

---

## 3. Modul Backoffice: Pembelian & Supplier

### A. Pembayaran Uang Muka (DP) Purchase Order (`PayPurchaseOrderDownPayment`)
*   **Debit**: UANG MUKA PEMBELIAN (Aset)
*   **Kredit**: KAS / BANK (Mengurangi uang bank)

### B. Penerimaan Pembelian / Pembelian Tunai & Kredit (`PostPurchaseTransaction`)
Sistem otomatis menyebar biaya ongkir ke HPP persediaan secara prorata.
*   **Debit**: PERSEDIAAN BARANG DAGANG (Termasuk alokasi dasar harga barang + ongkir prorata jika ditanggung toko)
*   **Debit**: PPN DIBAYAR DIMUKA / PPN MASUKAN (Jika ada pajak)
*   **Kredit**: HUTANG ONGKIR (Jika menggunakan ekspedisi pihak ke-3)
*   **Kredit**: HUTANG PPh PASAL 21 (Jika ada potongan PPh)
*   **Kredit**: UANG MUKA PEMBELIAN (Pembalikan DP PO)
*   **Kredit**: HUTANG DAGANG (Pembelian kredit) atau **KAS/BANK** (Tunai)

### C. Pelunasan Hutang Supplier (`ProcessSupplierPaymentComplete`)
Membayar cicilan atau melunasi faktur ke supplier.
*   **Debit**: HUTANG DAGANG
*   **Kredit**: KAS / BANK

---

## 4. Modul Backoffice: Inventaris & Gudang (Inventory)

### A. Penyesuaian Stok Opname (`ProcessStockOpnameComplete`)
Ketika admin memposting hasil stok fisik gudang dan ditemukan selisih (surplus/defisit).
*   **Kondisi Surplus (Lebih Barang)**:
    *   **Debit**: PERSEDIAAN BARANG DAGANG
    *   **Kredit**: HARGA POKOK PENJUALAN (Pembalikan/Penyesuaian ke HPP)
*   **Kondisi Defisit (Kurang/Hilang)**:
    *   **Debit**: HARGA POKOK PENJUALAN (Rugi persediaan dibebankan ke HPP)
    *   **Kredit**: PERSEDIAAN BARANG DAGANG

---

## 5. Modul Backoffice: Karyawan & Payroll (HR)

### A. Kasbon / Pinjaman Karyawan (`ApproveCashAdvance`)
*   **Debit**: PIUTANG KARYAWAN
*   **Kredit**: KAS / BANK

### B. Pelunasan Kasbon Tunai (Di Luar Gaji) (`SettleCashAdvanceEarly`)
Jika karyawan mengembalikan kasbon secara tunai.
*   **Debit**: KAS / BANK
*   **Kredit**: PIUTANG KARYAWAN

### C. Penggajian / Payroll (`ProcessPayrollPayment`)
*   **Debit**: BEBAN GAJI
*   **Kredit**: PIUTANG KARYAWAN (Otomatis memotong jika karyawan memiliki kasbon)
*   **Kredit**: KAS / BANK (Transfer bersih/Take Home Pay)

---

## 6. Modul Backoffice: Kas & Bank (Keuangan)

### A. Mutasi / Pengeluaran / Pemasukan Kas Kasar (`PostCashTransaction`)
Jurnal fleksibel dari menu Kas & Bank.
*   **Debit**: Akun Tujuan (Biaya Operasional, Biaya Admin Bank, dll)
*   **Kredit**: Akun Sumber (Kas, Bank)

---

## 7. Modul Backoffice: Akuntansi & Jurnal Umum

### A. Jurnal Umum Manual (`CreateJournalEntry`)
Modul pembuatan jurnal penyesuaian (*Adjustment Journal*) 100% manual.

### B. Tutup Buku Bulanan (`ExecuteMonthlyClosing`)
Pada akhir bulan, pendapatan dan beban dikosongkan.
*   **Debit**: SELURUH AKUN PENDAPATAN 
*   **Kredit**: SELURUH AKUN BEBAN & HPP
*   **Kredit / Debit**: LABA TAHUN BERJALAN / LABA DITAHAN (Target Ekuitas Penutup Laba/Rugi Bulanan)

### C. Tutup Buku Akhir Tahun (`ExecuteYearEndClosing`)
Sistem otomatis memindahkan laba/rugi satu tahun ke ekuitas tetap.
*   **Jika Laba (Profit)**:
    *   **Debit**: LABA TAHUN BERJALAN (Menolkan saldo tahun ini)
    *   **Kredit**: LABA DITAHAN (Menambah Laba Ditahan)
*   **Jika Rugi (Loss)**:
    *   **Debit**: LABA DITAHAN (Mengurangi Ekuitas)
    *   **Kredit**: LABA TAHUN BERJALAN

---

## 8. Modul Backoffice: Manajemen Aset Tetap

### A. Pelepasan / Penjualan Aset (`PostAssetDisposal`)
Penghapusan buku aset tetap.
*   **Debit**: AKUMULASI PENYUSUTAN ASET
*   **Debit**: KAS / BANK (Jika dijual tunai)
*   **Debit**: RUGI PENJUALAN ASET (Jika Rugi)
*   **Kredit**: ASET TETAP (Pelepasan Nilai Perolehan)
*   **Kredit**: LABA PENJUALAN ASET (Jika Untung)

---
*Laporan ini bersifat komprehensif (Exhaustive) setelah dilakukan pemindaian (scan) ke-2 terhadap seluruh sumber kode backend (Controllers, Action Classes, dan Livewire Components) per September 2026.*
