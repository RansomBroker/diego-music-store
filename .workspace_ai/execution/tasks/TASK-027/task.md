# TASK-027: Implement Front Office Owner Dashboard Widgets

- **Feature**: [FEATURE-010](file:///home/skylantern/Projects/diego-music-store-project/.workspace_ai/workspace/features/FEATURE-010-owner-frontoffice-dashboard.md)
- **Status**: Development
- **Role**: Developer
- **Description**: Menambahkan 11 widget owner/executive pada Front Office Dashboard meliputi Ringkasan Keuangan, Grafik Penjualan vs Pembelian, Turn Over Stok, Pareto 80/20, Tren & Prediksi Penjualan Bulanan, Penjualan per-Kategori/Cabang, Visitor Traffic (Low/Peak & Jam Sibuk), Performa Sales, serta Filter Interaktif.

## Acceptance Criteria
- [x] Action Class `App\Actions\OwnerDashboard\GetOwnerDashboardData` mengisolasi seluruh logika agregasi data.
- [x] Helper Class `App\Helpers\OwnerDashboardHelper` menyediakan fungsi kalkulasi statistik, pareto, dan tren linier.
- [x] Sub-komponen Livewire `OwnerDashboardWidgets` dan Blade View `owner-dashboard-widgets.blade.php` dirender di Front Office Dashboard.
- [x] Menyediakan 11 widget sesuai kebutuhan user dan otorisasi khusus role Owner/Admin.
- [x] Disertai Unit Test dan Feature Test otomatis yang sukses 100%.
