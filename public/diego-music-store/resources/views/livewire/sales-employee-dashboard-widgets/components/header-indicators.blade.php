<!-- HEADER SECTION -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
    <div>
        <div class="flex flex-wrap items-center gap-2.5">
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                Performa Sales & Indikator Target
            </h2>
            @if (!empty($isOwner) && !empty($selectedEmployee))
                <span class="px-2.5 py-0.5 bg-blue-100 dark:bg-blue-950/60 border border-blue-300 dark:border-blue-800 text-blue-700 dark:text-blue-300 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                    <i class="ph-bold ph-eye text-xs"></i>
                    <span>Monitoring: {{ $selectedEmployee->name }}</span>
                </span>
            @endif
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Monitoring pencapaian target harian, komisi, tier bonus, & absensi personal
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        @if (!empty($isOwner) && !empty($employeesList) && $employeesList->isNotEmpty())
            <!-- Interactive Employee Selector for Owner -->
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800 p-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 pl-2 flex items-center gap-1">
                    <i class="ph-bold ph-users text-primary"></i>
                    <span>Pilih Staf:</span>
                </span>
                <select 
                    wire:model.live="selectedEmployeeId"
                    class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-extrabold rounded-xl border border-slate-200 dark:border-slate-700 py-1.5 px-3 focus:ring-2 focus:ring-primary focus:outline-none cursor-pointer transition shadow-sm"
                >
                    @foreach ($employeesList as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->name }} ({{ $emp->nik ?: 'NIK -' }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <span class="px-3.5 py-1.5 bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-black flex items-center gap-1.5 shadow-sm">
            <i class="ph-bold ph-chart-line-up text-sm"></i>
            <span>{{ $monthlyTarget['progress_percent'] }}% Target Bulanan</span>
        </span>
    </div>
</div>
