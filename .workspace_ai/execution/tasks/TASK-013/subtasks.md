# Subtasks: TASK-013 - Integrate Units (Satuan) into Products Model and Form

## Checklist
- [x] **Analysis & Design**:
  - [x] Rancang migrasi penambahan foreign key `unit_id` pada tabel `products`.
  - [x] Rancang select field `unit_id` pada `ProductForm.php` agar mudah digunakan oleh admin.
- [x] **Implementation**:
  - [x] Buat migration `add_unit_id_to_products_table`.
  - [x] Jalankan `./docker-artisan.sh migrate`.
  - [x] Tambahkan `unit_id` ke dalam array `$fillable` di model `Product`.
  - [x] Definisikan relasi `unit()` di model `Product`.
  - [x] Modifikasi `ProductForm.php` untuk menambahkan `Select::make('unit_id')` yang terhubung ke model `Unit`.
  - [x] Tambahkan kolom `unit.name` pada index table `ProductResource.php`.
- [x] **Verification**:
  - [x] Buat produk baru, pilih satuan produk, dan pastikan data tersimpan dengan benar.
  - [x] Verifikasi bahwa kolom Satuan muncul di daftar produk Back Office.
