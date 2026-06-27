# Subtasks: TASK-015 - Filament Purchase Order & Delivery Order Resource with Per-Branch HPP Weighted Average

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang alur navigasi PO & DO.
  - [ ] Implementasikan alur tanpa approval PO -> langsung dapat diproses ke DO.
  - [ ] Rancang implementasi kode PHP untuk formula Weighted Average HPP yang diisolasi per cabang penerima.
- [ ] **Implementation**:
  - [ ] Buat Filament Resource `PurchaseOrderResource` beserta detail form repeaters.
  - [ ] Buat Filament Resource `DeliveryOrderResource` beserta verifikasi item penerimaan.
  - [ ] Tulis service / event handler untuk transisi status DO ke *Received*.
  - [ ] Implementasikan penambahan stok fisik cabang otomatis berdasarkan data item DO.
  - [ ] Implementasikan penghitungan HPP rata-rata terbobot per cabang teratribusi ongkir, lalu update kolom `hpp` di tabel `product_branch_stocks` untuk cabang tersebut.
- [ ] **Verification**:
  - [ ] Uji alur pembuatan PO, pencatatan DO penerimaan, dan verifikasi stok fisik cabang beserta HPP baru di tabel `product_branch_stocks`.
