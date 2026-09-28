# Diego Music Store ERP - Docker Environment

Lingkungan pengembangan Docker LEMP (Linux, Nginx, MySQL, PHP) untuk **Diego Music Store ERP**.

## Struktur Direktori
```
diego-music-store-project/
├── Dockerfile              # Konfigurasi container PHP-FPM
├── docker-compose.yml      # Orchestration container Docker
├── nginx/
│   └── conf.d/
│       └── default.conf    # Konfigurasi Nginx
└── public/
    └── diego-music-store/  # Direktori Laravel project utama
```

---

## Cara Menjalankan Project

### 1. Jalankan Container Docker
```bash
docker compose up -d
```

### 2. Jalankan Perintah Artisan / Composer
Untuk menjaga konsistensi permission file antara host dan container, selalu gunakan helper script berikut:
- **Artisan**: `./docker-artisan.sh <perintah>` (Contoh: `./docker-artisan.sh migrate`)
- **Composer**: `./docker-composer.sh <perintah>` (Contoh: `./docker-composer.sh install`)

### 3. Matikan Container
```bash
docker compose down
```

---

## ⏰ Penjadwalan Tugas Otomatis (Cron / Scheduler)

Sistem menggunakan **Laravel Scheduler** untuk menjalankan tugas otomatis, seperti:
1. **Tutup Buku Akhir Tahun (`app:year-end-closing`)**: Memindahkan akumulasi saldo Laba Tahun Berjalan (`311301001`) ke Laba Ditahan (`311201001`) setiap tanggal **31 Desember pukul 23:59**.
2. **Pemrosesan Jurnal Terjadwal (`app:process-scheduled-journals`)**: Memproses jurnal otomatis yang dijadwalkan setiap hari (*daily*).

### 1. Cek Daftar Tugas Terjadwal
Untuk melihat daftar tugas terjadwal beserta waktu eksekusi berikutnya:
```bash
./docker-artisan.sh schedule:list
```

### 2. Menjalankan Scheduler di Lingkungan Development
Untuk pengujian lokal tanpa memasang cron system di host:
```bash
# Menjalankan scheduler worker (berjalan terus di terminal dan mengecek jadwal setiap menit)
./docker-artisan.sh schedule:work
```

### 3. Konfigurasi Cron di Server Production (Host Crontab)
Pada server Linux production, daftarkan cron job pada user host dengan perintah `crontab -e`:
```bash
* * * * * cd /path/ke/diego-music-store-project && ./docker-artisan.sh schedule:run >> /dev/null 2>&1
```
> **Catatan:** Ganti `/path/ke/diego-music-store-project` dengan path direktori absolut project Anda di server.

### 4. Menjalankan Task Secara Manual (Ad-hoc / Testing)
Jika Anda ingin mengeksekusi langsung tanpa menunggu jadwal:
```bash
# Menjalankan seluruh tugas terjadwal yang jatuh tempo saat ini
./docker-artisan.sh schedule:run

# Menjalankan Tutup Buku Tahunan secara langsung (opsional: sertakan --year dan --branch)
./docker-artisan.sh app:year-end-closing
./docker-artisan.sh app:year-end-closing --year=2026
```

---

## 🔍 Scanner Codebase & Filament (Baru)

Untuk mempermudah pemahaman struktur kode, penggunaan Helper, Action Pattern, dan komponen Filament, Anda dapat menjalankan perintah scan interaktif:

```bash
# Scan seluruh codebase (Helpers, Actions, Filament Resources)
./docker-artisan.sh code:scan

# Scan Helpers saja
./docker-artisan.sh code:scan --type=helpers

# Scan Actions saja
./docker-artisan.sh code:scan --type=actions

# Scan Filament Resources saja
./docker-artisan.sh code:scan --type=filament
```

Perintah ini akan memindai folder:
* `app/Helpers/` -> Menampilkan daftar helper publik berserta dokumentasinya.
* `app/Actions/` -> Menampilkan daftar Action pattern berdasarkan modul fitur dan signature execute.
* `app/Filament/Resources/` -> Menampilkan daftar Filament Resource lengkap dengan model, grup navigasi, label, dan daftar halaman.

