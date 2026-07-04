---
trigger: always_on
---

# Panduan Code Style - Diego Music Store ERP

Standar penulisan kode (Coding Standards) & pola desain (Design Patterns) wajib proyek **Diego Music Store ERP** agar basis kode bersih, modular, mudah diuji (*testable*), dan mudah dirawat (*maintainable*).

---

## 1. Action Pattern (`App\Actions\{NamaFeature}`)
**Semua logika bisnis** harus diisolasi ke dalam **Action Class** terpisah. Jangan menuliskannya di Controller, Livewire, atau Model.

* **Aturan & Struktur:**
  * Namespace: `App\Actions\{NamaFeature}` (PascalCase).
  * Penamaan Class: Kata Kerja + Objek (contoh: `CreateCustomer`, `PostPurchaseTransaction`).
  * Method Utama: Gunakan satu method publik `execute()`.
  * Transaksi: Bungkus operasi multi-tabel dalam `DB::transaction()`.

```php
<?php
namespace App\Actions\Customer;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomer
{
    public function execute(array $data): Customer
    {
        return DB::transaction(fn() => Customer::create($data));
    }
}
```

---

## 2. Helper Classes (`App\Helpers`)
Gunakan **Helper Class** untuk logika reusable (utilitas, konversi, perhitungan, manipulasi data).

* **Aturan & Struktur:**
  * Lokasi: `app/Helpers/`.
  * Desain: Gunakan *static methods*.
  * Penamaan Class: Akhiran `Helper` (contoh: `TerbilangHelper`, `ProductHelper`).

```php
<?php
namespace App\Helpers;

class TerbilangHelper
{
    public static function convert(int $number): string
    {
        // Logika konversi angka ke kata
        return trim($result);
    }
}
```

---

## 3. Batasan Ukuran File Presentasi (Maksimal 1.000 Baris)
Satu file presentasi data (Livewire Component, Controller, Form Resource, View Helper) **dilarang melebihi 1.000 baris**.

* **Strategi Refactoring:**
  * Pecah form/komponen kompleks menjadi sub-komponen Livewire lebih kecil.
  * Pindahkan validasi/proses data ke **Action Class** (Aturan 1).
  * Pindahkan manipulasi/format data ke **Helper Class** (Aturan 2).
  * Ekstrak query kompleks ke **Eloquent Scope** atau Query Class terpisah.

---

## 4. Integrasi Alur Kerja & Workflow
Patuhi alur kerja di `workflow.md`:
1. Pahami status task di `.workspace_ai/execution/tasks/`.
2. Gunakan wrapper script Docker (`./docker-artisan.sh` dan `./docker-composer.sh`) agar permission file tetap konsisten.
3. Selalu perbarui status task dan dokumentasi setelah implementasi selesai.

---

## 5. Kustomisasi Tampilan Filament (Wajib Blade View)
Dilarang menulis tag HTML secara mentah (*raw HTML string*) di dalam file PHP Filament. Gunakan **Blade View** terpisah.

* **Aturan:**
  * Letakkan file blade di `resources/views/`.
  * Panggil view menggunakan helper `view('nama-view', $data)`.

```php
Placeholder::make('stock_history')
    ->label('Riwayat Stok')
    ->content(fn ($record) => view('backoffice.products.stock-movements-table', [
        'movements' => $record->movements,
    ]))
```

---

## 6. Kewajiban Menulis Unit & Integration Test
Setiap penambahan fitur baru, perubahan logika bisnis, atau pembuatan Action Class **wajib** disertai pengujian otomatis.

* **Aturan:**
  * Letakkan di `tests/Unit/` (tanpa interaksi DB) atau `tests/Feature/` (dengan DB/HTTP).
  * Gunakan akhiran `Test.php` (contoh: `CreateInventoryMutationTest.php`).
```