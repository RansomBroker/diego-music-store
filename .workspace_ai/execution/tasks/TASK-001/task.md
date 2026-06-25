# Task: TASK-001 - Database Migration & Cabang Table Setup

## Description
Membuat rancangan basis data awal, termasuk tabel `cabang`, migration Laravel untuk tabel-tabel utama (users, cabang, cabang_user), dan implementasi pemisahan data (data isolation) menggunakan scope global.

## Technical Details
- **Role**: Architect / Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-001 - Multi-Cabang & User RBAC Setup
- **Status**: Analysis

## Acceptance Criteria
- [ ] Database migration berhasil dijalankan tanpa error di container Docker.
- [ ] Relasi *Many-to-Many* antara `users` dan `cabang` terbentuk melalui pivot tabel `cabang_user`.
- [ ] Skema database mendukung multi-tenant cabang secara modular.

## Assignee
- Architect
