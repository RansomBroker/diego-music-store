# Task: TASK-004 - Eloquent Models & Relational/Bundling Logic

## Description
Buat model Eloquent (`Product`, `ProductVariant`, `PricingTier`, `ProductTierPrice`, `ProductBranchPrice`, `ProductBundle`) beserta relasi antarmodel. Buat logika bisnis khusus untuk menghitung stok bundling secara dinamis (berdasarkan stok terkecil komponen penyusunnya) dan flag stok tak terbatas untuk tipe jasa.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-002 - CRUD Master Barang & Varian (Bundling & Jasa)
- **Status**: Ready

## Acceptance Criteria
- [ ] Model Eloquent terdefinisi dengan relasi lengkap (hasMany, belongsTo, belongsToMany).
- [ ] Logika penentuan stok tipe jasa bernilai tak terbatas/unlimited (misal: return value `999999` atau abaikan pengurangan stok).
- [ ] Logika penghitungan stok produk bundling dihitung otomatis dari stok terkecil produk fisik penyusunnya.

## Assignee
- Developer
