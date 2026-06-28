# Subtasks: TASK-019 - Filament Custom Page for POS User Interface (Cart, Search, Scanner, Hold/Recall)

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Rancang tata letak halaman POS (grid layout untuk keranjang belanja dan panel pencarian produk).
- [ ] **Implementation**:
  - [ ] Buat custom page Filament menggunakan `./docker-artisan.sh make:filament-page POS`.
  - [ ] Bangun komponen Livewire / AlpineJS untuk mengelola state keranjang belanja secara real-time.
  - [ ] Implementasikan input deteksi barcode scanner.
  - [ ] Buat mekanisme penyimpanan sementara untuk fitur Hold/Recall keranjang belanja.
- [ ] **Verification**:
  - [ ] Uji responsivitas UI dan pastikan event scan barcode memicu penambahan item ke keranjang dengan benar.
