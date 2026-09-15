<!-- Summary Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- Card 1: Modal Awal -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
            <i class="ph ph-vault text-xl"></i>
        </div>
        <div>
            <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Modal Awal Sesi</span>
            <span class="text-base font-bold text-slate-800 dark:text-slate-100">
                Rp {{ number_format($openingCash, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 2: Penjualan Tunai -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
        <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
            <i class="ph ph-tag text-xl"></i>
        </div>
        <div>
            <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Penjualan Tunai</span>
            <span class="text-base font-bold text-slate-800 dark:text-slate-100">
                Rp {{ number_format($cashSales, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 3: Kas Masuk -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
            <i class="ph ph-arrow-circle-up-right text-xl"></i>
        </div>
        <div>
            <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Kas Masuk</span>
            <span class="text-base font-bold text-emerald-600 dark:text-emerald-450">
                +Rp {{ number_format($cashIn, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 4: Kas Keluar -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
        <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
            <i class="ph ph-arrow-circle-down-left text-xl"></i>
        </div>
        <div>
            <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Kas Keluar</span>
            <span class="text-base font-bold text-rose-650 dark:text-rose-400">
                -Rp {{ number_format($cashOut, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Card 5: Kas Laci (Net) -->
    <div class="bg-gradient-to-br from-primary-dark to-primary dark:from-blue-900 dark:to-blue-800 text-white rounded-xl p-4 flex items-center gap-4 shadow-md shadow-blue-500/10">
        <div class="w-10 h-10 rounded-lg bg-white/10 text-white flex items-center justify-center flex-shrink-0">
            <i class="ph ph-coins text-xl"></i>
        </div>
        <div>
            <span class="block text-[10px] font-black text-white/70 uppercase tracking-wider">Uang Laci (Teoritis)</span>
            <span class="text-base font-black">
                Rp {{ number_format($expectedCash, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>
