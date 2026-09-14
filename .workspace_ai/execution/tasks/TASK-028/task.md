# TASK-028: Implement Front Office Sales & Employee Dashboard Widgets

- **Feature**: [FEATURE-011](file:///home/skylantern/Projects/diego-music-store-project/.workspace_ai/workspace/features/FEATURE-011-sales-employee-frontoffice-dashboard.md)
- **Status**: Development
- **Role**: Developer
- **Description**: Menambahkan 8 widget dashboard karyawan/sales pada Front Office Dashboard meliputi Target Bulanan, Target Harian, Komisi Penjualan, Sisa Target Unlock Tier Komisi, Leaderboard Top 3 Sales, Produk Fokus Bulan Ini, Grafik Performa Sales 1 Tahun, dan Info/Bar Absensi.

## Acceptance Criteria
- [x] Action Class `App\Actions\SalesDashboard\GetSalesEmployeeDashboardData` mengisolasi seluruh logika agregasi data sales & absensi.
- [x] Helper Class `App\Helpers\SalesEmployeeDashboardHelper` menyediakan formula kalkulasi target harian dan unlock tier.
- [x] Livewire Component `SalesEmployeeDashboardWidgets` dan Blade View `sales-employee-dashboard-widgets.blade.php` terintegrasi.
- [x] Menyediakan 8 widget sesuai spesifikasi kebutuhan pengguna.
- [x] Disertai Unit Test dan Feature Test otomatis yang lulus 100%.
