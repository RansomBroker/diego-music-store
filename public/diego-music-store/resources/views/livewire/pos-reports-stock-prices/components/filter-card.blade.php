<!-- Toolbar (Filters & Search - Terhubung Langsung ke Tabel) -->
<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Pencarian Barang -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian Barang</label>
            <x-pos.form.input model="search" :live="true" placeholder="Cari SKU, barcode, nama, merk..." icon="ph-magnifying-glass" rounded="rounded-lg" />
        </div>

        <!-- Filter Cabang -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Filter Cabang</label>
            <x-pos.form.select model="selectedBranchId" :live="true" rounded="rounded-lg" icon="ph-storefront">
                <option value="">Semua Cabang (Total Stok)</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Kategori Produk -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Kategori Produk</label>
            <x-pos.form.select model="selectedCategory" :live="true" rounded="rounded-lg" icon="ph-tag">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Status Ketersediaan Stok & Reset -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Status Ketersediaan</label>
            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-pos.form.select model="stockStatus" :live="true" rounded="rounded-lg" icon="ph-funnel">
                        <option value="all">Semua Status</option>
                        <option value="available">Stok Tersedia / Aman</option>
                        <option value="low">Stok Rendah</option>
                        <option value="out_of_stock">Stok Habis</option>
                    </x-pos.form.select>
                </div>
                <x-pos.utility.button variant="danger" icon="ph-arrow-counter-clockwise" class="shrink-0" wire:click="resetFilters" title="Reset Filter">
                    Reset
                </x-pos.utility.button>
            </div>
        </div>
    </div>
</div>
