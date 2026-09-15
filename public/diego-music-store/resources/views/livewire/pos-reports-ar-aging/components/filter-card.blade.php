<!-- Comprehensive Filter Toolbar Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
        <!-- Filter Cabang -->
        @if (count($branches) > 1)
            <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Cabang</label>
                <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>
        @endif

        <!-- Filter Pelanggan -->
        <div>
            <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</label>
            <select wire:model.live="selectedCustomerId" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                <option value="">Semua Pelanggan</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Kategori Umur Piutang -->
        <div>
            <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Umur Piutang</label>
            <select wire:model.live="agingGroupFilter" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                <option value="">Semua Umur</option>
                <option value="0-30">0 - 30 Hari (Lancar)</option>
                <option value="31-60">31 - 60 Hari</option>
                <option value="61-90">61 - 90 Hari</option>
                <option value="over-90">> 90 Hari (Menunggak)</option>
            </select>
        </div>

        <!-- Filter Rentang Tanggal Dari -->
        <div>
            <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Dari Tgl Inv</label>
            <input type="date" wire:model.live="dateFrom" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
        </div>

        <!-- Filter Rentang Tanggal Sampai -->
        <div>
            <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Sampai Tgl Inv</label>
            <input type="date" wire:model.live="dateTo" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
        </div>

        <!-- Search Keyword -->
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                <x-pos.form.input model="search" :live="true" placeholder="No Invoice / Nama..." icon="ph-magnifying-glass" size="sm" />
            </div>
            <x-pos.utility.button variant="danger" size="sm" icon="ph-arrow-counter-clockwise" wire:click="resetFilters" title="Reset Filter" />
        </div>
    </div>
</div>
