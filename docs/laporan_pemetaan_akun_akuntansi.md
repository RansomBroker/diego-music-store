# Laporan Komprehensif Pemetaan Jurnal Akuntansi Otomatis
**Sistem**: Diego Music Store ERP (Backoffice & POS)  
**Tanggal Update**: 24 Oktober 2026 (Audit Komprehensif Seluruh Antarmuka Backoffice Filament & POS Front-Office)

Laporan ini menyajikan pemetaan menyeluruh (*exhaustive trace*) seluruh antarmuka **Backoffice Filament** (`/backoffice`) serta **Point of Sale / Front Office Kasir (POS / FO)** (`/pos`) yang berinteraksi dengan buku besar akuntansi (`JournalEntry`, `JournalItem`, dan `Account`) di dalam sistem **Diego Music Store ERP**. Seluruh aturan pencatatan telah diaudit dan diselaraskan dengan standar akuntansi berpasangan (*Double-Entry*), *Matching Principle*, SAK Indonesia, dan kepatuhan perpajakan.

---

## BAGIAN I: MODUL BACKOFFICE (FILAMENT ADMIN)

Seluruh modul di bawah ini dapat diakses melalui antarmuka Backoffice Filament (`/backoffice`).

### 1. Pembelian & Hutang Supplier (`PurchaseTransactions`, `PurchaseOrders`, `SupplierPayments`)

#### A. Pembayaran Uang Muka (DP) Purchase Order
*   **Navigasi Backoffice**: *Pengadaan > Purchase Order* ([PurchaseOrderResource](../public/diego-music-store/app/Filament/Resources/PurchaseOrders/PurchaseOrderResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`payDp`** pada [PurchaseOrdersTable](../public/diego-music-store/app/Filament/Resources/PurchaseOrders/Tables/PurchaseOrdersTable.php).
*   **Action Class**: [PayPurchaseOrderDownPayment.php](../public/diego-music-store/app/Actions/Procurement/PayPurchaseOrderDownPayment.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `111201006` - UANG MUKA PEMBELIAN (Aset Lancar)
    *   **Kredit**: Akun Kas / Bank Operasional yang dipilih kasir/admin

#### B. Penerimaan & Posting Faktur Pembelian (Tunai / Kredit)
*   **Navigasi Backoffice**: *Pengadaan > Transaksi Pembelian* ([PurchaseTransactionResource](../public/diego-music-store/app/Filament/Resources/PurchaseTransactions/PurchaseTransactionResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel atau header form **`post`** pada [PurchaseTransactionsTable](../public/diego-music-store/app/Filament/Resources/PurchaseTransactions/Tables/PurchaseTransactionsTable.php) & [EditPurchaseTransaction](../public/diego-music-store/app/Filament/Resources/PurchaseTransactions/Pages/EditPurchaseTransaction.php).
*   **Action Class**: [PostPurchaseTransaction.php](../public/diego-music-store/app/Actions/Procurement/PostPurchaseTransaction.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `111401001` - PERSEDIAAN BARANG DAGANG (Harga faktur + prorata ongkir & biaya lain kapitalisasi PSAK 14)
    *   **Debit**: `111901001` - PPN DIBAYAR DIMUKA / PPN MASUKAN (Jika faktur berpajak)
    *   **Kredit**: HUTANG ONGKIR EKSPEDISI (Jika ada ongkir pihak ke-3)
    *   **Kredit**: HUTANG PPh PEMBELIAN (Jika ada potongan PPh)
    *   **Kredit**: `111201006` - UANG MUKA PEMBELIAN (Pembalikan DP PO yang sudah dibayar di muka)
    *   **Kredit**: `211101001` - HUTANG DAGANG (Jika pembelian tempo/kredit) atau **KAS/BANK** (Jika tunai)

#### C. Pelunasan Hutang Supplier
*   **Navigasi Backoffice**: *Keuangan > Pembayaran Hutang Supplier* ([SupplierPaymentResource](../public/diego-music-store/app/Filament/Resources/SupplierPayments/SupplierPaymentResource.php))
*   **Trigger UI / Tombol**: Form simpan/update status `posted` pada [CreateSupplierPayment](../public/diego-music-store/app/Filament/Resources/SupplierPayments/Pages/CreateSupplierPayment.php) & [EditSupplierPayment](../public/diego-music-store/app/Filament/Resources/SupplierPayments/Pages/EditSupplierPayment.php).
*   **Action Class**: [ProcessSupplierPaymentComplete.php](../public/diego-music-store/app/Actions/SupplierPayment/ProcessSupplierPaymentComplete.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `211101001` - HUTANG DAGANG
    *   **Kredit**: Akun Kas / Bank sumber pembayaran yang dipilih
*   *Tautan UI*: Tabel memiliki tombol aksi **`lihat_jurnal`** untuk membuka jurnal terkait di Jurnal Umum.

#### D. Pembatalan Pelunasan Hutang Supplier (Void)
*   **Navigasi Backoffice**: *Keuangan > Pembayaran Hutang Supplier* ([SupplierPaymentResource](../public/diego-music-store/app/Filament/Resources/SupplierPayments/SupplierPaymentResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`cancel`** pada [SupplierPaymentsTable](../public/diego-music-store/app/Filament/Resources/SupplierPayments/Tables/SupplierPaymentsTable.php).
*   **Action Class**: [CancelSupplierPayment.php](../public/diego-music-store/app/Actions/SupplierPayment/CancelSupplierPayment.php)
*   **Dampak Akuntansi**: Mengubah status `JournalEntry` terkait menjadi `'cancelled'` (*void*) dan mengembalikan saldo hutang supplier (`suppliers.outstanding_debt`).

#### E. Retur Pembelian ke Supplier
*   **Navigasi Backoffice**: *Pengadaan > Retur Pembelian* ([PurchaseReturnResource](../public/diego-music-store/app/Filament/Resources/PurchaseReturns/PurchaseReturnResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`post`** pada [PurchaseReturnsTable](../public/diego-music-store/app/Filament/Resources/PurchaseReturns/Tables/PurchaseReturnsTable.php).
*   **Action Class**: [PostPurchaseReturn.php](../public/diego-music-store/app/Actions/Purchases/PostPurchaseReturn.php)
*   **Pencatatan Jurnal**:
    *   *Kondisi Potong Faktur*: **Debit** `211101001` HUTANG DAGANG | **Kredit** `111401001` PERSEDIAAN
    *   *Kondisi Refund Tunai*: **Debit** KAS / BANK | **Kredit** `111401001` PERSEDIAAN
    *   *Kondisi Saldo Deposit Supplier*: **Debit** `111201006` UANG MUKA PEMBELIAN | **Kredit** `111401001` PERSEDIAAN

---

### 2. Penjualan Backoffice & Piutang B2B (`SalesInvoices`, `SalesReturns`)

#### A. Faktur Penjualan Backoffice (B2B / Grosir / Tempo)
*   **Navigasi Backoffice**: *Penjualan > Faktur Penjualan* ([SalesInvoiceResource](../public/diego-music-store/app/Filament/Resources/SalesInvoices/SalesInvoiceResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`post`** pada [SalesInvoicesTable](../public/diego-music-store/app/Filament/Resources/SalesInvoices/Tables/SalesInvoicesTable.php).
*   **Action Class**: [PostSalesInvoice.php](../public/diego-music-store/app/Actions/Sales/PostSalesInvoice.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `111301001` - PIUTANG DAGANG (Kredit/Tempo) atau **KAS/BANK** (Tunai)
    *   **Kredit**: `411101001` - PENJUALAN (Subtotal bersih setelah diskon)
    *   **Kredit**: `211201001` - HUTANG PPN / PPN KELUARAN (Jika faktur dikenakan pajak)
    *   **Kredit**: PENDAPATAN ONGKOS KIRIM / PENJUALAN (Jika ada tagihan ongkir ke pelanggan)
    *   **Debit**: `511501001` - HARGA POKOK PENJUALAN
    *   **Kredit**: `111401001` - PERSEDIAAN BARANG DAGANG

#### B. Retur Penjualan Backoffice
*   **Navigasi Backoffice**: *Penjualan > Retur Penjualan* ([SalesReturnResource](../public/diego-music-store/app/Filament/Resources/SalesReturns/SalesReturnResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`post`** pada [SalesReturnsTable](../public/diego-music-store/app/Filament/Resources/SalesReturns/Tables/SalesReturnsTable.php).
*   **Action Class**: [PostSalesReturn.php](../public/diego-music-store/app/Actions/Sales/PostSalesReturn.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `411201001` - RETUR PENJUALAN (Pembalikan Pendapatan)
    *   **Kredit**: `111301001` - PIUTANG DAGANG (Diprioritaskan mengeliminasi sisa piutang nota)
    *   **Kredit**: KAS / BANK (Hanya untuk sisa refund tunai jika total refund melebihi piutang)
    *   **Debit**: `111401001` - PERSEDIAAN BARANG DAGANG (Barang fisik kembali ke gudang cabang)
    *   **Kredit**: `511501001` - HARGA POKOK PENJUALAN (Pembalikan HPP)

---

### 3. Kas, Bank & Keuangan Operasional (`CashTransactions`)

#### A. Transaksi Kas Masuk / Keluar / Mutasi Rekening
*   **Navigasi Backoffice**: *Keuangan > Transaksi Kas* ([CashTransactionResource](../public/diego-music-store/app/Filament/Resources/CashTransactions/CashTransactionResource.php))
*   **Trigger UI / Tombol**: Form submit pada [CreateCashTransaction](../public/diego-music-store/app/Filament/Resources/CashTransactions/Pages/CreateCashTransaction.php) & [EditCashTransaction](../public/diego-music-store/app/Filament/Resources/CashTransactions/Pages/EditCashTransaction.php).
*   **Action Class**: [PostCashTransaction.php](../public/diego-music-store/app/Actions/CashManagement/PostCashTransaction.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: Akun Tujuan (Beban Operasional, Pembelian ATK/Listrik, atau Bank Tujuan)
    *   **Kredit**: Akun Sumber (Kas Kecil Kasir atau Rekening Bank Asal)
*   *Tautan UI*: Tabel memiliki tombol aksi **`lihat_jurnal`** yang menghubungkan langsung ke transaksi Jurnal Umum.

---

### 4. Transfer Stok Antar-Cabang (`StockTransferResource`)

#### A. Eksekusi Pemindahan Stok Antar-Cabang
*   **Navigasi Backoffice**: *Inventaris > Transfer Stok Cabang* ([StockTransferResource](../public/diego-music-store/app/Filament/Resources/StockTransferResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`proses_transfer`** pada [StockTransferResource.php:L358](../public/diego-music-store/app/Filament/Resources/StockTransferResource.php#L358).
*   **Action Class**: [CompleteStockTransfer.php](../public/diego-music-store/app/Actions/StockTransfer/CompleteStockTransfer.php)
*   **Pencatatan Jurnal (Pola 4 Akun Resiprokal)**:
    *   *Sisi Cabang Pengirim*:
        *   **Debit**: `11160...` - PIUTANG ANTAR CABANG (Cabang Pengirim)
        *   **Kredit**: `111401001` / `11180...` - PERSEDIAAN BARANG DAGANG (Cabang Pengirim)
    *   *Sisi Cabang Penerima*:
        *   **Debit**: `111401001` / `11180...` - PERSEDIAAN BARANG DAGANG (Cabang Penerima)
        *   **Kredit**: `21130...` - HUTANG ANTAR CABANG (Cabang Penerima)
*   *Tautan UI*: Dilengkapi tombol aksi **`lihat_jurnal`** dan **`lihat_pergerakan_stok`**.

---

### 5. Inventaris & Gudang: Penyesuaian Hasil Stok Opname (`StockOpnameReview`)

#### A. Finalisasi Periode Stok Opname
*   **Navigasi Backoffice**: *Inventaris > Review Hasil Opname* ([StockOpnameReview.php](../public/diego-music-store/app/Filament/Pages/StockOpnameReview.php))
*   **Trigger UI / Tombol**: Tombol aksi **`Finalisasi Periode`** pada halaman review persetujuan owner.
*   **Action Class**: [FinalizeStockOpnamePeriod.php](../public/diego-music-store/app/Actions/StockOpname/FinalizeStockOpnamePeriod.php) yang memanggil [ProcessStockOpnameComplete.php](../public/diego-music-store/app/Actions/StockOpname/ProcessStockOpnameComplete.php).
*   **Pencatatan Jurnal**:
    *   *Kondisi Surplus*: **Debit** `111401001` PERSEDIAAN | **Kredit** `511501001` BEBAN HPP
    *   *Kondisi Defisit*: **Debit** `511501001` BEBAN HPP / KERUGIAN OPNAME | **Kredit** `111401001` PERSEDIAAN

---

### 6. HR & Karyawan: Kasbon & Penggajian (`EmployeeCashAdvances`, `Payrolls`)

#### A. Pencairan Kasbon Karyawan
*   **Navigasi Backoffice**: *SDM > Kasbon Karyawan* ([EmployeeCashAdvanceResource](../public/diego-music-store/app/Filament/Resources/EmployeeCashAdvances/EmployeeCashAdvanceResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`approve`** pada [EmployeeCashAdvanceResource.php:L170](../public/diego-music-store/app/Filament/Resources/EmployeeCashAdvances/EmployeeCashAdvanceResource.php#L170).
*   **Action Class**: [ApproveCashAdvance.php](../public/diego-music-store/app/Actions/CashAdvance/ApproveCashAdvance.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `111301002` - PIUTANG KARYAWAN
    *   **Kredit**: Akun Kas / Bank pencairan

#### B. Pelunasan Kasbon Tunai di Luar Gaji
*   **Navigasi Backoffice**: *SDM > Kasbon Karyawan* ([EmployeeCashAdvanceResource](../public/diego-music-store/app/Filament/Resources/EmployeeCashAdvances/EmployeeCashAdvanceResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`earlyRepayment`** pada [EmployeeCashAdvanceResource.php:L224](../public/diego-music-store/app/Filament/Resources/EmployeeCashAdvances/EmployeeCashAdvanceResource.php#L224).
*   **Action Class**: [SettleCashAdvanceEarly.php](../public/diego-music-store/app/Actions/CashAdvance/SettleCashAdvanceEarly.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: Akun Kas / Bank penerimaan
    *   **Kredit**: `111301002` - PIUTANG KARYAWAN

#### C. Pembayaran Gaji Karyawan (Payroll)
*   **Navigasi Backoffice**: *SDM > Penggajian* ([PayrollResource](../public/diego-music-store/app/Filament/Resources/Payrolls/PayrollResource.php))
*   **Trigger UI / Tombol**: Tombol aksi header tabel **`payPayroll`** pada [ListPayrolls](../public/diego-music-store/app/Filament/Resources/Payrolls/Pages/ListPayrolls.php#L40).
*   **Action Class**: [ProcessPayrollPayment.php](../public/diego-music-store/app/Actions/Payroll/ProcessPayrollPayment.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `611101001` - BEBAN GAJI KARYAWAN
    *   **Kredit**: `111301002` - PIUTANG KARYAWAN (Potongan angsuran kasbon)
    *   **Kredit**: Akun Bank / Kas pembayaran gaji bersih (THP)

---

### 7. Manajemen Aset Tetap: Pelepasan & Penjualan (`AssetDisposals`)

#### A. Pelepasan / Penjualan Aset Tetap
*   **Navigasi Backoffice**: *Aset Tetap > Pelepasan Aset* ([AssetDisposalResource](../public/diego-music-store/app/Filament/Resources/AssetDisposals/AssetDisposalResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`post`** pada [AssetDisposalResource.php:L204](../public/diego-music-store/app/Filament/Resources/AssetDisposals/AssetDisposalResource.php#L204).
*   **Action Class**: [PostAssetDisposal.php](../public/diego-music-store/app/Actions/Accounting/PostAssetDisposal.php)
*   **Pencatatan Jurnal**:
    *   **Debit**: `129101001` - AKUMULASI PENYUSUTAN ASET
    *   **Debit**: KAS / BANK (Senilai kas yang diterima dari pembeli)
    *   **Debit**: `811201001` - RUGI PELEPASAN/PENGHAPUSAN ASET (Jika Rugi / Write-off)
    *   **Kredit**: `121101001` - ASET TETAP (Eliminasi harga perolehan)
    *   **Kredit**: `711201001` - LABA PENJUALAN ASET TETAP (Jika Untung)

---

### 8. Buku Besar, Jurnal Umum, & Tutup Buku (`JournalEntries`, `MonthlyClosingPage`, `ScheduledJournalEntries`)

#### A. Pembuatan, Edit, & Posting Jurnal Umum Manual
*   **Navigasi Backoffice**: *Akuntansi > Jurnal Umum* ([JournalEntryResource](../public/diego-music-store/app/Filament/Resources/JournalEntries/JournalEntryResource.php))
*   **Trigger UI / Tombol**:
    *   Pembuatan: Header action *Tambah Jurnal* via [CreateJournalEntry.php](../public/diego-music-store/app/Actions/Accounting/CreateJournalEntry.php).
    *   Edit: Tombol aksi tabel **`Edit`** via [UpdateJournalEntry.php](../public/diego-music-store/app/Actions/Accounting/UpdateJournalEntry.php).
    *   Posting: Tombol aksi tabel **`post`** via [PostJournalEntry.php](../public/diego-music-store/app/Actions/Accounting/PostJournalEntry.php).

#### B. Tutup Buku Bulanan & Pembukaan Periode
*   **Navigasi Backoffice**: *Akuntansi > Tutup Buku Bulanan* ([MonthlyClosingPage](../public/diego-music-store/app/Filament/Pages/Accounting/MonthlyClosingPage.php))
*   **Trigger UI / Tombol**: Tombol modal **`Tutup Buku Bulan Ini`** dan **`Buka Kembali Periode`**.
*   **Action Class**: [ExecuteMonthlyClosing.php](../public/diego-music-store/app/Actions/Accounting/ExecuteMonthlyClosing.php) & [ReopenMonthlyClosing.php](../public/diego-music-store/app/Actions/Accounting/ReopenMonthlyClosing.php).
*   **Pencatatan Jurnal**: Menolkan seluruh akun nominal ke `311301001` (Laba Tahun Berjalan) / `311201001` (Laba Ditahan).

#### C. Tutup Buku Akhir Tahun
*   **Navigasi Backoffice**: *Akuntansi > Tutup Buku Bulanan* ([MonthlyClosingPage](../public/diego-music-store/app/Filament/Pages/Accounting/MonthlyClosingPage.php))
*   **Trigger UI / Tombol**: Tombol **`executeYearEndClosingAction`** pada kartu Tutup Buku Tahunan.
*   **Action Class**: [ExecuteYearEndClosing.php](../public/diego-music-store/app/Actions/Accounting/ExecuteYearEndClosing.php).
*   **Pencatatan Jurnal**: Memindahkan saldo Laba Berjalan ke `311201001` (Laba Ditahan).

#### D. Jurnal Terjadwal & Amortisasi Berulang
*   **Navigasi Backoffice**: *Akuntansi > Jurnal Terjadwal* ([ScheduledJournalEntryResource](../public/diego-music-store/app/Filament/Resources/ScheduledJournalEntries/ScheduledJournalEntryResource.php))
*   **Trigger UI / Tombol**: Tombol aksi tabel **`Jalankan Sekarang`** pada [ScheduledJournalEntriesTable:L112](../public/diego-music-store/app/Filament/Resources/ScheduledJournalEntries/Tables/ScheduledJournalEntriesTable.php#L112).
*   **Action Class**: [ProcessScheduledJournalEntries.php](../public/diego-music-store/app/Actions/Accounting/ProcessScheduledJournalEntries.php).

---

### 9. Master Data & Migrasi Awal (`Products`, `Suppliers`, `Branches`)

#### A. Import Saldo Awal Persediaan
*   **Navigasi Backoffice**: *Produk > Daftar Produk* ([ProductResource](../public/diego-music-store/app/Filament/Resources/Products/ProductResource.php))
*   **Trigger UI / Tombol**: Tombol header **`importProducts`** pada [ListProducts](../public/diego-music-store/app/Filament/Resources/Products/Pages/ListProducts.php#L21).
*   **Action Class**: [ImportProducts.php](../public/diego-music-store/app/Actions/Product/ImportProducts.php)
*   **Pencatatan Jurnal**: **Debit** `111401001` PERSEDIAAN | **Kredit** MODAL DISETOR / LABA DITAHAN.

#### B. Import Saldo Awal Hutang Supplier
*   **Navigasi Backoffice**: *Pengadaan > Data Supplier* ([SupplierResource](../public/diego-music-store/app/Filament/Resources/Suppliers/SupplierResource.php))
*   **Trigger UI / Tombol**: Tombol header **`import`** pada [ListSuppliers](../public/diego-music-store/app/Filament/Resources/Suppliers/Pages/ListSuppliers.php#L18).
*   **Action Class**: [ImportSupplierDebts.php](../public/diego-music-store/app/Actions/Supplier/ImportSupplierDebts.php)
*   **Pencatatan Jurnal**: **Debit** MODAL DISETOR / LABA DITAHAN | **Kredit** `211101001` HUTANG DAGANG.

#### C. Sinkronisasi Akun COA Cabang Otomatis
*   **Navigasi Backoffice**: *Pengaturan > Cabang & Toko* ([BranchResource](../public/diego-music-store/app/Filament/Resources/Branches/BranchResource.php))
*   **Trigger UI / Tombol**: Tombol header **`sync_coa`** pada [ListBranches](../public/diego-music-store/app/Filament/Resources/Branches/Pages/ListBranches.php#L22).
*   **Action Class**: [EnsureBranchCoaAccounts.php](../public/diego-music-store/app/Actions/Branch/EnsureBranchCoaAccounts.php)
*   **Fungsi**: Memvalidasi dan membuat otomatis akun Buku Besar Persediaan Cabang (`11180...`), Piutang Antar Cabang (`11160...`), dan Hutang Antar Cabang (`21130...`).

---

## BAGIAN II: MODUL POINT OF SALE / FRONT OFFICE KASIR (POS / FO)

Seluruh modul di bawah ini dapat diakses langsung oleh kasir dan manager toko melalui antarmuka kasir (`/pos`).

### 1. Transaksi Penjualan Meja Kasir POS (`/pos`)
*   **Komponen Livewire**: [POS.php](../public/diego-music-store/app/Livewire/POS.php) & [PaymentModal.php](../public/diego-music-store/app/Livewire/Pos/PaymentModal.php)
*   **Trigger UI**: Tombol modal kasir **`Selesaikan Pembayaran (Bayar)`** pada modal pembayaran checkout.
*   **Action Class**: [CreatePOSSale.php](../public/diego-music-store/app/Actions/Sales/CreatePOSSale.php) & [UpdatePOSSale.php](../public/diego-music-store/app/Actions/Sales/UpdatePOSSale.php)
*   **Pencatatan Jurnal Otomatis**:
    *   **Debit**: Akun Kas / Bank (sesuai mapping akun COA metode bayar pada [PosPaymentMethods.php](../public/diego-music-store/app/Livewire/PosPaymentMethods.php)), `111301001` PIUTANG DAGANG (bon tempo), atau BIAYA COMPLIMENTARY
    *   **Kredit**: `411101001` - PENJUALAN (Pendapatan Penjualan Bersih setelah diskon & kupon)
    *   **Kredit**: `211201001` - HUTANG PPN / PPN KELUARAN (Pajak PPN 11% jika ada)
    *   **Debit**: `511501001` - HARGA POKOK PENJUALAN (Khusus barang fisik bermutasi)
    *   **Kredit**: `111401001` - PERSEDIAAN BARANG DAGANG
*   **Dampak Sub-Ledger Terkait**:
    *   Mengurangi stok fisik `ProductBranchStock` di cabang aktif kasir.
    *   Mencatat mutasi keluar pada `StockMovement` (type: `out`, ref: `POS`).
    *   Mengurangi saldo poin loyalitas pelanggan (`loyalty_points`) jika ditukar diskon.
    *   Menerbitkan pesanan perbaikan tiket service otomatis via [CreateServiceOrderFromSale.php](../public/diego-music-store/app/Actions/Service/CreateServiceOrderFromSale.php) jika terdapat item jasa.

### 2. Pelunasan Piutang Bon di Meja Kasir (`/pos/customer-payments` & `/pos/transactions`)
*   **Komponen Livewire**: [PosCustomerPayments.php](../public/diego-music-store/app/Livewire/PosCustomerPayments.php) dan modal baris transaksi pada [POSTransactions.php](../public/diego-music-store/app/Livewire/POSTransactions.php).
*   **Trigger UI**: Tombol **`Simpan Pembayaran`** pada form pelunasan piutang pelanggan.
*   **Action Class**: [SettleCustomerReceivable.php](../public/diego-music-store/app/Actions/Sales/SettleCustomerReceivable.php)
*   **Pencatatan Jurnal Otomatis**:
    *   **Debit**: Akun Kas / Rekening Bank kasir penerima dana
    *   **Kredit**: `111301001` - PIUTANG DAGANG
*   **Dampak Sub-Ledger Terkait**:
    *   Mengurangi sisa piutang nota penjualan (`sales`).
    *   Mengurangi kolom akumulasi hutang pelanggan (`customers.outstanding_debt`).

### 3. Titipan Dana / DP Inden Barang Pelanggan (`/pos/customer-deposits`)
*   **Komponen Livewire**: [PosCustomerDeposits.php](../public/diego-music-store/app/Livewire/PosCustomerDeposits.php)
*   **Alur & Action Classes**:
    1.  **Penerimaan Titipan Baru**:
        *   Trigger UI: Tombol **`Terima Deposit`**.
        *   Action Class: [CreateCustomerDeposit.php](../public/diego-music-store/app/Actions/CustomerDeposit/CreateCustomerDeposit.php)
        *   Jurnal: **Debit** KAS / BANK | **Kredit** `211601004` HUTANG PENITIPAN DANA PELANGGAN
    2.  **Penyelesaian / Penyerahan Barang Inden (*Settlement*)**:
        *   Trigger UI: Tombol **`Serahkan Barang (Settle)`**.
        *   Action Class: [SettleCustomerDeposit.php](../public/diego-music-store/app/Actions/CustomerDeposit/SettleCustomerDeposit.php)
        *   Jurnal Otomatis:
            *   **Debit**: `211601004` - HUTANG PENITIPAN DANA PELANGGAN (Eliminasi saldo titipan)
            *   **Debit**: KAS / BANK (Penerimaan sisa pembayaran jika harga barang > deposit)
            *   **Kredit**: `411101001` - PENJUALAN (Pengakuan total pendapatan penjualan)
            *   **Debit**: `511501001` - HARGA POKOK PENJUALAN
            *   **Kredit**: `111401001` - PERSEDIAAN BARANG DAGANG (Pengurangan stok gudang)
    3.  **Pembatalan Deposit Inden**:
        *   Trigger UI: Tombol **`Batalkan Deposit`**.
        *   Action Class: [CancelCustomerDeposit.php](../public/diego-music-store/app/Actions/CustomerDeposit/CancelCustomerDeposit.php)
        *   Mekanisme: Jurnal penerimaan deposit di-*hard delete* (*rollback*) dan kolom `customers.deposit_balance` dikurangi sejumlah nominal uang muka.

### 4. Retur Penjualan di Layar Kasir POS (`/pos/transactions`)
*   **Komponen Livewire**: [POSTransactions.php](../public/diego-music-store/app/Livewire/POSTransactions.php)
*   **Trigger UI**: Tombol baris transaksi **`Retur Barang`** pada daftar transaksi kasir.
*   **Action Class**: [CreateSalesReturn.php](../public/diego-music-store/app/Actions/Sales/CreateSalesReturn.php) $\rightarrow$ [PostSalesReturn.php](../public/diego-music-store/app/Actions/Sales/PostSalesReturn.php)
*   **Pencatatan Jurnal Otomatis**:
    *   **Debit**: `411201001` - RETUR PENJUALAN (Pembalikan Pendapatan)
    *   **Kredit**: `111301001` - PIUTANG DAGANG (Diprioritaskan mengeliminasi sisa piutang nota tempo)
    *   **Kredit**: KAS / BANK (Pengembalian dana tunai jika ada nilai lebih)
    *   **Debit**: `111401001` - PERSEDIAAN BARANG DAGANG (Stok fisik kembali ke toko)
    *   **Kredit**: `511501001` - HARGA POKOK PENJUALAN (Pembalikan HPP)

### 5. Kas Masuk & Kas Keluar Harian Kasir (`/pos/daily-cash`)
*   **Komponen Livewire**: [POSDailyCash.php](../public/diego-music-store/app/Livewire/POSDailyCash.php)
*   **Trigger UI**: Tombol modal **`+ Kas Masuk`** dan **`- Kas Keluar`** pada panel petty cash kasir.
*   **Action Class**: [CreateCashTransaction.php](../public/diego-music-store/app/Actions/CashManagement/CreateCashTransaction.php) $\rightarrow$ [PostCashTransaction.php](../public/diego-music-store/app/Actions/CashManagement/PostCashTransaction.php)
*   **Pencatatan Jurnal Otomatis**:
    *   *Kas Masuk Kasir*: **Debit** `111101001` KAS TOKO | **Kredit** Akun Sumber yang dipilih
    *   *Kas Keluar Operasional Kasir (Petty Cash Drawer)*: **Debit** Akun Beban (Listrik, Air, Konsumsi, ATK, Kurir) | **Kredit** `111101001` KAS TOKO
*   *Catatan Operasional*: Transaksi otomatis terikat pada `cash_session_id` aktif kasir dan mempengaruhi rekonsiliasi kas pada Z-Report penutupan shift.

### 6. Penggajian / Payroll Cabang via Layar Kasir (`/pos/payroll`)
*   **Komponen Livewire**: [PosPayrollManagement.php](../public/diego-music-store/app/Livewire/PosPayrollManagement.php)
*   **Trigger UI**: Tombol **`Bayar Gaji`** oleh store manager/owner di menu POS.
*   **Action Class**: [ProcessPayrollPayment.php](../public/diego-music-store/app/Actions/Payroll/ProcessPayrollPayment.php)
*   **Pencatatan Jurnal Otomatis**:
    *   **Debit**: `611101001` - BEBAN GAJI KARYAWAN
    *   **Kredit**: `111301002` - PIUTANG KARYAWAN (Pemotongan angsuran kasbon)
    *   **Kredit**: Akun Bank / Kas pembayaran gaji bersih

### 7. Finalisasi Stok Opname via Layar Kasir (`/pos/stock-opname-review`)
*   **Komponen Livewire**: [StockOpnameReview.php](../public/diego-music-store/app/Livewire/StockOpnameReview.php)
*   **Trigger UI**: Tombol **`Finalisasi Periode`** pada review tugas lokasi.
*   **Action Class**: [FinalizeStockOpnamePeriod.php](../public/diego-music-store/app/Actions/StockOpname/FinalizeStockOpnamePeriod.php) $\rightarrow$ [ProcessStockOpnameComplete.php](../public/diego-music-store/app/Actions/StockOpname/ProcessStockOpnameComplete.php)
*   **Pencatatan Jurnal Otomatis**: **Debit / Kredit** Persediaan (`111401001`) vs Beban Selisih Stok Opname / HPP.

### 8. Sesi Kasir & Rekonsiliasi Kas Laci (Z-Report) (`/pos/session`)
*   **Komponen Livewire**: [POSCashSession.php](../public/diego-music-store/app/Livewire/POSCashSession.php) & Controller [ZReportController.php](../public/diego-music-store/app/Http/Controllers/POS/ZReportController.php)
*   **Action Classes**: [OpenCashSession.php](../public/diego-music-store/app/Actions/CashSession/OpenCashSession.php), [CloseCashSession.php](../public/diego-music-store/app/Actions/CashSession/CloseCashSession.php), [ReopenCashSession.php](../public/diego-music-store/app/Actions/CashSession/ReopenCashSession.php)
*   **Fungsi Rekonsiliasi**: Mengontrol modal awal kasir, mengakumulasi total uang masuk dari penjualan tunai dan mutasi harian, memvalidasi selisih kas fisik laci kasir (*cash discrepancy*), dan mencetak laporan Z-Report resmi.

### 9. Laporan Penjualan & Piutang Kasir POS (`/pos/reports/`)
*   [PosReportsSales.php](../public/diego-music-store/app/Livewire/PosReportsSales.php): Laporan omset, laba kotor harian, per kategori dan metode bayar kasir.
*   [PosReportsArAging.php](../public/diego-music-store/app/Livewire/PosReportsArAging.php): Laporan analisis umur piutang kasir (0-30, 31-60, 61-90, >90 hari).
*   [PosReportsArSettlement.php](../public/diego-music-store/app/Livewire/PosReportsArSettlement.php): Laporan riwayat penerimaan pelunasan piutang bon toko.
*   [PosReportsDailyCash.php](../public/diego-music-store/app/Livewire/PosReportsDailyCash.php): Laporan riwayat kas masuk & kas keluar petty cash laci kasir.
*   [PosReportsStockPrices.php](../public/diego-music-store/app/Livewire/PosReportsStockPrices.php): Laporan katalog harga eceran, grosir, dan saldo stok cabang.

---

## BAGIAN III: LAPORAN KEUANGAN BACKOFFICE (READ-ONLY LEDGER REPORTING)

Halaman laporan keuangan berikut membaca data langsung dari buku besar (`journal_items` & `accounts`) tanpa menulis jurnal baru:
1.  **Laporan Posisi Keuangan (Neraca)**: [BalanceSheet.php](../public/diego-music-store/app/Filament/Pages/Reports/BalanceSheet.php)
2.  **Laporan Laba Rugi**: [IncomeStatement.php](../public/diego-music-store/app/Filament/Pages/Reports/IncomeStatement.php)
3.  **Neraca Percobaan (Trial Balance)**: [TrialBalance.php](../public/diego-music-store/app/Filament/Pages/Reports/TrialBalance.php)
4.  **Buku Besar Umum (General Ledger)**: [GeneralLedger.php](../public/diego-music-store/app/Filament/Pages/Reports/GeneralLedger.php)
5.  **Buku Kas & Bank**: [BankLedger.php](../public/diego-music-store/app/Filament/Pages/Reports/BankLedger.php)
6.  **Buku Pembantu Hutang Supplier (Vendor Ledger)**: [VendorLedger.php](../public/diego-music-store/app/Filament/Pages/Reports/VendorLedger.php)
7.  **Laporan Jurnal Transaksi**: [JournalReport.php](../public/diego-music-store/app/Filament/Pages/Reports/JournalReport.php)

---

## Matriks Rekapitulasi Pemetaan Backoffice & POS

| Modul / Antarmuka | Navigasi / Trigger UI | Action Class Terkait | Dampak Buku Besar / Jurnal |
|---|---|---|---|
| **Purchase Order** | Tombol `payDp` | `PayPurchaseOrderDownPayment` | Debit: Uang Muka Pembelian (`111201006`), Kredit: Kas/Bank |
| **Transaksi Pembelian** | Tombol `post` | `PostPurchaseTransaction` | Debit: Persediaan (`111401001`) & PPN Masukan, Kredit: DP/Hutang/Kas |
| **Bayar Hutang Supplier** | Form Save / Post | `ProcessSupplierPaymentComplete` | Debit: Hutang Dagang (`211101001`), Kredit: Kas/Bank |
| **Batal Bayar Supplier** | Tombol `cancel` | `CancelSupplierPayment` | Mengubah status Jurnal menjadi `cancelled`, pulihkan hutang |
| **Retur Pembelian** | Tombol `post` | `PostPurchaseReturn` | Debit: Hutang/Kas/DP, Kredit: Persediaan (`111401001`) |
| **Faktur Penjualan B2B** | Tombol `post` | `PostSalesInvoice` | Debit: Piutang/Kas, Kredit: Penjualan & PPN Keluaran. Debit HPP, Kredit Persediaan |
| **Retur Penjualan Backoffice** | Tombol `post` | `PostSalesReturn` | Debit: Retur Penjualan & Persediaan, Kredit: Piutang/Kas & HPP |
| **Transaksi Kas/Bank Backoffice** | Form Save / Edit | `PostCashTransaction` | Debit: Akun Tujuan, Kredit: Akun Sumber |
| **Transfer Stok Cabang** | Tombol `proses_transfer` | `CompleteStockTransfer` | Jurnal Resiprokal 4 Akun (Persediaan Cabang & R/K Antar Cabang) |
| **Review Stok Opname Backoffice** | Tombol `Finalisasi` | `FinalizeStockOpnamePeriod` | Debit/Kredit: Persediaan (`111401001`) vs Selisih HPP/Beban |
| **Pencairan Kasbon** | Tombol `approve` | `ApproveCashAdvance` | Debit: Piutang Karyawan (`111301002`), Kredit: Kas/Bank |
| **Pelunasan Kasbon Dini** | Tombol `earlyRepayment` | `SettleCashAdvanceEarly` | Debit: Kas/Bank, Kredit: Piutang Karyawan (`111301002`) |
| **Penggajian Payroll Backoffice** | Tombol `payPayroll` | `ProcessPayrollPayment` | Debit: Beban Gaji (`611101001`), Kredit: Piutang Karyawan & Kas/Bank |
| **Pelepasan Aset** | Tombol `post` | `PostAssetDisposal` | Debit: Akumulasi Penyusutan & Kas, Kredit: Nilai Perolehan Aset, Selisih Laba/Rugi |
| **Jurnal Umum Manual** | Tombol `post` / Form | `CreateJournalEntry`, `PostJournalEntry` | Validasi simetri Debit == Kredit > 0 |
| **Tutup Buku Bulanan** | Tombol `Tutup Buku` | `ExecuteMonthlyClosing` | Pengosongan akun nominal ke Laba Berjalan / Ditahan |
| **Buka Tutup Buku** | Tombol `Buka Kembali` | `ReopenMonthlyClosing` | Hard delete jurnal penutup periode terkait |
| **Tutup Buku Tahunan** | Tombol `Tutup Buku Tahunan` | `ExecuteYearEndClosing` | Pemindahan saldo Laba Tahun Berjalan ke Laba Ditahan |
| **Jurnal Terjadwal** | Tombol `Jalankan Sekarang` | `ProcessScheduledJournalEntries` | Eksekusi otomatis jurnal akrual/amortisasi berulang |
| **Import Stok Awal** | Tombol `importProducts` | `ImportProducts` | Debit: Persediaan (`111401001`), Kredit: Modal/Laba Ditahan |
| **Import Hutang Awal** | Tombol `import` | `ImportSupplierDebts` | Debit: Modal/Laba Ditahan, Kredit: Hutang Dagang (`211101001`) |
| **Sinkronisasi COA Cabang** | Tombol `sync_coa` | `EnsureBranchCoaAccounts` | Provisioning otomatis akun 11180, 11160, dan 21130 per cabang |
| **Penjualan POS Kasir** | POS Checkout `Bayar` | `CreatePOSSale`, `UpdatePOSSale` | Debit: Kas/Bank/Piutang, Kredit: Penjualan & PPN. Debit HPP, Kredit Persediaan |
| **Pelunasan Bon Kasir** | Tombol `Simpan Pembayaran` | `SettleCustomerReceivable` | Debit: Kas/Bank, Kredit: Piutang Dagang (`111301001`) |
| **Deposit Inden Kasir** | Tombol `Terima Deposit` | `CreateCustomerDeposit` | Debit: Kas/Bank, Kredit: Hutang Penitipan Dana (`211601004`) |
| **Settlement Inden Kasir** | Tombol `Serahkan Barang` | `SettleCustomerDeposit` | Debit: Titipan Pelanggan & Kas, Kredit: Penjualan. Debit HPP, Kredit Persediaan |
| **Batal Inden Kasir** | Tombol `Batalkan Deposit` | `CancelCustomerDeposit` | Hard delete jurnal penerimaan deposit dan kurangi saldo deposit pelanggan |
| **Retur Penjualan Kasir POS** | Tombol `Retur Barang` | `CreateSalesReturn` $\rightarrow$ `PostSalesReturn` | Debit: Retur Penjualan & Persediaan, Kredit: Piutang/Kas & HPP |
| **Kas Masuk Laci Kasir** | Tombol `+ Kas Masuk` | `CreateCashTransaction` | Debit: Kas Toko (`111101001`), Kredit: Akun Sumber |
| **Kas Keluar Operasional Kasir** | Tombol `- Kas Keluar` | `CreateCashTransaction` | Debit: Akun Beban (Listrik, Air, ATK), Kredit: Kas Toko (`111101001`) |
| **Penggajian Payroll POS** | Tombol `Bayar Gaji` | `ProcessPayrollPayment` | Debit: Beban Gaji (`611101001`), Kredit: Piutang Karyawan & Kas/Bank |
| **Review Opname POS** | Tombol `Finalisasi Periode` | `FinalizeStockOpnamePeriod` | Debit/Kredit: Persediaan (`111401001`) vs Selisih HPP/Beban |

---
*Laporan ini diaudit secara mendalam menyisir seluruh antarmuka Filament Backoffice & POS Kasir per 24 Oktober 2026.*
