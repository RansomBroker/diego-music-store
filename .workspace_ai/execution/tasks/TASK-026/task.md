# Task: TASK-026 - Menu Utility POS (Setting Privilege User, Register Nama Toko, Setting Struk & Invoice, Cetak Barcode)

## Description
Menambahkan menu Utility pada sidebar POS beserta 4 sub-fitur:
1. Setting Privilage User (Kelola Role & Hak Akses User)
2. Register Nama Toko (Profil Toko, Logo, Alamat, Kontak)
3. Setting Struk dan Invoice (Konfigurasi Header, Footer, Ukuran Kertas, Informasi Struk & Faktur)
4. Cetak Barcode (Generator & Template Cetak Barcode Produk/Varian)

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-001 - POS & Store Utility
- **Feature**: FEATURE-010 - POS Utility Modules
- **Status**: Done

## Acceptance Criteria
- [x] Menambahkan menu Utility pada sidebar POS (`sidebar.blade.php`) dengan flyout submenu untuk 4 fitur.
- [x] Membuat migrasi database `2026_07_20_000005_create_receipt_settings_table.php` dan model `ReceiptSetting`.
- [x] Membuat Helper `BarcodeHelper` di `app/Helpers/BarcodeHelper.php` untuk merender kode barcode Code128 format SVG.
- [x] Membuat Action Class:
  - `App\Actions\Privilege\CreateRole`
  - `App\Actions\Privilege\UpdateRolePermissions`
  - `App\Actions\Store\UpdateBranchProfile`
  - `App\Actions\Setting\UpdateReceiptSettings`
- [x] Membuat Livewire Component & Blade Views:
  - `App\Livewire\PosPrivileges` & `resources/views/livewire/pos-privileges.blade.php`
  - `App\Livewire\PosStoreProfile` & `resources/views/livewire/pos-store-profile.blade.php`
  - `App\Livewire\PosReceiptSettings` & `resources/views/livewire/pos-receipt-settings.blade.php`
  - `App\Livewire\PosBarcodePrint` & `resources/views/livewire/pos-barcode-print.blade.php`
- [x] Membuat Printable Barcode View `resources/views/pos/barcode-print-sheet.blade.php` dan Route `/pos/barcode-print/sheet`.
- [x] Mengintegrasikan `ReceiptSetting` pada tampilan cetak struk POS (`resources/views/pos/receipt.blade.php`).
- [x] Mendaftarkan route untuk keempat fitur pada `routes/web.php`.
- [x] Membuat Unit & Integration Test untuk Action & Feature di `tests/Feature/PosUtilityTest.php`.
