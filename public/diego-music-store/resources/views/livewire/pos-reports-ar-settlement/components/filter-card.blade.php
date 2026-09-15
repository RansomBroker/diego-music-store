<!-- Filter Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Date Range, Branch & Search Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Dari:</span>
                <x-pos.form.input type="date" model="dateFrom" :live="true" size="sm" />
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">s/d</span>
                <x-pos.form.input type="date" model="dateTo" :live="true" size="sm" />
            </div>

            @if (count($branches) > 1)
                <div class="min-w-[160px]">
                    <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </x-pos.form.select>
                </div>
            @endif

            <div class="min-w-[220px]">
                <x-pos.form.input model="search" :live="true" placeholder="Cari bukti/pelanggan..." icon="ph-magnifying-glass" size="sm" />
            </div>
        </div>

        <!-- Quick Date Presets -->
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset:</span>
            <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('today')">Hari Ini</x-pos.utility.button>
            <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_week')">Minggu Ini</x-pos.utility.button>
            <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_month')">Bulan Ini</x-pos.utility.button>
            <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_year')">Tahun Ini</x-pos.utility.button>
        </div>
    </div>
</div>
