# Task: TASK-013 - Integrate Units (Satuan) into Products Model and Form

## Description
Tambahkan kolom `unit_id` ke tabel `products` melalui migrasi, definisikan relasi `belongsTo` pada model `Product`, dan integrasikan input select Satuan (UoM) ke dalam `ProductForm` / `ProductResource` Filament.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-004 - Satuan Produk & Pengelolaan PO/DO
- **Status**: Done

## Acceptance Criteria
- [x] Migration untuk menambahkan kolom `unit_id` (foreign key ke `units` dengan constraint `restrict`) pada tabel `products` berhasil dijalankan.
- [x] Model `Product` memiliki relasi `unit()` yang mengembalikan `belongsTo(Unit::class)`.
- [x] Tab **Informasi Umum** pada form input produk (`ProductForm.php`) memiliki select field `unit_id` yang mengambil data dari model `Unit` yang aktif (`is_active = true`).
- [x] Input select `unit_id` bersifat wajib diisi (*required*).
- [x] Kolom "Satuan" ditampilkan pada tabel daftar produk (`ProductResource` index table).

## Assignee
- Developer
