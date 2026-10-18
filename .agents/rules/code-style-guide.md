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



---

## 2. Helper Classes (`App\Helpers`)
Gunakan **Helper Class** untuk logika reusable (utilitas, konversi, perhitungan, manipulasi data).

* **Aturan & Struktur:**
  * Lokasi: `app/Helpers/`.
  * Desain: Gunakan *static methods*.
  * Penamaan Class: Akhiran `Helper` (contoh: `TerbilangHelper`, `ProductHelper`).


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


---

## 6. Kewajiban Menulis Unit & Integration Test
Setiap penambahan fitur baru, perubahan logika bisnis, atau pembuatan Action Class **wajib** disertai pengujian otomatis.

* **Aturan:**
  * Letakkan di `tests/Unit/` (tanpa interaksi DB) atau `tests/Feature/` (dengan DB/HTTP).
  * Gunakan akhiran `Test.php` (contoh: `CreateInventoryMutationTest.php`).

---

## 7. Eksekusi Docker & Schema Inspection (JIT)
* **Eksekusi Docker:** Gunakan wrapper `./docker-artisan.sh <command>` atau langsung via container:
  `docker exec -w /var/www/public/diego-music-store lemp-app-diego-music-store php artisan <command>`
* **Just-In-Time (JIT) Model Inspection:** Dilarang menebak kolom, relasi, atau tipe data model. Periksa skema model langsung via artisan:
  `docker exec -w /var/www/public/diego-music-store lemp-app-diego-music-store php artisan model:show <ModelName>`
  *(Contoh: `docker exec -w /var/www/public/diego-music-store lemp-app-diego-music-store php artisan model:show Product`)*
* **Fallback:** Jika container tidak aktif, periksa langsung file model di `public/diego-music-store/app/Models/<ModelName>.php` atau file migrasi terkait di `database/migrations/`.