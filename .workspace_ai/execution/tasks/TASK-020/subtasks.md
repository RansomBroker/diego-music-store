# Subtasks: TASK-020 - POS Transaction Submission, Stock Deductions & Receipt Printing

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang alur logika penyimpanan transaksi (menggunakan database transaction agar aman jika terjadi kegagalan tengah jalan).
- [x] **Implementation**:
  - [x] Buat Action Class `CreatePOSSaleAction` untuk memproses logika checkout transaksi, validasi uang, pemotongan stok cabang, dan pembuatan log `stock_movements`.
  - [x] Buat layout struk thermal menggunakan Blade View kustom.
  - [x] Integrasikan trigger print JavaScript ke halaman POS setelah transaksi berhasil di-commit.
- [x] **Verification**:
  - [x] Simulasi transaksi POS, verifikasi apakah stok di `product_branch_stocks` berkurang secara tepat dan log kartu stok terbentuk.
