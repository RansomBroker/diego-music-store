# Product Requirements Document (PRD) Reference

This file serves as the main entry point to the requirements of the ERP Diego Music Store. The full detailed requirements document is located at:
- [PRD_DIEGO_MUSIC_STORE.md](../../PRD_DIEGO_MUSIC_STORE.md)
- [KEBUTUHAN_APLIKASI_BISNIS.md](../../KEBUTUHAN_APLIKASI_BISNIS.md)

## Core Fungsionalitas Sistem

### 1. Front Desk (POS System & CRM)
- **POS Transaksi Penjualan**: Mendukung barcode, pricing tier (default/member/cabang), Mix Payment, PPN, dan catatan kasir.
- **Sesi Kasir**: Sistem laci kasir harian (buka sesi, input kas awal, tutup sesi blind counting, otorisasi batal tutup).
- **CRM**: Loyalty member & poin, notifikasi WhatsApp Gateway (Invoice PDF, Reminder, Broadcast).
- **Manajemen Servis**: Pelacakan servis instrumen (Kanban), input sparepart & jasa, konversi langsung ke POS invoice.

### 2. Back Office (Multi-Cabang, Accounting, Inventory, & Payroll)
- **Multi-Cabang**: Data terisolasi per cabang, hak akses user, offline sync (Service Worker & IndexedDB FIFO queue).
- **Akuntansi & Stok**: double-entry accounting otomatis, Weighted Average HPP, Purchase Order (PO) & Delivery Order (DO), Mutasi & Stok Opname, Depresiasi Aset.
- **Kepegawaian & Payroll**: Absensi fingerprint & foto geotagging, kasbon cicilan payroll, penalty point denda telat, komisi sales bertingkat, slip gaji PDF/WA.

### 3. Dashboard & Analytics
- **Dashboard Owner**: Widget total omset, profit, Pareto 80/20, turn over stock, traffic jam pengunjung.
- **Dashboard Sales**: Progress bar target bulanan/harian, status komisi, leaderboard top 3, produk fokus bulanan.
