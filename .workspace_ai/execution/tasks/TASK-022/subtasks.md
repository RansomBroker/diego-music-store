# Subtasks: TASK-022 - POS Reports and Daily Z-Report PDF/Thermal Exports

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Tentukan parameter filter laporan POS dan desain layout dokumen PDF laporan & Z-Report.
- [ ] **Implementation**:
  - [ ] Implementasikan halaman dashboard/report tab khusus di Filament panel.
  - [ ] Buat custom Blade layout untuk format Z-Report.
  - [ ] Implementasikan action controller untuk merender view ke PDF menggunakan DomPDF dan mengirimkannya sebagai stream unduhan browser.
- [ ] **Verification**:
  - [ ] Unduh laporan dan Z-Report, lalu periksa apakah datanya sesuai dengan transaksi riil di database.
