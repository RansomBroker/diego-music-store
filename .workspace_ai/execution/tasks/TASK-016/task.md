# Task: TASK-016 - Database Migration & Eloquent Models for Mutations, Opname, & Stock Cards

## Description
Buat skema database dan model Eloquent untuk modul Mutasi Barang Antar-Cabang, Stok Opname Cabang, serta log histori pergerakan stok (Stock Cards / Kartu Stok).

## Technical Details
- **Role**: Developer / Architect
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-005 - Mutasi Barang & Stok Opname
- **Status**: Ready

## Acceptance Criteria
- [ ] Migration untuk tabel `inventory_mutations` (`id`, `sender_branch_id`, `receiver_branch_id`, `mutation_number`, `mutation_date`, `status` [enum: draft, transit, received], `notes`, `timestamps`) dan `inventory_mutation_items` (`id`, `inventory_mutation_id`, `product_variant_id`, `quantity`, `timestamps`) berhasil dijalankan.
- [ ] Migration untuk tabel `stock_opnames` (`id`, `branch_id`, `opname_number`, `opname_date`, `status` [enum: draft, completed], `notes`, `timestamps`) dan `stock_opname_items` (`id`, `stock_opname_id`, `product_variant_id`, `system_qty`, `physical_qty`, `difference`, `cost_price`, `timestamps`) berhasil dijalankan.
- [ ] Migration untuk tabel `stock_movements` (`id`, `product_variant_id`, `branch_id`, `type` [enum: in, out], `quantity`, `reference_type` [enum: DO, Mutation, Opname, POS], `reference_id`, `created_at`, `timestamps`) berhasil dijalankan.
- [ ] Model Eloquent (`InventoryMutation`, `InventoryMutationItem`, `StockOpname`, `StockOpnameItem`, `StockMovement`) beserta relasi-relasinya terdefinisi dengan lengkap.

## Assignee
- Developer
