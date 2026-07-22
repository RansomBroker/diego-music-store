# Subtasks for TASK-026

- [x] 1. Migration & Model: `receipt_settings` table & `ReceiptSetting` model
- [x] 2. Barcode Helper: `app/Helpers/BarcodeHelper.php`
- [x] 3. Actions:
  - [x] `App\Actions\Privilege\CreateRole`
  - [x] `App\Actions\Privilege\UpdateRolePermissions`
  - [x] `App\Actions\Store\UpdateBranchProfile`
  - [x] `App\Actions\Setting\UpdateReceiptSettings`
- [x] 4. Livewire Components & Views:
  - [x] `PosPrivileges`
  - [x] `PosStoreProfile`
  - [x] `PosReceiptSettings`
  - [x] `PosBarcodePrint`
- [x] 5. Controller & Route Cetak Barcode Sheet
- [x] 6. Update `resources/views/pos/receipt.blade.php` to use `ReceiptSetting`
- [x] 7. Update POS Sidebar (`sidebar.blade.php`) & Routes (`routes/web.php`)
- [x] 8. Feature Tests: `tests/Feature/PosUtilityTest.php`
