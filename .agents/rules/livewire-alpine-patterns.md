# Livewire v4 + Alpine.js — Pola & Anti-Pattern

Proyek ini menggunakan **Livewire v4.x** dan **Alpine.js v3**.
Patuhi pola berikut untuk menghindari bug subtle dan masalah performa.

---

## A. Livewire Computed Property

### 1. Jangan Set Public Property di Dalam Computed Property

Side-effect di dalam `getXxxProperty()` tidak reliable — nilainya bisa tidak terbaca
dengan benar saat blade render karena lifecycle Livewire.

**❌ SALAH:**
```php
public function getProductsProperty() {
    $results = $query->get();
    $this->someFlag = $results->count() > 10; // tidak reliable!
    return $results;
}
```

**✅ BENAR — Buat computed property terpisah:**
```php
public function getProductsProperty() {
    return $query->limit($take + 1)->get()->take($take);
}

public function getSomeFlagProperty(): bool {
    // Baca dari computed property lain — Livewire cache-kan per request
    return $this->products->count() >= $take;
}
```

### 2. Akses Computed Property di Blade via `$this->`

Untuk memastikan computed property terpanggil (bukan membaca public property snapshot):

```blade
{{-- ✓ Panggil computed property --}}
:hasMore="$this->hasMoreProducts"

{{-- Hati-hati: $hasMoreProducts bisa membaca snapshot lama --}}
:hasMore="$hasMoreProducts"
```

---

## B. Alpine.js + `$wire`

### 3. `$wire` Hanya Tersedia di Alpine Context

`$wire` adalah Alpine magic — **tidak tersedia** di `<script>` global,
di IntersectionObserver callback, setTimeout, atau Promise chain.

**❌ SALAH:**
```html
<script>
function myComponent() {
    return {
        setup() {
            new IntersectionObserver(() => {
                $wire.someMethod(); // ReferenceError: $wire is not defined
            });
        }
    };
}
</script>
```

**✅ BENAR — Capture `this.$wire` SEBELUM masuk callback:**
```blade
<div x-data="{
    init() {
        const wire = this.$wire; // capture di Alpine context
        new IntersectionObserver(() => {
            wire.someMethod();   // ✓ works
        });
    }
}">
```

### 4. Jangan Bergantung pada `Livewire.hook()` Tanpa Verifikasi Versi

Hook API berbeda antara Livewire v3 dan v4. Sebelum menggunakan hook:
1. Cek versi: `./docker-composer.sh show livewire/livewire | grep version`
2. Versi proyek ini: **Livewire v4.x**

**Alternatif reliable di Livewire v4:**
- `wire:click`, `wire:loading`, `wire:target` — native Livewire directive
- Native browser `scroll` event listener dengan `$wire` yang di-capture
- `@scroll.passive` Alpine directive (inline di blade, bukan di `<script>` global)

---

## C. Optimasi DB Query di Livewire Component

### 5. Guard Computed Property dengan State Modal/Kondisi

Computed property dipanggil **setiap re-render** Livewire. Kalau properti itu berat
(query ribuan rows), guard dengan kondisi sebelum query.

**❌ SALAH — Query jalan setiap re-render:**
```php
public function getProductsProperty() {
    return ProductVariant::with(['product', 'branchStocks'])->get(); // selalu jalan!
}
```

**✅ BENAR — Guard dengan modal state:**
```php
public function getProductsProperty() {
    if (!$this->showProductSearchModal) {
        return collect(); // tidak query kalau modal tutup
    }
    // ... query
}
```

### 6. Filter di SQL, Bukan di PHP Collection

Jangan ambil semua data lalu filter di PHP (`->filter()`).
Gunakan `WHERE` clause di query.

**❌ SALAH — Load semua, filter di PHP:**
```php
$all = ProductVariant::with('product')->get(); // load 2889 rows

return $all->filter(function($v) {
    return $this->matchesCategory($v); // loop PHP per row
});
```

**✅ BENAR — Filter di SQL:**
```php
return ProductVariant::whereHas('product', function($q) {
    $q->where('is_active', true)
      ->where('category', $this->activeCategory); // filter di DB
})->get();
```

### 7. Pagination untuk Data Besar

Jangan load semua data sekaligus. Gunakan `limit()` + increment page untuk infinite scroll,
atau `paginate()` untuk pagination standar.

```php
const PRODUCTS_PER_PAGE = 40;

public function getProductsProperty() {
    $take = self::PRODUCTS_PER_PAGE * $this->productPage;
    // Ambil $take+1 untuk deteksi apakah masih ada lebih (tanpa COUNT query tambahan)
    return $query->limit($take + 1)->get()->take($take);
}

public function getHasMoreProductsProperty(): bool {
    $take = self::PRODUCTS_PER_PAGE * $this->productPage;
    return $this->products->count() >= $take;
}
```

### 8. Select Kolom Minimal di Eager Loading

Saat eager load relasi hanya untuk akses beberapa kolom, batasi dengan `select()`:

**❌ SALAH — Load semua kolom relasi:**
```php
ProductVariant::with('product')->whereIn('id', $ids)->get();
```

**✅ BENAR — Hanya kolom yang diperlukan:**
```php
ProductVariant::with(['product:id,category,type'])
    ->select(['id', 'product_id'])
    ->whereIn('id', $ids)
    ->get();
```

### 9. Hindari Klasifikasi Data Berbasis Parse Nama String

Jangan tentukan kategori/klasifikasi dari parsing nama string produk.
Gunakan kolom DB yang sudah ada (`category`, `type`, dll).

**❌ SALAH — Fragile, tidak akurat:**
```php
$name = strtolower($variant->product->name);
if (str_contains($name, 'gitar')) return 'Gitar & Bass'; // bisa salah!
```

**✅ BENAR — Gunakan kolom DB:**
```php
// Filter langsung di query
$q->where('category', $this->activeCategory);

// Atau ambil distinct values dari DB untuk render UI
Product::distinct()->pluck('category');
```

---

## D. Perintah Diagnosis Berguna

```bash
# Cek versi Livewire
./docker-composer.sh show livewire/livewire | grep version

# Lihat schema tabel
./docker-artisan.sh tinker --execute="echo implode(', ', Schema::getColumnListing('products'));"

# Lihat distinct values kolom (untuk kategori, tipe, dll)
./docker-artisan.sh tinker --execute="
\$cats = App\Models\Product::distinct()->pluck('category')->filter()->sort()->values();
echo \$cats->implode(', ');
"

# Hitung jumlah row (cek skala data sebelum buat query)
./docker-artisan.sh tinker --execute="
echo 'Variants aktif: ' . App\Models\ProductVariant::where('is_active', true)->count();
"
```
