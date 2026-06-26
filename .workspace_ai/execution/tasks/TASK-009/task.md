# Task: TASK-009 - Database Migration & Eloquent Model for Cash Sessions

## Description
Buat tabel database `cash_sessions` untuk menampung data sesi kasir harian beserta model Eloquent `CashSession` dan relasi-relasinya.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Front Desk & POS Dasar
- **Feature**: FEATURE-004 - Sesi Kasir Harian & Laci Kas
- **Status**: Ready

## Acceptance Criteria
- [ ] Migration untuk tabel `cash_sessions` berhasil dibuat dan dijalankan.
- [ ] Kolom-kolom minimal di `cash_sessions`: `id`, `user_id`, `cabang_id`, `opened_at`, `closed_at`, `opening_cash` (modal awal), `expected_cash` (ekspektasi kas), `actual_cash` (fisik kas), `difference` (selisih), `status` (enum: open, closed), `closed_by_user_id`, `notes`.
- [ ] Model `CashSession` memiliki relasi `belongsTo` ke `User` (kasir), `Branch` (cabang), dan `closedBy` (supervisor yang menutup/menyetujui selisih jika diperlukan).

## Assignee
- Developer
