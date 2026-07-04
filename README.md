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

