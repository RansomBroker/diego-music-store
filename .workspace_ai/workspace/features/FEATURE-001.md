# Feature: FEATURE-001 - Multi-Cabang & User RBAC Setup

## Parent Epic
- EPIC-001 - Master Data Setup & Basic Config

## Description
Fitur ini mengimplementasikan isolasi data multi-cabang (multi-tenant di level database) dan sistem login terpusat dengan Role-Based Access Control (RBAC). 

## Detailed Specifications
1. **Multi-Cabang**:
   - Setiap transaksi (penjualan, mutasi stok, jurnal, dll.) harus memiliki kolom `cabang_id`.
   - Admin dan Kasir hanya bisa mengakses data yang memiliki `cabang_id` sesuai dengan cabang aktif mereka saat login.
   - Owner memiliki akses global ke semua cabang dan dapat melihat laporan konsolidasi.
2. **User Roles & RBAC (Role-Based Access Control)**:
   - **Owner**: Akses penuh ke semua fitur dan cabang.
   - **Admin**: Akses manajemen data master, inventaris, dan input keuangan di cabangnya.
   - **Kasir**: Akses menu POS transaksi dan sesi laci kas harian.
   - **Sales**: Akses input penjualan POS, data komisi pribadi.
   - **Teknisi**: Akses papan Kanban servis instrumen dan input sparepart.

## Acceptance Criteria
- [ ] Database tabel `cabang` dan kolom `cabang_id` di tabel relasional telah terbuat.
- [ ] Pengguna dengan role `Kasir` cabang A tidak bisa melihat stok atau transaksi cabang B.
- [ ] Opsi cabang muncul saat login jika user ditugaskan di lebih dari satu cabang.

## Technical Implementation Details
- Buat migration untuk tabel `cabang` dan pivot tabel `cabang_user`.
- Gunakan Spatie Laravel Permission (atau policy kustom Laravel) untuk RBAC.
- Implementasikan Global Scope di Laravel Eloquent agar query otomatis memfilter `cabang_id` berdasarkan session user.
