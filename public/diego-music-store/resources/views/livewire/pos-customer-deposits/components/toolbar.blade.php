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
<div class="p-4 sm:px-6 sm:py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-900 transition-colors">
    <!-- Search Input -->
    <div class="w-full md:max-w-xs">
        <x-pos.form.input
            model="search"
            :live="true"
            debounce="300ms"
            placeholder="Cari no. deposit, produk, pelanggan..."
            icon="ph-magnifying-glass"
            size="sm"
        />
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Status Filter -->
        <div class="w-full sm:w-48">
            <x-pos.form.dropdown
                model="statusFilter"
                :live="true"
                size="sm"
                icon="ph-funnel"
            >
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Pelunasan</option>
                <option value="settled">Lunas</option>
                <option value="cancelled">Dibatalkan</option>
            </x-pos.form.dropdown>
        </div>

        <!-- Date Filter -->
        <div class="w-full sm:w-36">
            <x-pos.form.input
                type="date"
                model="dateFrom"
                :live="true"
                size="sm"
                title="Dari Tanggal"
            />
        </div>
        <span class="text-xs text-slate-400 font-bold hidden sm:inline">-</span>
        <div class="w-full sm:w-36">
            <x-pos.form.input
                type="date"
                model="dateTo"
                :live="true"
                size="sm"
                title="Sampai Tanggal"
            />
        </div>

        @if ($search || $statusFilter || $dateFrom || $dateTo)
            <x-pos.utility.button
                variant="danger"
                size="sm"
                icon="ph-x"
                wire:click="resetFilters"
                title="Reset Filter"
            >
                Reset
            </x-pos.utility.button>
        @endif
    </div>
</div>
