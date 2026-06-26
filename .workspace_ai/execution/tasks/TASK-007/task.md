# Task: TASK-007 - Eloquent Models & Relational/Validation Logic for Customers, Suppliers, & COA

## Description
Buat Model Eloquent untuk `Customer`, `Supplier`, dan `Account` (COA). Tambahkan relasi, validation rules (seperti format email, keunikan nomor telepon, keunikan kode akun), dan logic standard (seperti penentuan format kode COA, default loyalty status).

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-003 - CRUD Pelanggan, Supplier, & COA Dasar
- **Status**: Done

## Acceptance Criteria
- [x] Model `Customer` terdefinisi dengan fillable attributes (nama, no telepon, email, alamat, loyalty member status, poin, deposit).
- [x] Model `Supplier` terdefinisi dengan fillable attributes (nama, kontak, no telepon, email, alamat, no rekening bank, hutang).
- [x] Model `Account` terdefinisi dengan fillable attributes (kode, nama, klasifikasi akun, is_active).
- [x] Model validation rules dan scope teruji dengan baik.

## Assignee
- Developer
