# Task: TASK-021 - Customer Debt Payment Tracking (Piutang) and History

## Description
Buat fitur pelacakan piutang pelanggan atas transaksi POS kredit/tempo, halaman kelola pelunasan cicilan piutang di Filament, dan update otomatis status invoice serta pencatatan jurnal akuntansi sederhana terkait penerimaan kas piutang.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-008 - Pelunasan Piutang & Laporan POS
- **Status**: Ready

## Acceptance Criteria
- [ ] Buat skema migrasi tabel `receivable_payments` (`id`, `sale_id`, `payment_date`, `amount_paid`, `payment_method`, `notes`, `timestamps`) dan jalankan migrasinya.
- [ ] Sediakan CRUD `ReceivablePaymentResource` di Filament untuk mencatat pelunasan cicilan piutang per invoice pelanggan.
- [ ] Sisa piutang berjalan dihitung otomatis dan memperbarui kolom status pembayaran invoice POS terkait (`Unpaid`, `Partially Paid`, `Paid`).
- [ ] Saldo piutang global per customer ter-update di database.

## Assignee
- Developer
