<!-- Page Header (Title & Breadcrumbs & CTA) -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <!-- Breadcrumbs -->
        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-400 dark:text-slate-600 mx-1"></i>
                        <span class="text-slate-600 dark:text-slate-300 font-bold">Deposit Pelanggan</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Page Title -->
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight flex items-center gap-2.5">
            <span>Deposit & Titipan Dana Pelanggan</span>
        </h1>
    </div>

    <!-- Add Action Button -->
    <x-pos.utility.button
        variant="primary"
        icon="ph-plus"
        wire:click="openCreateModal"
    >
        Tambah Deposit Baru
    </x-pos.utility.button>
</div>

<!-- Toolbar (Search & Filter Status & Dates) -->
<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5">
        
        <!-- Pencarian -->
        <div class="sm:col-span-2 lg:col-span-3 xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian Deposit</label>
            <x-pos.form.input
                model="search"
                :live="true"
                debounce="300ms"
                placeholder="Cari no. deposit, produk, pelanggan..."
                icon="ph-magnifying-glass"
                rounded="rounded-lg"
            />
        </div>

        <!-- Filter Status -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Status Deposit</label>
            <x-pos.form.select
                model="statusFilter"
                :live="true"
                rounded="rounded-lg"
                icon="ph-funnel"
            >
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Pelunasan</option>
                <option value="settled">Lunas</option>
                <option value="cancelled">Dibatalkan</option>
            </x-pos.form.select>
        </div>

        <!-- Dari Tanggal -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Dari Tanggal</label>
            <x-pos.form.input
                type="date"
                model="dateFrom"
                :live="true"
                rounded="rounded-lg"
            />
        </div>

        <!-- Sampai Tanggal -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Sampai Tanggal</label>
            <x-pos.form.input
                type="date"
                model="dateTo"
                :live="true"
                rounded="rounded-lg"
            />
        </div>

        <!-- Tombol Reset -->
        <div class="flex items-end">
            <x-pos.utility.button
                variant="danger"
                icon="ph-arrow-counter-clockwise"
                wire:click="resetFilters"
                title="Reset Filter"
                class="w-full"
            >
                Reset
            </x-pos.utility.button>
        </div>

    </div>
</div>
