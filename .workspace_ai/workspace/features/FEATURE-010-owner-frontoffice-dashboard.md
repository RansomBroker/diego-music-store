# FEATURE-010: Front Office Owner Dashboard Widgets

## 1. Overview
Fitur ini menambahkan widget executive dashboard pada Front Office Dashboard (`/pos/dashboard`) yang dikhususkan bagi pengguna dengan role **Owner**, **Admin**, dan **Super Admin**. Widget mencakup ringkasan keuangan, grafik perbandingan penjualan vs pembelian, perputaran stok, analisis Pareto 80/20, tren & prediksi bulan berikutnya, kontribusi omzet & profit per kategori/cabang, pola pengunjung harian & jam sibuk, serta performa sales dan filter interaktif.

## 2. Requirements & Business Rules
1. **Hak Akses**: Hanya dapat dilihat oleh user dengan role `owner`, `admin`, `super_admin`.
2. **Ringkasan Keuangan**:
   - Total Penjualan (Penjualan bersih dari transaksi `completed`).
   - Total Pengeluaran (Transaksi pengeluaran kas / beban).
   - Total Hutang (Sisa tagihan transaksi pembelian kredit yang belum terbayar).
   - Saldo Kas & Bank (Akumulasi saldo akun kas & bank).
3. **Grafik Penjualan vs Pembelian**: Grafik perbandingan nominal penjualan vs pembelian secara berkala.
4. **Turn Over Stok**: Kecepatan perputaran stok per kategori (HPP / Nilai Rata-rata Stok).
5. **Analisis Pareto 80/20**: Produk & pelanggan teratas yang memberikan kontribusi omzet/profit hingga ~80%.
6. **Tren Penjualan Bulanan & Prediksi**: Proyeksi tren omzet bulan berikutnya menggunakan Linear Regression Forecasting.
7. **Breakdown Kategori & Cabang**: Kontribusi omzet dan profit per kategori produk & per cabang toko.
8. **Visitor Traffic**:
   - Monthly Report: Hari dengan transaksi tertinggi (peak) dan terendah (low).
   - Hourly Traffic Jam: Distribusi jam transaksi untuk mengidentifikasi jam sibuk (busy hours).
9. **Performa Sales**: Pencapaian omset dibanding target bulanan & persentase kehadiran staf sales.
10. **Interactive Filter**: Filter global pada dashboard berdasarkan Cabang, Kategori Produk, dan Rentang Waktu.

## 3. Architecture Constraints
- Seluruh logika bisnis wajib berada pada Action Class `App\Actions\OwnerDashboard\GetOwnerDashboardData`.
- Formula statistik dan pembantu wajib berada pada Helper Class `App\Helpers\OwnerDashboardHelper`.
- Tampilan harus dipisah dalam komponen Livewire `OwnerDashboardWidgets` dan Blade View `owner-dashboard-widgets.blade.php`.
