# Feature: FEATURE-006 - Sesi Kasir Harian & Laci Kas (Daily Cash Session)

## Parent Epic
- EPIC-003 - Front Desk & POS Dasar

## Description
Fitur ini mengelola laci kas harian (sesi kasir). Kasir diwajibkan membuka sesi dengan menginput uang modal awal sebelum melakukan transaksi. Saat shift selesai, kasir melakukan tutup sesi dengan metode *blind count* (menghitung fisik uang riil tanpa melihat ekspektasi sistem), lalu sistem membandingkan nilai fisik dan ekspektasi untuk mengetahui selisih kurang/lebih kas, diikuti dengan pencetakan Z-Report.

---

# Software Requirements Specification (SRS)

## 1. Pendahuluan & Tujuan
Untuk mengontrol arus kas masuk dan keluar di laci kasir secara harian, mencegah kecurangan (fraud), dan mempermudah rekonsiliasi keuangan per cabang secara real-time.

## 2. User Roles & Aktor
- **Kasir**: Melakukan buka sesi (input modal awal), melakukan transaksi POS harian, dan melakukan tutup sesi (blind count).
- **Supervisor / Owner**: Melakukan otorisasi pembatalan sesi atau menyetujui jika terjadi selisih kas (selisih kurang/lebih uang di laci).

## 3. Alur Kerja (Workflows / Use Cases)
### 3.1. Buka Sesi Kasir (Open Session)
- **Pre-condition**: Kasir belum memiliki sesi yang berstatus `open` di cabang aktif saat ini.
- **Main Flow**:
  1. Kasir membuka menu POS. Sistem mendeteksi tidak ada sesi aktif.
  2. Sistem mengarahkan kasir ke halaman/modal "Buka Sesi Kasir".
  3. Kasir memasukkan jumlah modal awal (`opening_cash`).
  4. Kasir menekan tombol "Buka Sesi".
  5. Sistem mencatat `user_id`, `cabang_id`, `opened_at`, status `open`, dan mengaktifkan akses transaksi POS.
- **Post-condition**: Akses menu transaksi POS terbuka untuk kasir terkait.

### 3.2. Tutup Sesi & Blind Count (Close Session)
- **Pre-condition**: Kasir memiliki sesi kasir berstatus `open`.
- **Main Flow**:
  1. Kasir menekan tombol "Tutup Sesi" di panel POS.
  2. Sistem menampilkan form "Tutup Sesi" yang menyembunyikan ekspektasi kas sistem (*blind count*).
  3. Kasir menghitung fisik uang riil di laci kas dan menginput jumlahnya ke field `actual_cash`.
  4. Kasir memasukkan catatan tambahan (jika ada) dan menekan "Tutup Sesi".
  5. Sistem membandingkan `actual_cash` dengan `expected_cash` (Modal Awal + Transaksi Tunai - Pengeluaran Kas).
  6. Jika **tidak ada selisih**, status sesi diubah menjadi `closed`, mencatat `closed_at`, dan mencetak Z-Report.
- **Alternative Flow (Ada Selisih Kas)**:
  - Pada langkah 5, jika ada selisih (`actual_cash != expected_cash`), sistem memunculkan pop-up peringatan selisih.
  - Sesi memerlukan **Otorisasi Supervisor** (memasukkan Password/PIN Supervisor) untuk menyetujui selisih tersebut sebelum status sesi dapat ditutup menjadi `closed`.

## 4. Spesifikasi UI/UX & Input Validasi
### 4.1. Elemen Form Buka Sesi
| Nama Field | Tipe Data | Wajib (Y/T) | Aturan Validasi / Business Logic |
| :--- | :--- | :--- | :--- |
| `opening_cash` | Decimal / Bigint | Y | Harus angka $\ge 0$. Default 0 |

### 4.2. Elemen Form Tutup Sesi (Blind Count)
| Nama Field | Tipe Data | Wajib (Y/T) | Aturan Validasi / Business Logic |
| :--- | :--- | :--- | :--- |
| `actual_cash` | Decimal / Bigint | Y | Harus angka $\ge 0$. |
| `notes` | Text | T | Opsional untuk keterangan jika ada selisih. |
| `supervisor_password` | Password | T | Wajib diisi jika terdapat selisih untuk otorisasi. |

## 5. Integrasi & Data Flow
- **POS Integration**: Setiap transaksi penjualan dengan metode pembayaran tunai secara otomatis menambahkan nilai `expected_cash` pada sesi kasir yang aktif.
- **Z-Report Print**: Ketika sesi ditutup, sistem men-trigger proses pencetakan Z-Report berupa slip struk thermal.

## 6. Kriteria Keberhasilan (Acceptance Criteria)
- [ ] Database tabel `cash_sessions` telah didefinisikan dengan migration.
- [ ] Kasir tidak bisa mengakses halaman POS jika belum membuka sesi kasir.
- [ ] Form Buka Sesi menyimpan modal awal kas dengan benar.
- [ ] Form Tutup Sesi melakukan perhitungan selisih kas secara tepat (Blind Counting).
- [ ] Tombol Tutup Sesi mencetak Z-Report ringkasan keuangan sesi.
- [ ] Logika pembatalan atau persetujuan selisih memerlukan otorisasi Supervisor.
