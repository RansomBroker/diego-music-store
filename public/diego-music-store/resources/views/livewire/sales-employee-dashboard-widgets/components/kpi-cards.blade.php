<!-- GRID ROW 1: Target Bulanan (1), Target Harian (2), Komisi (3), Unlock Tier (4) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Pantau Target Penjualan Bulanan -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Target Penjualan Bulanan</span>
            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="ph-bold ph-target text-lg"></i>
            </div>
        </div>
        <div>
            <div class="text-lg font-black text-slate-900 dark:text-white ">
                Rp {{ number_format($monthlyTarget['achieved_amount'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                Target: <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($monthlyTarget['target_amount'], 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="space-y-1 pt-1">
            <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                <span>Pencapaian</span>
                <span class="text-blue-600 dark:text-blue-400">{{ $monthlyTarget['progress_percent'] }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ $monthlyTarget['progress_percent'] }}%;"></div>
            </div>
        </div>
    </div>

    <!-- 2. Pantau Target Penjualan Harian -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Target Penjualan Harian</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="ph-bold ph-calendar-check text-lg"></i>
            </div>
        </div>
        <div>
            <div class="text-lg font-black text-slate-900 dark:text-white ">
                Rp {{ number_format($dailyTarget['achieved_amount'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                Target Hari Ini: <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($dailyTarget['target_amount'], 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="space-y-1 pt-1">
            <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                <span>Status: <strong class="text-emerald-600 dark:text-emerald-400">{{ $dailyTarget['status'] }}</strong></span>
                <span class="text-emerald-600 dark:text-emerald-400">{{ $dailyTarget['progress_percent'] }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500" style="width: {{ $dailyTarget['progress_percent'] }}%;"></div>
            </div>
        </div>
    </div>

    <!-- 3. Komisi Penjualan Bulan Ini -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Komisi Penjualan Bulan Ini</span>
            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i class="ph-bold ph-coins text-lg"></i>
            </div>
        </div>
        <div>
            <div class="text-xl font-black text-amber-600 dark:text-amber-400  mt-1">
                Rp {{ number_format($monthlyCommission, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Tier Aktif: <strong class="text-amber-600 dark:text-amber-300 font-extrabold">{{ $tierInfo['current_tier'] }} ({{ $tierInfo['current_rate'] }}%)</strong>
            </div>
        </div>
        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center gap-1">
            <i class="ph-bold ph-check-circle"></i> Berdasarkan Transaksi Lunas
        </div>
    </div>

    <!-- 4. Sisa Target Unlock Tier Komisi -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Unlock Tier Komisi Next</span>
            <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <i class="ph-bold ph-lock-key-open text-lg"></i>
            </div>
        </div>
        <div>
            <div class="text-xs font-extrabold text-purple-700 dark:text-purple-300">
                Target {{ $tierInfo['next_tier'] }}
            </div>
            <div class="text-sm font-black text-slate-900 dark:text-white  mt-0.5">
                Sisa: Rp {{ number_format($tierInfo['remaining_to_next'], 0, ',', '.') }}
            </div>
        </div>
        <div class="space-y-1 pt-1">
            <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                <span>Progress Tier</span>
                <span class="text-purple-600 dark:text-purple-400">{{ $tierInfo['tier_progress'] }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $tierInfo['tier_progress'] }}%;"></div>
            </div>
        </div>
    </div>
</div>
