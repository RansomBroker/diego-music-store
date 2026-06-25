# Epic: EPIC-001 - Master Data Setup & Basic Config

## Description
Epic ini adalah fondasi awal sistem ERP Diego Music Store. Tujuannya adalah menyiapkan isolasi data multi-cabang di level database, otentikasi pengguna, manajemen hak akses (RBAC), serta manajemen data master dasar (Pelanggan, Supplier, Gudang/Cabang, Barang & Varian, serta COA dasar).

## User Stories / Features
- [ ] **FEATURE-001: Multi-Cabang & User RBAC Setup**
- [ ] **FEATURE-002: CRUD Master Barang & Varian (Bundling & Jasa)**
- [ ] **FEATURE-003: CRUD Pelanggan, Supplier, & COA Dasar**

## Technical Roadmap & Dependencies
- Tergantung pada: Project Setup (Laravel 12 & Docker)
- Target Waktu: Sprint 1 (Hari 1 - 12)

## Acceptance Criteria
- [ ] Database memiliki pemisahan `cabang_id` di setiap data transaksi/operasional.
- [ ] Pengguna hanya dapat mengakses cabang yang ditugaskan kepada mereka.
- [ ] CRUD untuk data master dasar (Cabang, Barang, Varian, Pelanggan, Supplier, COA) dapat dilakukan dengan benar.

## Status
- **Status**: Active
- **Progress**: 0%
