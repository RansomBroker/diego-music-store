<!-- Top Action & Filter Header -->
<div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/70 dark:border-slate-700/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-xl">
                <i class="ph-bold ph-warning-circle"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Potongan & Denda Presensi</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500">Aturan keterlambatan, pulang cepat, log denda presensi & sinkronisasi ke Payroll</p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
        <!-- Filter Cabang -->
        <div class="w-full sm:w-44">
            <x-pos.form.dropdown
                model="filterBranchId"
                :live="true"
                icon="ph-buildings"
                size="sm"
                placeholder="Semua Cabang"
            >
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.dropdown>
        </div>

        <!-- Filter Bulan/Periode -->
        <div class="w-full sm:w-40">
            <x-pos.form.input
                type="month"
                model="filterMonth"
                :live="true"
                icon="ph-calendar"
                size="sm"
            />
        </div>

        <!-- Button Export CSV -->
        <x-pos.utility.button
            type="button"
            variant="success"
            size="sm"
            icon="ph-download-simple"
            wire:click="exportCsv()"
        >
            Export Rekap CSV
        </x-pos.utility.button>

        <!-- Button Tambah Aturan -->
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-plus-circle"
            wire:click="openRuleModal()"
        >
            Tambah Aturan Denda
        </x-pos.utility.button>
    </div>
</div>
