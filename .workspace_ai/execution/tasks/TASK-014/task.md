# Task: TASK-014 - Database Migration & Eloquent Models for Purchase Orders & Delivery Orders

## Description
Buat skema database dan model Eloquent untuk modul pemesanan barang ke supplier (Purchase Order - PO), penerimaan barang (Delivery Order - DO), serta tambahkan kolom `hpp` pada tabel `product_branch_stocks` untuk mendukung pencatatan HPP Rata-rata Terbobot (Weighted Average) per cabang.

## Technical Details
- **Role**: Developer / Architect
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-004 - Satuan Produk & Pengelolaan PO/DO
- **Status**: Done

## Acceptance Criteria
- [x] Migration untuk tabel `purchase_orders` (`id`, `supplier_id`, `po_number`, `order_date`, `status` [enum: draft, approved, closed], `total_amount`, `notes`, `timestamps`) dan `purchase_order_items` (`id`, `purchase_order_id`, `product_variant_id`, `quantity`, `price`, `timestamps`) berhasil dibuat dan dijalankan.
- [x] Migration untuk tabel `delivery_orders` (`id`, `purchase_order_id`, `branch_id`, `do_number`, `received_date`, `status` [enum: draft, received], `shipping_cost` [ongkos kirim], `notes`, `timestamps`) dan `delivery_order_items` (`id`, `delivery_order_id`, `product_variant_id`, `quantity_ordered`, `quantity_received`, `timestamps`) berhasil dibuat dan dijalankan.
- [x] Migration untuk menambahkan kolom `hpp` (bigint, default 0) pada tabel `product_branch_stocks` berhasil dijalankan.
- [x] Model Eloquent (`PurchaseOrder`, `PurchaseOrderItem`, `DeliveryOrder`, `DeliveryOrderItem`) dan relasi-relasinya terdefinisi dengan lengkap.

## Assignee
- Developer
