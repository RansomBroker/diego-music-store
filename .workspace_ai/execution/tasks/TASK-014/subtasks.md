# Subtasks: TASK-014 - Database Migration & Eloquent Models for Purchase Orders & Delivery Orders

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang skema tabel PO & DO beserta detail itemnya.
  - [ ] Rancang penambahan kolom `hpp` ke tabel `product_branch_stocks` agar pencatatan HPP terisolasi per cabang.
- [ ] **Implementation**:
  - [ ] Buat migration untuk tabel `purchase_orders` & `purchase_order_items`.
  - [ ] Buat migration untuk tabel `delivery_orders` & `delivery_order_items`.
  - [ ] Buat migration `add_hpp_to_product_branch_stocks_table`.
  - [ ] Jalankan `./docker-artisan.sh migrate`.
  - [ ] Buat model Eloquent `PurchaseOrder`, `PurchaseOrderItem`, `DeliveryOrder`, `DeliveryOrderItem` beserta relasi-relasinya.
- [ ] **Verification**:
  - [ ] Verifikasi kebenaran migrasi database dan relasi Eloquent melalui Tinker atau test case.
