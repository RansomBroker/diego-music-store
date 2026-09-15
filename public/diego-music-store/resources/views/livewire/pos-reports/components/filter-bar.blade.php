<!-- Common Filter Toolbar Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Date Range & Branch Filters -->
        <div class="flex flex-wrap items-center gap-3">
            @if ($activeTab !== 'ar-aging' && $activeTab !== 'stock-prices')
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Dari:</span>
                    <input
                        type="date"
                        wire:model.live="dateFrom"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                    >
                    <span class="text-xs font-bold text-slate-500">s/d</span>
                    <input
                        type="date"
                        wire:model.live="dateTo"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                    >
                </div>
            @endif

            @if (count($branches) > 1)
                <select
                    wire:model.live="selectedBranchId"
                    class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                >
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif

            <div class="relative min-w-[200px]">
                <i class="ph ph-magnifying-glass text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari kata kunci..."
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                >
            </div>
        </div>

        <!-- Quick Date Presets -->
        @if ($activeTab !== 'ar-aging' && $activeTab !== 'stock-prices')
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset:</span>
                <button wire:click="setQuickDateRange('today')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition cursor-pointer">Hari Ini</button>
                <button wire:click="setQuickDateRange('this_week')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition cursor-pointer">Minggu Ini</button>
                <button wire:click="setQuickDateRange('this_month')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition cursor-pointer">Bulan Ini</button>
                <button wire:click="setQuickDateRange('this_year')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition cursor-pointer">Tahun Ini</button>
            </div>
        @endif
    </div>
</div>
