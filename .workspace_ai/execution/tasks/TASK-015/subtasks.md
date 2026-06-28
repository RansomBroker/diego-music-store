# Subtasks: TASK-015 - Filament Purchase Order & Delivery Order Resource with Per-Branch HPP Weighted Average

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang alur navigasi PO & DO.
  - [x] Implementasikan alur tanpa approval PO -> langsung dapat diproses ke DO.
  - [x] Rancang implementasi kode PHP untuk formula Weighted Average HPP yang diisolasi per cabang penerima.
- [x] **Implementation**:
  - [x] Buat Filament Resource `PurchaseOrderResource` beserta detail form repeaters.
  - [x] Buat Filament Resource `DeliveryOrderResource` beserta verifikasi item penerimaan.
  - [x] Tulis service / event handler untuk transisi status DO ke *Received*.
  - [x] Implementasikan penambahan stok fisik cabang otomatis berdasarkan data item DO.
  - [x] Implementasikan penghitungan HPP rata-rata terbobot per cabang teratribusi ongkir, lalu update kolom `hpp` di tabel `product_branch_stocks` untuk cabang tersebut.
- [x] **Verification**:
  - [x] Uji alur pembuatan PO, pencatatan DO penerimaan, dan verifikasi stok fisik cabang beserta HPP baru di tabel `product_branch_stocks`.
