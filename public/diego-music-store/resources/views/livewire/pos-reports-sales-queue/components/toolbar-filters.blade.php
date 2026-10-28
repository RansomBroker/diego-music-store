<!-- Toolbar (Filters & Search) -->
<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-3.5">
        
        <!-- Pencarian -->
        <div class="sm:col-span-2 xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian</label>
            <x-pos.form.input 
                model="search" 
                :live="true" 
                debounce="300ms" 
                placeholder="Cari sales, no. invoice..." 
                icon="ph-magnifying-glass" 
                rounded="rounded-lg" 
            />
        </div>

        <!-- Tanggal Laporan -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Tanggal Laporan</label>
            <x-pos.form.input 
                type="date" 
                model="selectedDate" 
                :live="true" 
                rounded="rounded-lg" 
            />
        </div>

        <!-- Filter Cabang -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Filter Cabang</label>
            <x-pos.form.select model="selectedBranchId" :live="true" rounded="rounded-lg" icon="ph-storefront">
                <option value="">Semua Cabang</option>
                @foreach($this->branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Sales Rep -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Sales Rep</label>
            <x-pos.form.select model="selectedUserId" :live="true" rounded="rounded-lg" icon="ph-user">
                <option value="">Semua Sales</option>
                @foreach($this->salesUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Status Melayani -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Status Melayani</label>
            <x-pos.form.select model="selectedStatus" :live="true" rounded="rounded-lg" icon="ph-squares-four">
                <option value="all">Semua Status</option>
                <option value="completed">Selesai</option>
                <option value="serving">Sedang Melayani</option>
                <option value="cancelled">Dibatalkan</option>
            </x-pos.form.select>
        </div>

        <!-- Tombol Aksi: Preset & Reset -->
        <div class="flex items-end gap-1.5">
            <x-pos.utility.button 
                variant="{{ $selectedDate === now()->format('Y-m-d') ? 'primary' : 'secondary' }}" 
                size="sm" 
                class="flex-1 !py-2.5 !rounded-lg"
                wire:click="setQuickDate('today')"
                title="Pilih Hari Ini"
            >
                Hari Ini
            </x-pos.utility.button>

            <x-pos.utility.button 
                variant="danger" 
                size="sm"
                icon="ph-arrow-counter-clockwise" 
                class="!py-2.5 !rounded-lg" 
                wire:click="resetFilters" 
                title="Reset Semua Filter"
            >
                Reset
            </x-pos.utility.button>
        </div>

    </div>
</div>
