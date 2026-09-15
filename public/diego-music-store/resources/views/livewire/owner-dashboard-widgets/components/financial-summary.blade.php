<!-- 1. RINGKASAN KEUANGAN CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Penjualan -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-emerald-500/50 transition">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Total Penjualan</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="ph-bold ph-trend-up text-lg"></i>
            </div>
        </div>
        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400  mt-2">
            Rp {{ number_format($financialSummary['total_penjualan'], 0, ',', '.') }}
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            Est. Laba Kotor: <strong class="text-slate-800 dark:text-white">Rp {{ number_format($financialSummary['laba_kotor_est'], 0, ',', '.') }}</strong>
        </div>
    </div>

    <!-- Total Pengeluaran -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-rose-500/50 transition">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Total Pengeluaran Kas</span>
            <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <i class="ph-bold ph-receipt text-lg"></i>
            </div>
        </div>
        <div class="text-xl font-black text-rose-600 dark:text-rose-400  mt-2">
            Rp {{ number_format($financialSummary['total_pengeluaran'], 0, ',', '.') }}
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            Beban Operasional & Kas Keluar
        </div>
    </div>

    <!-- Total Hutang Supplier -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-amber-500/50 transition">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Hutang Supplier (Kredit)</span>
            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i class="ph-bold ph-hand-coins text-lg"></i>
            </div>
        </div>
        <div class="text-xl font-black text-amber-600 dark:text-amber-400  mt-2">
            Rp {{ number_format($financialSummary['total_hutang'], 0, ',', '.') }}
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            Sisa Belum Lunas Supplier
        </div>
    </div>

    <!-- Saldo Kas & Bank -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-blue-500/50 transition">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
            <span>Saldo Kas & Bank</span>
            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="ph-bold ph-vault text-lg"></i>
            </div>
        </div>
        <div class="text-xl font-black text-blue-600 dark:text-blue-400  mt-2">
            Rp {{ number_format($financialSummary['saldo_kas_bank'], 0, ',', '.') }}
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            Akumulasi Kas Toko & Rekening
        </div>
    </div>
</div>
