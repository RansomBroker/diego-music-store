# Subtasks: TASK-014 - Database Migration & Eloquent Models for Purchase Orders & Delivery Orders

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang skema tabel PO & DO beserta detail itemnya.
  - [x] Rancang penambahan kolom `hpp` ke tabel `product_branch_stocks` agar pencatatan HPP terisolasi per cabang.
- [x] **Implementation**:
  - [x] Buat migration untuk tabel `purchase_orders` & `purchase_order_items`.
  - [x] Buat migration untuk tabel `delivery_orders` & `delivery_order_items`.
  - [x] Buat migration `add_hpp_to_product_branch_stocks_table`.
  - [x] Jalankan `./docker-artisan.sh migrate`.
  - [x] Buat model Eloquent `PurchaseOrder`, `PurchaseOrderItem`, `DeliveryOrder`, `DeliveryOrderItem` beserta relasi-relasinya.
- [x] **Verification**:
  - [x] Verifikasi kebenaran migrasi database dan relasi Eloquent melalui Tinker atau test case.
