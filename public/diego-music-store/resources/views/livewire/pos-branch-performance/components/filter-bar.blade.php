<!-- Filter Header & Branch Selector Bar -->
<div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-2xl">
                <i class="ph-bold ph-buildings text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                    {{ $currentBranch?->store_name ?: ($currentBranch?->name ?: 'Konsolidasi Seluruh Cabang') }}
                </h2>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                    Mengelola stok, pelanggan, dan Laba Rugi per cabang dalam satu entitas bisnis
                </p>
            </div>
        </div>
    </div>

    <!-- Date Range & Branch Filters -->
    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <!-- Preset Date Buttons -->
        <div class="flex items-center bg-slate-100 dark:bg-slate-700/50 p-1 rounded-2xl border border-slate-200 dark:border-slate-700">
            <button type="button" wire:click="setQuickDateRange('today')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                Hari Ini
            </button>
            <button type="button" wire:click="setQuickDateRange('this_month')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->startOfMonth()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                Bulan Ini
            </button>
            <button type="button" wire:click="setQuickDateRange('this_year')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->startOfYear()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                Tahun Ini
            </button>
        </div>

        <!-- Filter Dropdown Cabang -->
        <select wire:model.live="selectedBranchId" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
            <option value="all">🌐 Semua Cabang (Konsolidasi Entitas Bisnis)</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city ?: $b->store_name }})</option>
            @endforeach
        </select>
    </div>
</div>
