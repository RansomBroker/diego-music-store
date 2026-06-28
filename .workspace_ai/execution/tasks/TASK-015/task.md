# Task: TASK-015 - Filament Purchase Order & Delivery Order Resource with Per-Branch HPP Weighted Average

## Description
Buat Filament Resource untuk mengelola pembelian barang (`PurchaseOrderResource` dan `DeliveryOrderResource`). Implementasikan logika otomatisasi penerimaan barang (DO) untuk mengupdate stok fisik cabang dan menghitung HPP Weighted Average secara terisolasi per masing-masing cabang.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-002 - Back Office Procurement & Inventory
- **Feature**: FEATURE-004 - Satuan Produk & Pengelolaan PO/DO
- **Status**: Development

## Acceptance Criteria
- [ ] CRUD `PurchaseOrderResource` dapat digunakan untuk mengelola data PO (Draft, Approved), serta input item PO dan harganya.
- [ ] CRUD `DeliveryOrderResource` dapat menginput Qty fisik yang diterima per item vs Qty dipesan dari PO yang aktif, serta input `shipping_cost` (ongkos kirim).
- [ ] Saat status DO diselesaikan (*Received*), sistem secara otomatis menambahkan stok fisik di `product_branch_stocks` untuk cabang penerima.
- [ ] Logika Akuntansi Per-Cabang: Saat DO berstatus *Received*, hitung otomatis HPP baru untuk produk-produk yang diterima menggunakan formula Weighted Average HPP per cabang tersebut:
      $$\text{HPP Baru Cabang} = \frac{(\text{Stok Lama Cabang} \times \text{HPP Lama Cabang}) + (\text{Qty Baru} \times (\text{Harga Beli} + \text{Ongkos Kirim Satuan}))}{\text{Stok Lama Cabang} + \text{Qty Baru}}$$
      Di mana *Ongkos Kirim Satuan* adalah pembagian merata dari total `shipping_cost` dokumen DO ke item-item yang diterima. Update HPP baru ini ke kolom `hpp` pada tabel `product_branch_stocks` untuk cabang tersebut.
- [ ] Tidak ada alur approval/persetujuan khusus (seperti otorisasi owner) untuk memproses PO menjadi DO.

## Assignee
- Developer
