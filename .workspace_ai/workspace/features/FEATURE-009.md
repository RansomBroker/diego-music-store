# Feature: FEATURE-009 - Jurnal Umum Terjadwal

## Parent Epic
- EPIC-005 - HR, Payroll & Accounting

## Description
Fitur Jurnal Umum Terjadwal memungkinkan pengguna untuk menjadwalkan pencatatan jurnal umum secara otomatis (recurring) pada interval waktu tertentu (misalnya bulanan untuk penyusutan aset tetap). Pengguna dapat menentukan tanggal mulai, durasi/frekuensi, serta daftar baris debit/kredit yang seimbang. Sebuah background process (scheduler) akan memproses jadwal yang jatuh tempo secara otomatis.

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Membantu departemen akuntansi mengotomatiskan pencatatan berkala yang bersifat rutin dan repetitif (seperti penyusutan aset tetap, amortisasi biaya dibayar dimuka, atau beban akrual bulanan) guna meningkatkan efisiensi kerja, mencegah kelupaan pencatatan, dan menjaga akurasi laporan keuangan bulanan.

## 2. User Roles & Aktor
- **Akuntan / Finance Staff**: Membuat, mengedit, menjeda, dan memantau template jurnal umum terjadwal, serta dapat memicu jalannya jurnal terjadwal secara manual.
- **System Scheduler (Cron)**: Memeriksa jadwal jurnal yang aktif dan jatuh tempo setiap hari untuk digenerasi secara otomatis menjadi Jurnal Umum (Journal Entry) terposting.

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Pembuatan Jadwal Jurnal Umum
- **Pre-condition**: Akun COA (Chart of Accounts) dan Cabang sudah terkonfigurasi.
- **Main Flow**:
  1. Pengguna membuka modul Jurnal Umum dan mengklik tombol "Jurnal Terjadwal".
  2. Pengguna mengklik "Buat Jadwal Jurnal Baru" untuk membuka form pembuatan template.
  3. Pengguna mengisi parameter penjadwalan: Tanggal Mulai, Frekuensi (Harian/Mingguan/Bulanan/Tahunan), Interval, Durasi (Bulan), Cabang, Deskripsi, serta detail baris Debit & Kredit.
  4. Sistem memvalidasi keseimbangan Debit dan Kredit. Jika tidak seimbang, form tidak dapat disimpan.
  5. Setelah disimpan, sistem mengeset `next_run_at` ke tanggal mulai. Status awal adalah `Aktif`.

### 3.2. Eksekusi Otomatis via Scheduler
- **Flow**:
  1. Cron Job menjalankan perintah `app:process-scheduled-journals` secara harian.
  2. Sistem mencari seluruh jadwal berstatus `Aktif` dengan `next_run_at` <= hari ini.
  3. Untuk setiap jadwal yang cocok, sistem membuat transaksi `JournalEntry` dengan status `posted` dan mereferensikan `ScheduledJournalEntry` terkait.
  4. Tanggal pada `JournalEntry` diatur sesuai dengan nilai `next_run_at`.
  5. Sistem memperbarui `last_run_at` dengan tanggal eksekusi tersebut, lalu menghitung `next_run_at` berikutnya.
  6. Jika `next_run_at` baru melampaui `end_date` (dihitung dari tanggal mulai + durasi bulan), status diubah menjadi `Selesai` (`completed`) dan `next_run_at` diset null.

### 3.3. Eksekusi Manual (Jalankan Sekarang)
- **Flow**:
  1. Pengguna mengklik tombol "Jalankan" pada baris jurnal terjadwal aktif di tabel.
  2. Sistem memunculkan dialog konfirmasi.
  3. Jika disetujui, sistem langsung mengeksekusi pembuatan `JournalEntry` untuk tanggal rencana berikutnya dan memajukan jadwal ke rencana run berikutnya.

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Elemen Jurnal Umum Terjadwal
| Nama Field | Tipe Data | Wajib | Validasi / Aturan |
| :--- | :--- | :--- | :--- |
| `start_date` | Date | Y | Tanggal awal rencana eksekusi pertama |
| `frequency` | Select | Y | `daily`, `weekly`, `monthly`, `yearly` |
| `interval` | Integer | Y | Minimal 1. Pengulangan frekuensi |
| `duration_months`| Integer | T | Jumlah bulan durasi aktif. Dihitung sebagai `end_date = start_date + duration_months` |
| `status` | Select | Y | `active` (Aktif), `paused` (Jeda), `completed` (Selesai) |
| `branch_id` | Relasi | Y | Cabang transaksi |
| `items` | Repeater | Y | Minimal 2 baris. Total Debit harus sama dengan Kredit dan > 0 |

## 5. Integrasi & Data Flow
- **Journal Linkage**: Jurnal umum yang dibuat otomatis akan mencatat `reference_type` = 'ScheduledJournalEntry' dan `reference_id` = ID Jadwal terkait.
- **Console Schedule**: Menambahkan perintah artisan `app:process-scheduled-journals` ke dalam scheduler `routes/console.php`.

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [ ] Tersedia tombol navigasi "Jurnal Terjadwal" pada header halaman Jurnal Umum.
- [ ] Tersedia halaman management (Filament Resource) untuk melihat, membuat, mengedit, dan menghapus jadwal jurnal.
- [ ] Form pembuatan memvalidasi keseimbangan Debit dan Kredit dengan benar.
- [ ] Tersedia tombol "Jalankan Sekarang" untuk memicu eksekusi manual.
- [ ] Terdapat Artisan Command `app:process-scheduled-journals` untuk melakukan pemrosesan otomatis harian dan terdaftar di `routes/console.php`.
- [ ] Unit & integration test memvalidasi alur pembuatan, pengeditan, pemrosesan otomatis, dan eksekusi manual.
