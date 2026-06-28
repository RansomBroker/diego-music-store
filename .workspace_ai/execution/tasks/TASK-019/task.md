# Task: TASK-019 - Filament Custom Page for POS User Interface (Cart, Search, Scanner, Hold/Recall)

## Description
Buat halaman kustom Filament (Custom Page) untuk antarmuka kasir (POS). Antarmuka harus interaktif dan responsif, mendukung pencarian barang cepat, scan barcode, penambahan item ke keranjang belanja, diskon item/faktur, pemilihan Sales Representative, dan fungsi Hold/Recall keranjang.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-003 - Front Desk & POS Dasar
- **Feature**: FEATURE-007 - Core POS (Point of Sale)
- **Status**: Ready

## Acceptance Criteria
- [ ] Buat custom page `POS` di Filament yang terpisah dari resource CRUD standar.
- [ ] Implementasikan form pencarian produk kustom yang mendukung scan barcode secara instan menggunakan javascript event listener.
- [ ] Terdapat visualisasi keranjang belanja (cart) dengan kemampuan menambah/mengurangi kuantitas barang, menghapus item, dan menginput diskon.
- [ ] Terdapat dropdown dinamis untuk memilih Sales Representative per item atau per transaksi.
- [ ] Tombol "Hold" untuk menahan transaksi berjalan, dan tombol "Recall" untuk memanggil kembali transaksi yang di-hold.

## Assignee
- Developer
