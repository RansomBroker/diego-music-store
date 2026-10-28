<!-- Common Filter Toolbar Card (Expandable / Collapsible) -->
<div x-data="{ isExpanded: true }" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs transition-all duration-200 overflow-hidden">
    
    <!-- Filter Header Bar (Clickable to Collapse / Expand) -->
    <div @click="isExpanded = !isExpanded" class="p-4 px-4.5 flex items-center justify-between cursor-pointer select-none bg-slate-50/50 dark:bg-slate-800/20 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="ph ph-funnel text-base font-bold"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    Filter Laporan & Parameter
                </h3>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Klik untuk menyembunyikan / menampilkan opsi filter
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span x-show="!isExpanded" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                Filter Sembunyi
            </span>
            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-200/50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                <i class="ph text-base transition-transform duration-200" :class="isExpanded ? 'ph-caret-up' : 'ph-caret-down'"></i>
            </button>
        </div>
    </div>

    <!-- Filter Form Content Body (Collapsible) -->
    <div x-show="isExpanded" x-collapse class="p-4.5 border-t border-slate-100 dark:border-slate-800 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Col 1: Tanggal Laporan -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Tanggal Laporan</label>
                <x-pos.form.input type="date" model="selectedDate" :live="true" size="sm" />
            </div>

            <!-- Col 2: Quick Presets Tanggal -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Preset Tanggal</label>
                <div class="flex items-center gap-2">
                    <x-pos.utility.button 
                        variant="{{ $selectedDate === now()->format('Y-m-d') ? 'primary' : 'secondary' }}" 
                        size="sm" 
                        class="flex-1"
                        wire:click="setQuickDate('today')"
                    >
                        Hari Ini
                    </x-pos.utility.button>
                    <x-pos.utility.button 
                        variant="{{ $selectedDate === now()->subDay()->format('Y-m-d') ? 'primary' : 'secondary' }}" 
                        size="sm" 
                        class="flex-1"
                        wire:click="setQuickDate('yesterday')"
                    >
                        Kemarin
                    </x-pos.utility.button>
                </div>
            </div>

            <!-- Col 3: Filter Sales Rep -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Sales Rep</label>
                <x-pos.form.select model="selectedUserId" :live="true" size="sm" icon="ph-user">
                    <option value="">Semua Sales</option>
                    @foreach ($this->salesUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 4: Status Melayani -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Melayani</label>
                <x-pos.form.select model="selectedStatus" :live="true" size="sm" icon="ph-clock">
                    <option value="all">Semua Status</option>
                    <option value="completed">Selesai (Completed)</option>
                    <option value="serving">Sedang Melayani (Serving)</option>
                    <option value="cancelled">Dibatalkan</option>
                </x-pos.form.select>
            </div>
        </div>

        <!-- Search Bar Row -->
        <div>
            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Cepat</label>
            <x-pos.form.input 
                type="text" 
                model="search" 
                :live="true" 
                debounce="300ms" 
                size="sm" 
                icon="ph-magnifying-glass" 
                placeholder="Cari nama sales atau nomor faktur penjualan..." 
            />
        </div>
    </div>
</div>
