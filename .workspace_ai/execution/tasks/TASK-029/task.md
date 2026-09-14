# TASK-029: Implementasi Smart Assist Kelayakan Keuangan PO & Halaman Pengaturan

## Status
- **State**: Development
- **Role**: Developer

## Description
Fitur Smart Assist Kelayakan Keuangan PO (Purchase Order) pada Backoffice ERP. Fitur ini menghitung kecukupan likuiditas kas toko dan komitmen beban berjalan untuk memberikan rekomendasi status PO (AMAN, WASPADA, TIDAK AMAN) secara real-time. Dilengkapi dengan Halaman Pengaturan (Settings) di bawah Navigation Group 'Pengaturan'.

## Acceptance Criteria
1. Terdapat halaman pengaturan `Pengaturan -> Smart Assist PO` di Filament Backoffice.
2. Pengaturan mencakup: Status aktif/non-aktif, % Min Buffer Kas, Nominal Min Kas, Include PO Pending, Include Proyeksi Inflow 30 Hari.
3. Form Purchase Order menampilkan kartu analisis Smart Assist secara live saat nominal PO atau cabang berubah.
4. Action class `EvaluatePoFinancialHealth` dan `UpdatePoSmartAssistSettings` teruji via Automated Tests.
5. Memenuhi standar `code-style-guide.md` (Blade View untuk custom UI, Action pattern, Helper class, test coverage).
