<!-- Filter Toolbar -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-sm space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Col 1: Pencarian -->
        <div class="lg:col-span-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Cabang</label>
            <x-pos.form.input model="search" :live="true" placeholder="Cari nama cabang, kota, alamat..." icon="ph-magnifying-glass" size="sm" />
        </div>

        <!-- Col 2: Status -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Cabang</label>
            <x-pos.form.select model="selectedStatus" :live="true" size="sm">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Non-Aktif</option>
            </x-pos.form.select>
        </div>

        <!-- Col 3: Reset -->
        <div class="flex items-end">
            <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="w-full" wire:click="resetFilters">
                Reset Filter
            </x-pos.utility.button>
        </div>
    </div>
</div>
