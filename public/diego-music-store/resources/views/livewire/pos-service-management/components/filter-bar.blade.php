<!-- Filter Toolbar (Integrated in Table Card) -->
<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Col 1: Filter Status -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Status Progress</label>
            <x-pos.form.select model="selectedStatus" :live="true" size="sm" icon="ph-squares-four">
                <option value="">Semua Status Progress</option>
                <option value="received">Diterima</option>
                <option value="diagnosing">Proses Diagnosa</option>
                <option value="in_progress">Dikerjakan</option>
                <option value="waiting_parts">Menunggu Sparepart</option>
                <option value="completed">Selesai Service</option>
                <option value="picked_up">Siap / Sudah Diambil</option>
                <option value="cancelled">Dibatalkan</option>
            </x-pos.form.select>
        </div>

        <!-- Col 2: Filter Cabang -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Filter Cabang</label>
            <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Col 3: Filter Teknisi -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Teknisi Penanggung Jawab</label>
            <x-pos.form.select model="selectedTechnicianId" :live="true" size="sm" icon="ph-user-gear">
                <option value="">Semua Teknisi</option>
                @foreach ($technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Col 4: Pencarian & Reset -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian Tiket / Unit</label>
            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-pos.form.input model="search" :live="true" placeholder="No. Tiket / Pelanggan / Unit..." icon="ph-magnifying-glass" size="sm" />
                </div>
                <x-pos.utility.button variant="primary" size="sm" icon="ph-arrow-counter-clockwise" class="shrink-0" wire:click="resetFilters" title="Reset Filter">
                    Reset
                </x-pos.utility.button>
            </div>
        </div>
    </div>
</div>
