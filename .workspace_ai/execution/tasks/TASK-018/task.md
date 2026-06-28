# Task: TASK-018 - Database Migration & Eloquent Models for Sales Transactions & Payments (POS)

## Description
Buat skema database dan model Eloquent untuk transaksi penjualan retail (POS), item penjualan, dan jenis pembayaran. Pastikan skema mendukung integrasi dengan multi-cabang (`branch_id`) dan sales representative (`user_id`).

## Technical Details
- **Role**: Developer / Architect
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-007 - Core POS (Point of Sale)
- **Status**: Ready

## Acceptance Criteria
- [ ] Migration untuk tabel `sales` (`id`, `branch_id`, `cash_session_id`, `customer_id`, `sales_rep_id`, `invoice_number`, `invoice_date`, `subtotal`, `discount_amount`, `tax_amount`, `grand_total`, `payment_method` [enum: cash, debit, credit], `status` [enum: draft, completed, canceled], `timestamps`) berhasil dibuat dan dijalankan.
- [ ] Migration untuk tabel `sale_items` (`id`, `sale_id`, `product_variant_id`, `quantity`, `unit_price`, `discount_amount`, `total_price`, `timestamps`) berhasil dibuat dan dijalankan.
- [ ] Model Eloquent (`Sale`, `SaleItem`) beserta relasi-relasinya (ke `Branch`, `Customer`, `User` as sales representative, `CashSession`) terdefinisi dengan lengkap.

## Assignee
- Developer
