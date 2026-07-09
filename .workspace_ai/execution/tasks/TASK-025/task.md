# Task: TASK-025 - Implementasi Jurnal Umum Terjadwal (Recurring Journals)

## Description
Implementasikan fitur Jurnal Umum Terjadwal untuk mengotomatiskan pembuatan pencatatan jurnal umum berkala (seperti penyusutan aset tetap). Menyediakan antarmuka Filament untuk mengelola jadwal, memicu eksekusi manual, serta background job untuk pemrosesan otomatis harian.

## Technical Details
- **Role**: Developer
- **Epic**: EPIC-005 - HR, Payroll & Accounting
- **Feature**: FEATURE-009 - Jurnal Umum Terjadwal
- **Status**: Done

## Acceptance Criteria
- [x] Membuat migrasi database untuk tabel `scheduled_journal_entries` dan `scheduled_journal_items`.
- [x] Membuat model Eloquent `ScheduledJournalEntry` dan `ScheduledJournalItem` beserta relasi relasinya.
- [x] Membuat Action Class `CreateScheduledJournalEntry`, `UpdateScheduledJournalEntry`, dan `ProcessScheduledJournalEntries` untuk mengisolasi logika bisnis.
- [x] Membuat Artisan Command `app:process-scheduled-journals` dan mendaftarkannya di `routes/console.php`.
- [x] Membuat Filament Resource `ScheduledJournalEntryResource` dengan skema form input parameter jadwal dan repeater baris jurnal yang seimbang.
- [x] Menambahkan tombol tautan "Jurnal Terjadwal" pada header halaman Jurnal Umum (`ListJournalEntries`).
- [x] Membuat unit & integration test `ScheduledJournalActionsTest` untuk memverifikasi seluruh alur logika bisnis.
