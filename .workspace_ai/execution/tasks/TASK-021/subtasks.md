# Subtasks: TASK-021 - Customer Debt Payment Tracking (Piutang) and History

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang relasi antara transaksi `sales` (piutang) dengan tabel history pelunasan `receivable_payments`.
- [ ] **Implementation**:
  - [ ] Buat migration untuk tabel `receivable_payments`.
  - [ ] Buat model Eloquent `ReceivablePayment`.
  - [ ] Buat Filament Resource `ReceivablePaymentResource` untuk mencatat cicilan pembayaran dari pelanggan.
  - [ ] Implementasikan listener atau Model Observer pada `ReceivablePayment` untuk memperbarui status lunas pada transaksi `sales` asal secara otomatis.
- [ ] **Verification**:
  - [ ] Input pembayaran parsial piutang dan verifikasi status invoice berubah menjadi `Partially Paid`. Input pelunasan penuh dan pastikan status berubah menjadi `Paid`.
