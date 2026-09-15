<!-- DASHBOARD HEADER & FILTER TOOLBAR -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-6">
    <div>
        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            Analisis Performansi &amp; Keuangan Bisnis
        </h2>
        <div class="flex flex-wrap items-center gap-2 mt-2">
            <a href="{{ route('pos.branch-performance') }}" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <i class="ph-bold ph-buildings text-sm"></i> Performa Cabang
            </a>
            <a href="{{ route('pos') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                <i class="ph-bold ph-shopping-cart text-sm"></i> POS Kasir
            </a>
            <a href="/backoffice" class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                <i class="ph-bold ph-house text-sm"></i> Backoffice
            </a>
        </div>
    </div>

    <!-- Interactive Filters -->
    <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-end gap-3 w-full sm:w-auto">
        <!-- Date From -->
        <div class="col-span-1 sm:w-36">
            <x-pos.form.input
                type="date"
                label="Dari Tanggal"
                model="dateFrom"
                :live="true"
                size="sm"
            />
        </div>

        <!-- Date To -->
        <div class="col-span-1 sm:w-36">
            <x-pos.form.input
                type="date"
                label="Sampai Tanggal"
                model="dateTo"
                :live="true"
                size="sm"
            />
        </div>

        <!-- Branch Filter -->
        <div class="col-span-2 sm:w-48">
            <x-pos.form.dropdown
                label="Cabang Toko"
                model="branchId"
                :live="true"
                size="sm"
                icon="ph-buildings"
            >
                <option value="">Semua Cabang</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.dropdown>
        </div>

        <!-- Product Category Filter -->
        <div class="col-span-2 sm:w-48">
            <x-pos.form.dropdown
                label="Kategori Produk"
                model="productCategory"
                :live="true"
                size="sm"
                icon="ph-tag"
            >
                <option value="">Semua Kategori</option>
                @foreach ($productCategories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </x-pos.form.dropdown>
        </div>

        <!-- Reset Filter Button -->
        <div class="col-span-2 sm:w-auto flex flex-col justify-end">
            <x-pos.utility.button
                variant="danger"
                size="sm"
                icon="ph-arrows-counter-clockwise"
                wire:click="resetFilters"
                title="Reset Filter"
                class="!py-2"
            >
                Reset
            </x-pos.utility.button>
        </div>
    </div>
</div>
