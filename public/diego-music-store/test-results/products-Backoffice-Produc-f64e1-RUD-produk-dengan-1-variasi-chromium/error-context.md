# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: products.spec.js >> Backoffice Product CRUD E2E Test >> harus dapat melakukan alur CRUD produk dengan 1 variasi
- Location: tests/e2e/products.spec.js:169:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByRole('row', { name: /Gitar Varian E2E 1786366028084/ }).first()
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 10000ms
  - waiting for getByRole('row', { name: /Gitar Varian E2E 1786366028084/ }).first()

```

```yaml
- navigation:
  - button "Collapse sidebar"
  - link "Diego Music Store (Cabang Pusat (Back Office))":
    - /url: http://localhost/backoffice
  - text: Global search
  - searchbox "Global search"
  - button "Cabang Pusat (Back Office)":
    - text: Cabang Pusat (Back Office)
    - img
  - button "User menu":
    - img "Avatar of Owner Diego Music Store"
- complementary:
  - navigation:
    - list:
      - listitem:
        - list:
          - listitem:
            - link "Dashboard":
              - /url: http://localhost/backoffice
      - listitem:
        - text: Master Data
        - button "Master Data" [expanded]
        - list:
          - listitem:
            - link "Branches":
              - /url: http://localhost/backoffice/branches
          - listitem:
            - link "Label Pelanggan":
              - /url: http://localhost/backoffice/customer-labels
          - listitem:
            - link "Customers":
              - /url: http://localhost/backoffice/customers
          - listitem:
            - link "Pricing Tiers":
              - /url: http://localhost/backoffice/pricing-tiers
          - listitem:
            - link "Produk":
              - /url: http://localhost/backoffice/products
          - listitem:
            - link "Suppliers":
              - /url: http://localhost/backoffice/suppliers
          - listitem:
            - link "Units":
              - /url: http://localhost/backoffice/units
      - listitem:
        - text: Pembelian
        - button "Pembelian" [expanded]
        - list:
          - listitem:
            - link "Laporan Hutang":
              - /url: http://localhost/backoffice/accounts-payable-report
          - listitem:
            - link "Laporan Pembelian":
              - /url: http://localhost/backoffice/purchase-report
          - listitem:
            - link "Laporan Retur Pembelian":
              - /url: http://localhost/backoffice/purchase-return-report
          - listitem:
            - link "Laporan Pelunasan Hutang":
              - /url: http://localhost/backoffice/supplier-payment-report
          - listitem:
            - link "Purchase Orders":
              - /url: http://localhost/backoffice/purchase-orders
          - listitem:
            - link "Retur Pembelian (Supplier)":
              - /url: http://localhost/backoffice/purchase-returns
          - listitem:
            - link "Transaksi Pembelian":
              - /url: http://localhost/backoffice/purchase-transactions
          - listitem:
            - link "Pelunasan Hutang":
              - /url: http://localhost/backoffice/supplier-payments
      - listitem:
        - text: Penjualan
        - button "Penjualan" [expanded]
        - list:
          - listitem:
            - link "Surat Jalan (DO)":
              - /url: http://localhost/backoffice/delivery-orders
          - listitem:
            - link "Faktur Penjualan":
              - /url: http://localhost/backoffice/sales-invoices
          - listitem:
            - link "Penawaran Harga (SQ)":
              - /url: http://localhost/backoffice/sales-quotations
          - listitem:
            - link "Point of Sale":
              - /url: /pos
      - listitem:
        - text: Inventori
        - button "Inventori" [expanded]
        - list:
          - listitem:
            - link "Laporan Persediaan Akhir":
              - /url: http://localhost/backoffice/ending-inventory-report
          - listitem:
            - link "Laporan Daftar Stok":
              - /url: http://localhost/backoffice/stock-list-report
          - listitem:
            - link "Laporan Mutasi Barang":
              - /url: http://localhost/backoffice/stock-movement-report
          - listitem:
            - link "Laporan Stok Opname":
              - /url: http://localhost/backoffice/stock-opname-report
          - listitem:
            - link "Mutasi Barang":
              - /url: http://localhost/backoffice/inventory-mutations
          - listitem:
            - link "Retur Penjualan (Barang)":
              - /url: http://localhost/backoffice/sales-returns
          - listitem:
            - link "Kartu Stok":
              - /url: http://localhost/backoffice/stock-movements
          - listitem:
            - link "Stok Opname":
              - /url: http://localhost/backoffice/stock-opnames
      - listitem:
        - text: Kelola User
        - button "Kelola User" [expanded]
        - list:
          - listitem:
            - link "Setting Hak Akses User":
              - /url: http://localhost/backoffice/user-privileges
          - listitem:
            - link "Users":
              - /url: http://localhost/backoffice/users
      - listitem:
        - text: Akuntansi
        - button "Akuntansi" [expanded]
        - list:
          - listitem:
            - link "Tutup Buku Bulanan":
              - /url: http://localhost/backoffice/monthly-closing-page
          - listitem:
            - link "Klasifikasi Akun":
              - /url: http://localhost/backoffice/account-classifications
          - listitem:
            - link "Accounts":
              - /url: http://localhost/backoffice/accounts
          - listitem:
            - link "Transaksi Kas & Bank":
              - /url: http://localhost/backoffice/cash-transactions
          - listitem:
            - link "Jurnal Umum":
              - /url: http://localhost/backoffice/journal-entries
          - listitem:
            - link "Jurnal Umum Terjadwal":
              - /url: http://localhost/backoffice/scheduled-journal-entries
          - listitem:
            - link "Master Aset Tetap":
              - /url: http://localhost/backoffice/assets
          - listitem:
            - link "Disposisi & Penghapusan Aset":
              - /url: http://localhost/backoffice/asset-disposals
      - listitem:
        - text: Laporan Keuangan
        - button "Laporan Keuangan" [expanded]
        - list:
          - listitem:
            - link "Balance Sheet (Neraca)":
              - /url: http://localhost/backoffice/balance-sheet
          - listitem:
            - link "Buku Bank (Bank Ledger)":
              - /url: http://localhost/backoffice/bank-ledger
          - listitem:
            - link "Laporan Kas & Bank":
              - /url: http://localhost/backoffice/cash-book-report
          - listitem:
            - link "Buku Besar (General Ledger)":
              - /url: http://localhost/backoffice/general-ledger
          - listitem:
            - link "Laba Rugi (Income Statement)":
              - /url: http://localhost/backoffice/income-statement
          - listitem:
            - link "Laporan Jurnal":
              - /url: http://localhost/backoffice/journal-report
          - listitem:
            - link "Neraca Saldo (Trial Balance)":
              - /url: http://localhost/backoffice/trial-balance
          - listitem:
            - link "Buku Vendor (Kartu Hutang)":
              - /url: http://localhost/backoffice/vendor-ledger
      - listitem:
        - text: Pengaturan
        - button "Pengaturan" [expanded]
        - list:
          - listitem:
            - link "Backup Database":
              - /url: http://localhost/backoffice/database-backup
- main:
  - navigation:
    - list:
      - listitem:
        - link "Produk":
          - /url: http://localhost/backoffice/products
      - listitem: List
  - heading "Produk" [level=1]
  - button "Cetak Semua Barcode"
  - button "New Produk"
  - heading "Filters" [level=2]
  - button "Reset"
  - text: Tampilan Varian
  - combobox "Tampilan Varian":
    - option "Varian Child & Produk Tunggal" [selected]
    - option "Semua Row (Termasuk Parent)"
    - option "Hanya Parent Product"
    - option "Hanya Varian Child"
  - text: Pilih Produk
  - button "Semua Produk"
  - text: Tipe Produk
  - combobox "Tipe Produk":
    - option "Semua Tipe" [selected]
    - option "Barang Fisik"
    - option "Produk Bundling"
    - option "Jasa / Layanan"
  - text: Kategori Produk
  - combobox "Kategori Produk":
    - option "Semua Kategori" [selected]
    - option "Jasa Service"
  - text: Merk / Brand
  - combobox "Merk / Brand":
    - option "Semua Merk" [selected]
  - text: Supplier
  - button "Semua Supplier"
  - text: Search
  - searchbox "Search": Gitar Varian E2E 1786366028084
  - button "Column manager"
  - table:
    - rowgroup:
      - row "Select/deselect all items for bulk actions. Foto Nama Produk Tipe Kategori Stok Barcode Satuan SKU Harga Jual Dasar Aktif Actions":
        - columnheader "Select/deselect all items for bulk actions.":
          - checkbox "Select/deselect all items for bulk actions."
        - columnheader "Foto"
        - columnheader "Nama Produk":
          - button "Nama Produk"
        - columnheader "Tipe"
        - columnheader "Kategori":
          - button "Kategori"
        - columnheader "Stok"
        - columnheader "Barcode"
        - columnheader "Satuan"
        - columnheader "SKU"
        - columnheader "Harga Jual Dasar"
        - columnheader "Aktif"
        - columnheader "Actions"
    - rowgroup:
      - row "Select/deselect item 1 for bulk actions. Gitar Akustik Yamaha FS800 (Natural) Barang Fisik - 25 8991234567891 Pieces SKU-YMHFSNAT Rp 3.200.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 1 for bulk actions.":
          - checkbox "Select/deselect item 1 for bulk actions."
        - cell:
          - button
        - cell "Gitar Akustik Yamaha FS800 (Natural)":
          - button "Gitar Akustik Yamaha FS800 (Natural)"
        - cell "Barang Fisik":
          - button "Barang Fisik"
        - cell "-":
          - button "-":
            - paragraph: "-"
        - cell "25":
          - button "25"
        - cell "8991234567891":
          - button "8991234567891":
            - img
            - text: "8991234567891"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-YMHFSNAT":
          - button "SKU-YMHFSNAT"
        - cell "Rp 3.200.000":
          - button "Rp 3.200.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 2 for bulk actions. Gitar Akustik Yamaha FS800 (Sunburst) Barang Fisik - 26 8991234567892 Pieces SKU-YMHFSBST Rp 3.300.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 2 for bulk actions.":
          - checkbox "Select/deselect item 2 for bulk actions."
        - cell:
          - button
        - cell "Gitar Akustik Yamaha FS800 (Sunburst)":
          - button "Gitar Akustik Yamaha FS800 (Sunburst)"
        - cell "Barang Fisik":
          - button "Barang Fisik"
        - cell "-":
          - button "-":
            - paragraph: "-"
        - cell "26":
          - button "26"
        - cell "8991234567892":
          - button "8991234567892":
            - img
            - text: "8991234567892"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-YMHFSBST":
          - button "SKU-YMHFSBST"
        - cell "Rp 3.300.000":
          - button "Rp 3.300.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 3 for bulk actions. Setup & Stem Gitar Jasa / Layanan - 999999 8992345678901 Pieces SKU-JSASTEMP Rp 150.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 3 for bulk actions.":
          - checkbox "Select/deselect item 3 for bulk actions."
        - cell:
          - button
        - cell "Setup & Stem Gitar":
          - button "Setup & Stem Gitar"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "-":
          - button "-":
            - paragraph: "-"
        - cell "999999":
          - button "999999"
        - cell "8992345678901":
          - button "8992345678901":
            - img
            - text: "8992345678901"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-JSASTEMP":
          - button "SKU-JSASTEMP"
        - cell "Rp 150.000":
          - button "Rp 150.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 4 for bulk actions. Paket Siap Konser Yamaha FS800 Produk Bundling - 25 8993456789012 Set SKU-BNDYMHFS Rp 3.250.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 4 for bulk actions.":
          - checkbox "Select/deselect item 4 for bulk actions."
        - cell:
          - button
        - cell "Paket Siap Konser Yamaha FS800":
          - button "Paket Siap Konser Yamaha FS800"
        - cell "Produk Bundling":
          - button "Produk Bundling"
        - cell "-":
          - button "-":
            - paragraph: "-"
        - cell "25":
          - button "25"
        - cell "8993456789012":
          - button "8993456789012":
            - img
            - text: "8993456789012"
        - cell "Set":
          - button "Set"
        - cell "SKU-BNDYMHFS":
          - button "SKU-BNDYMHFS"
        - cell "Rp 3.250.000":
          - button "Rp 3.250.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 5 for bulk actions. Service & Pasang Preamp / Pickup Gitar Jasa / Layanan Jasa Service 999999 8992345678902 Pieces SKU-SVC-PICKUP Rp 250.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 5 for bulk actions.":
          - checkbox "Select/deselect item 5 for bulk actions."
        - cell:
          - button
        - cell "Service & Pasang Preamp / Pickup Gitar":
          - button "Service & Pasang Preamp / Pickup Gitar"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "Jasa Service":
          - button "Jasa Service"
        - cell "999999":
          - button "999999"
        - cell "8992345678902":
          - button "8992345678902":
            - img
            - text: "8992345678902"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-SVC-PICKUP":
          - button "SKU-SVC-PICKUP"
        - cell "Rp 250.000":
          - button "Rp 250.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 6 for bulk actions. Reparasi & Rewiring Elektronik Gitar / Bass Jasa / Layanan Jasa Service 999999 8992345678903 Pieces SKU-SVC-WIRING Rp 200.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 6 for bulk actions.":
          - checkbox "Select/deselect item 6 for bulk actions."
        - cell:
          - button
        - cell "Reparasi & Rewiring Elektronik Gitar / Bass":
          - button "Reparasi & Rewiring Elektronik Gitar / Bass"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "Jasa Service":
          - button "Jasa Service"
        - cell "999999":
          - button "999999"
        - cell "8992345678903":
          - button "8992345678903":
            - img
            - text: "8992345678903"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-SVC-WIRING":
          - button "SKU-SVC-WIRING"
        - cell "Rp 200.000":
          - button "Rp 200.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 7 for bulk actions. Service & Kalibrasi Keyboard / Piano Digital Jasa / Layanan Jasa Service 999999 8992345678904 Pieces SKU-SVC-KEYB Rp 350.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 7 for bulk actions.":
          - checkbox "Select/deselect item 7 for bulk actions."
        - cell:
          - button
        - cell "Service & Kalibrasi Keyboard / Piano Digital":
          - button "Service & Kalibrasi Keyboard / Piano Digital"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "Jasa Service":
          - button "Jasa Service"
        - cell "999999":
          - button "999999"
        - cell "8992345678904":
          - button "8992345678904":
            - img
            - text: "8992345678904"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-SVC-KEYB":
          - button "SKU-SVC-KEYB"
        - cell "Rp 350.000":
          - button "Rp 350.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 8 for bulk actions. Service Amplifier & Sound System Jasa / Layanan Jasa Service 999999 8992345678905 Pieces SKU-SVC-AMP Rp 450.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 8 for bulk actions.":
          - checkbox "Select/deselect item 8 for bulk actions."
        - cell:
          - button
        - cell "Service Amplifier & Sound System":
          - button "Service Amplifier & Sound System"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "Jasa Service":
          - button "Jasa Service"
        - cell "999999":
          - button "999999"
        - cell "8992345678905":
          - button "8992345678905":
            - img
            - text: "8992345678905"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-SVC-AMP":
          - button "SKU-SVC-AMP"
        - cell "Rp 450.000":
          - button "Rp 450.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 9 for bulk actions. Refret & Fret Leveling Gitar / Bass Pro Jasa / Layanan Jasa Service 999999 8992345678906 Pieces SKU-SVC-REFRET Rp 500.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 9 for bulk actions.":
          - checkbox "Select/deselect item 9 for bulk actions."
        - cell:
          - button
        - cell "Refret & Fret Leveling Gitar / Bass Pro":
          - button "Refret & Fret Leveling Gitar / Bass Pro"
        - cell "Jasa / Layanan":
          - button "Jasa / Layanan"
        - cell "Jasa Service":
          - button "Jasa Service"
        - cell "999999":
          - button "999999"
        - cell "8992345678906":
          - button "8992345678906":
            - img
            - text: "8992345678906"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-SVC-REFRET":
          - button "SKU-SVC-REFRET"
        - cell "Rp 500.000":
          - button "Rp 500.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
      - row "Select/deselect item 10 for bulk actions. Gitar E2E 1786364678029 Barang Fisik - 0 8994227476347 Pieces SKU-W3844GZK Rp 1.500.000 Edit Copy Produk Kartu Stok Delete":
        - cell "Select/deselect item 10 for bulk actions.":
          - checkbox "Select/deselect item 10 for bulk actions."
        - cell:
          - button
        - cell "Gitar E2E 1786364678029":
          - button "Gitar E2E 1786364678029"
        - cell "Barang Fisik":
          - button "Barang Fisik"
        - cell "-":
          - button "-":
            - paragraph: "-"
        - cell "0":
          - button "0"
        - cell "8994227476347":
          - button "8994227476347":
            - img
            - text: "8994227476347"
        - cell "Pieces":
          - button "Pieces"
        - cell "SKU-W3844GZK":
          - button "SKU-W3844GZK"
        - cell "Rp 1.500.000":
          - button "Rp 1.500.000"
        - cell:
          - switch [checked]
        - cell "Edit Copy Produk Kartu Stok Delete":
          - button "Edit"
          - button "Copy Produk"
          - button "Kartu Stok"
          - button "Delete"
  - navigation "Pagination navigation":
    - text: Per page
    - combobox "Per page":
      - option "5"
      - option "10" [selected]
      - option "25"
      - option "50"
    - button "Next"
  - dialog:
    - button "Close"
    - heading "Create Produk" [level=2]
    - tablist "Product Details":
      - tab "Informasi Umum"
      - tab "Varian / Spesifikasi"
      - tab "Akuntansi"
    - 'tabpanel "Produk ini memiliki beberapa varian (warna, ukuran, dll.) Nama Varian SKU Barcode Harga Jual Harga Beli Est. Ongkir HPP Diskon PPN Harga Tier: Umum / Retail Harga Tier: Reseller / Grosir Variants Delete Name Merah Sku SKU-NOOKI1EO Barcode 8995967346785 Price Rp Cost price Rp Estimated shipping Rp Hpp Rp Discount value Rp Tax value % 1 Rp 2 Rp Delete Name The name field is required. Sku SKU-RLBKQ5CU Barcode 8991434241492 Price Rp The price field is required. Cost price Rp The cost price field is required. Estimated shipping Rp Hpp Rp Discount value Rp Tax value % 1 Rp 2 Rp Add to variants"':
      - switch "Produk ini memiliki beberapa varian (warna, ukuran, dll.)" [checked]
      - text: Produk ini memiliki beberapa varian (warna, ukuran, dll.)
      - definition: "Nama Varian SKU Barcode Harga Jual Harga Beli Est. Ongkir HPP Diskon PPN Harga Tier: Umum / Retail Harga Tier: Reseller / Grosir"
      - text: Variants
      - list:
        - listitem:
          - list:
            - listitem:
              - button "Delete"
          - text: Name
          - textbox "Name":
            - /placeholder: Nama Varian
            - text: Merah
          - text: Sku
          - textbox "Sku":
            - /placeholder: SKU
            - text: SKU-NOOKI1EO
          - text: Barcode
          - textbox "Barcode": "8995967346785"
          - text: Price Rp
          - spinbutton "Price": "1750000"
          - text: Cost price Rp
          - spinbutton "Cost price": "1200000"
          - text: Estimated shipping Rp
          - spinbutton "Estimated shipping": "0"
          - text: Hpp Rp
          - spinbutton "Hpp": "1200000"
          - text: Discount value
          - button "Rp"
          - spinbutton "Discount value": "0"
          - text: Tax value
          - button "%"
          - spinbutton "Tax value": "0"
          - text: 1 Rp
          - spinbutton "1"
          - text: 2 Rp
          - spinbutton "2"
        - listitem:
          - list:
            - listitem:
              - button "Delete"
          - text: Name
          - textbox "Name":
            - /placeholder: Nama Varian
          - paragraph: The name field is required.
          - text: Sku
          - textbox "Sku":
            - /placeholder: SKU
            - text: SKU-RLBKQ5CU
          - text: Barcode
          - textbox "Barcode": "8991434241492"
          - text: Price Rp
          - spinbutton "Price"
          - paragraph: The price field is required.
          - text: Cost price Rp
          - spinbutton "Cost price"
          - paragraph: The cost price field is required.
          - text: Estimated shipping Rp
          - spinbutton "Estimated shipping": "0"
          - text: Hpp Rp
          - spinbutton "Hpp"
          - text: Discount value
          - button "Rp"
          - spinbutton "Discount value": "0"
          - text: Tax value
          - button "%"
          - spinbutton "Tax value": "0"
          - text: 1 Rp
          - spinbutton "1"
          - text: 2 Rp
          - spinbutton "2"
      - button "Add to variants"
    - button "Create"
    - button "Create & create another"
    - button "Cancel"
- status
```

# Test source

```ts
  158 | 
  159 |     await page.waitForTimeout(2000);
  160 |     await page.waitForLoadState('networkidle');
  161 | 
  162 |     // Verifikasi produk sudah terhapus dari tabel
  163 |     await expect(page.getByRole('table')).not.toContainText(updatedProductName);
  164 | 
  165 |     // Jeda 1 detik di akhir test untuk snapshot visual di Playwright UI
  166 |     await page.waitForTimeout(1000);
  167 |   });
  168 | 
  169 |   test('harus dapat melakukan alur CRUD produk dengan 1 variasi', async ({ page }) => {
  170 |     const productName = `Gitar Varian E2E ${Date.now()}`;
  171 |     const updatedProductName = `${productName} (Updated)`;
  172 | 
  173 |     // 1. Navigasi ke Halaman Produk
  174 |     await page.goto('/backoffice/products', { waitUntil: 'networkidle' });
  175 |     await expect(page).toHaveURL(/\/backoffice\/products/);
  176 | 
  177 |     // ==========================================
  178 |     // 2. CREATE (Tambah Produk Baru Dengan 1 Variasi)
  179 |     // ==========================================
  180 |     const createButton = page.getByRole('button', { name: /new produk|tambah produk|buat/i }).first();
  181 |     await expect(createButton).toBeVisible();
  182 |     await createButton.click();
  183 | 
  184 |     const createHeading = page.getByRole('heading', { name: /create produk/i });
  185 |     await expect(createHeading).toBeVisible({ timeout: 10000 });
  186 | 
  187 |     const modalDialog = page.getByRole('dialog');
  188 | 
  189 |     // 2.1 Tab 1: Informasi Umum
  190 |     const nameInput = modalDialog.getByRole('textbox', { name: /nama produk/i }).or(modalDialog.locator('input[name="data.name"]')).first();
  191 |     await expect(nameInput).toBeVisible();
  192 |     await nameInput.fill(productName);
  193 | 
  194 |     const typeSelect = modalDialog.getByRole('combobox', { name: /tipe produk/i }).first();
  195 |     await expect(typeSelect).toBeVisible();
  196 |     await typeSelect.selectOption({ label: 'Barang Fisik' });
  197 |     await page.waitForTimeout(500);
  198 | 
  199 |     const unitButton = modalDialog.getByRole('button', { name: /select an option|satuan/i }).first();
  200 |     await expect(unitButton).toBeVisible();
  201 |     await unitButton.click();
  202 |     await page.waitForTimeout(400);
  203 | 
  204 |     const unitOption = modalDialog.getByRole('listbox').getByRole('option').first();
  205 |     await expect(unitOption).toBeVisible({ timeout: 5000 });
  206 |     await unitOption.click();
  207 |     await page.waitForTimeout(300);
  208 | 
  209 |     // 2.2 Tab 2: Varian / Spesifikasi
  210 |     const variantTab = modalDialog.getByRole('tab', { name: /varian|spesifikasi/i });
  211 |     await expect(variantTab).toBeVisible({ timeout: 5000 });
  212 |     await variantTab.click();
  213 |     await page.waitForTimeout(400);
  214 | 
  215 |     // Toggle status "Produk ini memiliki beberapa varian"
  216 |     const hasVariantsToggle = modalDialog.getByRole('switch', { name: /memiliki beberapa varian/i }).or(modalDialog.locator('input[wire\\:model*="has_variants"]').or(modalDialog.getByRole('switch'))).first();
  217 |     await expect(hasVariantsToggle).toBeVisible();
  218 |     await hasVariantsToggle.click();
  219 |     await page.waitForTimeout(600);
  220 | 
  221 |     // Jika ada tombol "Add to variants" / "Tambah Varian", klik untuk membuat 1 item varian
  222 |     const addVariantBtn = modalDialog.getByRole('button', { name: /add|tambah/i }).first();
  223 |     if (await addVariantBtn.isVisible({ timeout: 2000 })) {
  224 |       await addVariantBtn.click();
  225 |       await page.waitForTimeout(400);
  226 |     }
  227 | 
  228 |     // Isi Varian 1 (Nama Varian, Harga Jual, Harga Beli)
  229 |     const variantNameInput = modalDialog.getByPlaceholder('Nama Varian').or(modalDialog.locator('input[name*="variants"][name*="name"]')).first();
  230 |     await expect(variantNameInput).toBeVisible({ timeout: 5000 });
  231 |     await variantNameInput.fill('Merah');
  232 | 
  233 |     const variantPriceInput = modalDialog.getByPlaceholder('Harga Jual').or(modalDialog.locator('input[name*="variants"][name*="price"]')).first();
  234 |     await expect(variantPriceInput).toBeVisible();
  235 |     await variantPriceInput.fill('1750000');
  236 | 
  237 |     const variantCostPriceInput = modalDialog.getByPlaceholder('Harga Beli').or(modalDialog.locator('input[name*="variants"][name*="cost_price"]')).first();
  238 |     await expect(variantCostPriceInput).toBeVisible();
  239 |     await variantCostPriceInput.fill('1200000');
  240 | 
  241 |     // Submit Form Create
  242 |     const modalSubmitBtn = modalDialog.getByRole('button', { name: 'Create', exact: true });
  243 |     await expect(modalSubmitBtn).toBeVisible();
  244 |     await modalSubmitBtn.click();
  245 | 
  246 |     await page.waitForTimeout(2000);
  247 |     await page.waitForLoadState('networkidle');
  248 | 
  249 |     // ==========================================
  250 |     // 3. READ & SEARCH (Cari Produk Varian di Tabel)
  251 |     // ==========================================
  252 |     const tableSearchInput = page.locator('input[wire\\:model*="tableSearch"]').or(page.getByRole('main').getByRole('searchbox', { name: 'Search', exact: true })).first();
  253 |     await expect(tableSearchInput).toBeVisible();
  254 |     await tableSearchInput.fill(productName);
  255 |     await page.waitForTimeout(1200);
  256 | 
  257 |     const tableRow = page.getByRole('row', { name: new RegExp(productName) }).first();
> 258 |     await expect(tableRow).toBeVisible({ timeout: 10000 });
      |                            ^ Error: expect(locator).toBeVisible() failed
  259 | 
  260 |     // ==========================================
  261 |     // 4. UPDATE (Edit Produk Varian)
  262 |     // ==========================================
  263 |     const editButton = tableRow.getByRole('button', { name: 'Edit', exact: true }).first();
  264 |     await expect(editButton).toBeVisible();
  265 |     await editButton.click();
  266 | 
  267 |     const editHeading = page.getByRole('dialog').getByRole('heading', { name: /edit/i });
  268 |     await expect(editHeading).toBeVisible({ timeout: 10000 });
  269 | 
  270 |     const editDialog = page.getByRole('dialog');
  271 | 
  272 |     const infoTab = editDialog.getByRole('tab', { name: /informasi umum/i }).first();
  273 |     if (await infoTab.isVisible()) {
  274 |       await infoTab.click();
  275 |       await page.waitForTimeout(300);
  276 |     }
  277 | 
  278 |     const editNameInput = editDialog.getByRole('textbox', { name: /nama produk/i }).or(editDialog.locator('input[name="data.name"]')).first();
  279 |     await editNameInput.fill(updatedProductName);
  280 | 
  281 |     const updateSubmitBtn = editDialog.getByRole('button', { name: /save|simpan|save changes/i }).first();
  282 |     await updateSubmitBtn.click();
  283 | 
  284 |     await page.waitForTimeout(2000);
  285 |     await page.waitForLoadState('networkidle');
  286 | 
  287 |     if (await tableSearchInput.isVisible()) {
  288 |       await tableSearchInput.fill(updatedProductName);
  289 |       await page.waitForTimeout(1200);
  290 |     }
  291 | 
  292 |     const updatedRow = page.getByRole('row', { name: new RegExp(updatedProductName) }).first();
  293 |     await expect(updatedRow).toBeVisible({ timeout: 10000 });
  294 | 
  295 |     // ==========================================
  296 |     // 5. DELETE (Hapus Produk Varian)
  297 |     // ==========================================
  298 |     const deleteButton = updatedRow.getByRole('button', { name: 'Delete', exact: true }).first();
  299 |     await expect(deleteButton).toBeVisible();
  300 |     await deleteButton.click();
  301 | 
  302 |     const confirmBtn = page.getByRole('dialog').getByRole('button', { name: /confirm|delete|hapus|ya/i }).last();
  303 |     await expect(confirmBtn).toBeVisible({ timeout: 5000 });
  304 |     await confirmBtn.click();
  305 | 
  306 |     await page.waitForTimeout(2000);
  307 |     await page.waitForLoadState('networkidle');
  308 | 
  309 |     await expect(page.getByRole('table')).not.toContainText(updatedProductName);
  310 | 
  311 |     // Jeda 1 detik di akhir test untuk snapshot visual di Playwright UI
  312 |     await page.waitForTimeout(1000);
  313 |   });
  314 | 
  315 | });
  316 | 
```