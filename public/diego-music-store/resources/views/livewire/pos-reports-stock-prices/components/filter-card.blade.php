<!-- Common Filter Toolbar Card (4-Column Layout) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-sm space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Col 1: Status Ketersediaan Stok -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Ketersediaan Stok</label>
            <x-pos.form.select model="stockStatus" :live="true" size="sm" icon="ph-funnel">
                <option value="all">Semua Status Stok</option>
                <option value="available">Stok Tersedia / Aman</option>
                <option value="low">Stok Rendah (Batas Min)</option>
                <option value="out_of_stock">Stok Habis (Kosong)</option>
            </x-pos.form.select>
        </div>

        <!-- Col 2: Filter Cabang -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Filter Cabang</label>
            <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                <option value="">Semua Cabang (Total Stok)</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Col 3: Kategori Produk -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Produk</label>
            <x-pos.form.select model="selectedCategory" :live="true" size="sm" icon="ph-tag">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Col 4: Pencarian Barang & Reset -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Barang</label>
            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-pos.form.input model="search" :live="true" placeholder="Cari SKU / Barcode / Nama / Merk..." icon="ph-magnifying-glass" size="sm" />
                </div>
                <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="shrink-0" wire:click="resetFilters" title="Reset Filter">
                    Reset
                </x-pos.utility.button>
            </div>
        </div>
    </div>
</div>
