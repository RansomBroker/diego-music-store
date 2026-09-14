# FEATURE-011: Front Office Sales & Employee Dashboard Widgets

## 1. Overview
Fitur ini menambahkan 8 widget performa khusus karyawan & staf sales pada Front Office Dashboard (`/pos/dashboard`). Widget berfokus pada pencapaian target bulanan & harian, estimasi komisi penjualan, progress unlock tier komisi berikutnya, leaderboard Top 3 sales, produk fokus bulan ini, grafik tren omzet personal 1 tahun, serta info/bar absensi dan kuota off-day.

## 2. Requirements & Business Rules
1. **Target Penjualan Bulanan**:
   - Menampilkan target omzet bulanan dari `CommissionScheme` karyawan atau default target (Rp 25.000.000).
   - Menampilkan akumulasi omzet penjualan personal bulan ini dan persentase ketercapaian target.
2. **Target Penjualan Harian**:
   - Target harian dikalkulasi dari `Target Bulanan / Jumlah Hari Kerja Efektif Bulanan` (misal 25 hari kerja).
   - Menampilkan omzet transaksi personal hari ini dan indikator status pencapaian (On Track / Need Effort).
3. **Komisi Penjualan**:
   - Menampilkan akumulasi nominal rupiah komisi bulan ini yang diperoleh dari `SalesCommissionLog`.
4. **Sisa Target Unlock Tier Komisi**:
   - Menghitung sisa nominal Rp penjualan yang dibutuhkan karyawan untuk mencapai tier komisi berikutnya (Tier 1, Tier 2, Tier 3).
5. **Leaderboard Top 3 Sales**:
   - Ranking Top 3 staf sales dengan total omzet tertinggi bulan ini di cabang toko (🥇 Gold, 🥈 Silver, 🥉 Bronze).
6. **Produk Fokus Bulan Ini**:
   - Menampilkan produk-produk prioritas/fokus penjualan bulan ini yang memiliki skema target komisi atau insentif khusus.
7. **Grafik Performa Sales (1 Tahun)**:
   - Grafik tren omzet penjualan personal selama 12 bulan terakhir (1 tahun) menggunakan library Chart.js.
8. **Info/Bar Absensi**:
   - Indikator bar jumlah kehadiran bulan ini.
   - Kuota off-day terpakai vs kuota bulanan karyawan serta peringatan visual jika terjadi over-quota.

## 3. Architecture Constraints
- Seluruh logika agregasi bisnis berada di Action Class `App\Actions\SalesDashboard\GetSalesEmployeeDashboardData`.
- Seluruh logika kalkulasi statistik dan tier komisi berada di Helper Class `App\Helpers\SalesEmployeeDashboardHelper`.
- Tampilan harus dipisah dalam Livewire Component `SalesEmployeeDashboardWidgets` dan Blade View `sales-employee-dashboard-widgets.blade.php`.
