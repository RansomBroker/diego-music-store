# Feature: FEATURE-003 - CRUD Pelanggan, Supplier, & COA Dasar

## Parent Epic
- EPIC-001 - Master Data Setup & Basic Config

## Description
Fitur ini mencakup pengelolaan entitas eksternal (Pelanggan dan Supplier) serta pondasi struktur keuangan berupa Chart of Accounts (COA/Daftar Akun) dasar untuk pencatatan akuntansi double-entry di sprint berikutnya.

## Detailed Specifications
1. **Data Pelanggan (Customer)**:
   - Kolom: Nama, No Telepon, Email, Alamat, Status Loyalty Member, Poin Terkumpul, Saldo Deposit.
   - Terintegrasi dengan sistem poin belanja (Loyalty Program).
2. **Data Supplier / Vendor**:
   - Kolom: Nama, Kontak Person, No Telepon, Email, Alamat, Nomor Rekening Bank, Saldo Hutang Berjalan.
3. **Chart of Accounts (COA) Dasar**:
   - Kolom: Kode Akun (unik), Nama Akun, Klasifikasi Akun (Aset, Kewajiban, Ekuitas, Pendapatan, Beban), Status Aktif.
   - Seeder COA standar akuntansi Indonesia (Kas, Bank, Piutang Dagang, Persediaan Barang, Hutang Dagang, Modal, Pendapatan Penjualan, HPP, Biaya Operasional, dll.).

## Acceptance Criteria
- [x] Database tabel `customers`, `suppliers`, dan `accounts` (COA) telah didefinisikan dengan migration.
- [x] Seeder untuk daftar akun COA dasar berhasil dijalankan.
- [x] Tersedia CRUD untuk Pelanggan, Supplier, dan COA di Filament Backoffice.
- [x] Validasi keunikan nomor telepon pelanggan dan kode akun COA.

## Technical Implementation Details
- Buat migration dan seeder.
- Daftarkan Filament Resources untuk `CustomerResource`, `SupplierResource`, dan `AccountResource` (COA).
- Kelompokkan `AccountResource` di bawah navigation group Settings atau Akuntansi.
