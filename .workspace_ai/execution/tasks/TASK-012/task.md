# Task: TASK-012 - Database Migration, Eloquent Model, & Filament CRUD for Units (Satuan Produk)

## Description
Buat tabel database `units` untuk menyimpan satuan produk (seperti Pcs, Set, Unit), buat model Eloquent `Unit`, dan buat Filament Resource `UnitResource` di Back Office agar admin dapat mengelola daftar satuan produk.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-004 - Satuan Produk & Pengelolaan PO/DO
- **Status**: Done

## Acceptance Criteria
- [x] Migration untuk tabel `units` (`id`, `name`, `code`, `is_active`, `timestamps`) berhasil dibuat dan dijalankan.
- [x] Model Eloquent `Unit` terdefinisi dengan fillable attributes (`name`, `code`, `is_active`) dan casts (`is_active` => 'boolean').
- [x] Filament Resource `UnitResource` berhasil dibuat di bawah grup navigasi **Master Data** (atau grup relevan).
- [x] Form `UnitResource` memiliki input `name` (required), `code` (required, unique), dan toggle `is_active` (default true).
- [x] Table `UnitResource` menampilkan daftar satuan produk beserta status keaktifannya.

## Assignee
- Developer
