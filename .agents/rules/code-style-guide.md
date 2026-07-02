---
trigger: always_on
---

# Panduan Code Style - Diego Music Store ERP

Dokumen ini mendefinisikan standar penulisan kode (Coding Standards) dan pola desain (Design Patterns) yang wajib diikuti oleh semua developer di proyek **Diego Music Store ERP**. Panduan ini dibuat untuk memastikan basis kode tetap bersih, modular, mudah diuji (*testable*), dan mudah dirawat (*maintainable*).

---

##1. Action Pattern untuk Logika Bisnis (`App\Actions\{NamaFeature}`)

Semua logika bisnis (business logic) tidak boleh ditulis langsung di dalam Controller, Livewire Component, atau Model. Logika tersebut harus diisolasi ke dalam **Action Class** terpisah di bawah namespace `App\Actions\{NamaFeature}`.

###  Mengapa Menggunakan Action Pattern?
* **Single Responsibility Principle (SRP):** Setiap Action hanya bertanggung jawab atas satu fungsionalitas spesifik.
* **Reusability:** Logika bisnis yang sama dapat digunakan kembali oleh Controller, Livewire, API, Console Command, atau Queue Job tanpa duplikasi kode.
* **Testability:** Action class sangat mudah diuji secara independen menggunakan Unit atau Integration Test.

###  Aturan & Struktur:
* **Penamaan Namespace:** `App\Actions\{NamaFeature}` (menggunakan bentuk jamak `Actions`, dan `{NamaFeature}` ditulis dalam format PascalCase).
* **Penamaan Class:** Menggunakan kata kerja + objek (misal: `CreateCustomer`, `UpdateSupplier`, `PostPurchaseTransaction`).
* **Method Utama:** Gunakan satu method publik `execute()` untuk menjalankan action tersebut.
* **Transaksi Database:** Pastikan operasi database yang kompleks atau melibatkan banyak tabel dibungkus di dalam transaksi database (`DB::transaction`).

###  Contoh Kode Action:
```php
<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomer
{
    /**
     * Menjalankan proses pembuatan customer baru.
     *
     * @param  array<string, mixed>  $data
     * @return Customer
     */
    public function execute(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            // Logika bisnis tambahan (misal: generate kode customer unik) dapat ditaruh di sini
            return Customer::create($data);
        });
    }
}
```

---

##  2. Helper Classes (`App\Helpers`) untuk Logika Reusable

Jika terdapat fungsi, perhitungan, manipulasi string/data, atau logika utilitas lainnya yang terindikasi akan digunakan di lebih dari satu tempat (atau memiliki potensi digunakan kembali di masa depan), buat fungsi tersebut sebagai **Helper Class** di bawah namespace `App\Helpers`.

###  Mengapa Menggunakan Helper Class?
* Menjaga file resource, Controller, dan Action tetap bersih dan fokus pada alur utama.
* Menghindari *hardcoding* logika format/konversi data di banyak tempat.

###  Aturan & Struktur:
* **Lokasi File:** Letakkan file helper di folder `app/Helpers/` dengan format namespace `App\Helpers`.
* **Desain Class:** Gunakan *static methods* agar mudah dipanggil tanpa perlu melakukan instansiasi objek.
* **Penamaan Class:** Menggunakan akhiran `Helper` (misal: `TerbilangHelper`, `ProductHelper`).

### 📝 Contoh Kode Helper:
```php
<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Mengubah angka nominal menjadi teks terbilang dalam bahasa Indonesia.
     *
     * @param  int  $number
     * @return string
     */
    public static function convert(int $number): string
    {
        $number = abs($number);
        $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $result = "";

        if ($number < 12) {
            $result = " " . $words[$number];
        } elseif ($number < 20) {
            $result = static::convert($number - 10) . " Belas";
        } // ... (lanjutan logika konversi)

        return trim($result);
    }
}
```

---

## 📏 3. Batasan Ukuran File Resource & Presentasi Data (Maksimal 1.000 Baris)

Satu file presentasi data (misalnya: Livewire Component, Controller, Form Resource, atau View Helper) **tidak boleh melebihi 1.000 baris kode (1K lines)**.

### 💡 Mengapa Dibatasi Maksimal 1.000 Baris?
* File yang terlalu besar (*God Class*) sangat sulit dipahami, rawan menimbulkan bug saat dimodifikasi, dan memperlambat proses review kode.

### 🛠️ Strategi Jika File Mendekati/Melebihi 1.000 Baris:
1. **Pecah Livewire Component:** Pisahkan form kompleks menjadi beberapa sub-komponen Livewire yang lebih kecil (misal: pisahkan tabel daftar barang, form input detail, dan modal konfirmasi).
2. **Ekstrak ke Action:** Pindahkan seluruh logika validasi tingkat lanjut atau penyimpanan data ke dalam *Action Class* (Aturan 1).
3. **Ekstrak ke Helper:** Pindahkan logika pemrosesan data, manipulasi string, atau format data ke *Helper Class* (Aturan 2).
4. **Pindahkan Query Kompleks:** Gunakan *Eloquent Scope* pada Model atau buat Query Class terpisah daripada menulis query panjang di dalam controller/komponen.

---

## 4. Integrasi Alur Kerja & Workflow

Selalu baca `workflow.md` agar alur kerja proyek (Workspace AI, pengerjaan task, pembuatan feature, dokumentasi SRS, dan eksekusi perintah terminal via wrapper script seperti `./docker-artisan.sh` dan `./docker-composer.sh`) tetap sinkron dan teratur. 

Setiap kali Anda bekerja pada kode program:
1. Pastikan Anda memahami status task di `.workspace_ai/execution/tasks/`.
2. Selalu gunakan helper docker scripts agar permission file di workspace tetap konsisten.
3. Selalu perbarui status tugas dan tulis dokumentasi secara teratur setelah implementasi selesai.

---

##  5. Pemisahan Tampilan Kustom (Custom View) Filament (Wajib Blade View)

Jika membuat komponen presentasi atau tampilan kustom di Filament (seperti render HTML kustom di dalam Form Placeholder, custom page, atau custom cell Table), **dilarang keras menulis tag HTML secara mentah (*raw HTML string*) di dalam file PHP**. Seluruh markup presentasi harus diisolasi ke dalam berkas **Blade View** terpisah.

### 💡 Mengapa Menggunakan Blade View?
* **Clean Code:** Menghindari menumpuknya kode tag HTML di dalam logika file PHP.
* **Syntax Highlighting & Formatting:** Memudahkan penyuntingan markup HTML dan CSS/Tailwind di dalam template engine `.blade.php`.
* **Separation of Concerns:** Memisahkan logika presentasi (Blade) dari konfigurasi skema data Filament (PHP).

### 📐 Aturan & Struktur:
* Buat file blade terpisah di dalam direktori `resources/views/` (misal: `resources/views/backoffice/products/stock-movements-table.blade.php`).
* Panggil view tersebut dari komponen Filament menggunakan helper `view('nama-view', $data)`.

### 📝 Contoh Kode Filament PHP:
```php
Placeholder::make('stock_history')
    ->label('Riwayat Stok')
    ->content(fn ($record) => view('backoffice.products.stock-movements-table', [
        'movements' => $record->movements,
    ]))

---

## 6. Kewajiban Menulis Unit / Integration Test

Setiap kali mengimplementasikan fitur baru, mengedit logika bisnis, atau membuat *Action Class*, developer **wajib** menulis berkas pengujian (*Unit Test* atau *Integration Test*) untuk memvalidasi fungsionalitas tersebut secara otomatis.

###  Mengapa Menulis Unit Test?
* **Mencegah Regresi:** Memastikan perubahan kode di masa depan tidak merusak fitur yang sudah berjalan dengan benar.
* **Dokumentasi Hidup:** Test case bertindak sebagai dokumentasi teknis mengenai perilaku sistem yang diharapkan.
* **Kepercayaan Kode:** Membantu memvalidasi skenario batas (*edge cases*) dan penanganan eror (*error handling*).

### Aturan & Struktur:
* Letakkan file pengujian di folder `tests/Unit/` (untuk logika terisolasi tanpa interaksi database) atau `tests/Feature/` (untuk integrasi database, request HTTP, atau alur multi-step).
* Penamaan file test harus menggunakan akhiran `Test.php` (misal: `CreateInventoryMutationTest.php`).
* Gunakan framework testing yang sudah terpasang (Pest PHP / PHPUnit) untuk menjalankan pengujian.
```