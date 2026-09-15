<!-- Executive KPI Cards Grid (Laba Rugi, Stok, Pelanggan, Omset) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    <!-- 1. Omset Penjualan -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-blue-500/50 transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Omset Penjualan</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="ph-bold ph-trend-up text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
                {{ number_format($totalSalesCount) }} transaksi berhasil
            </p>
        </div>
    </div>

    <!-- 2. Nilai Persediaan Stok Cabang -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Persediaan</span>
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="ph-bold ph-package text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalStockValue, 0, ',', '.') }}
            </div>
            <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                {{ number_format($totalStockItems) }} unit barang tersedia
            </p>
        </div>
    </div>

    <!-- 3. Pelanggan & Piutang AR -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-purple-500/50 transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Pelanggan & Piutang</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <i class="ph-bold ph-users text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalArUnpaid, 0, ',', '.') }}
            </div>
            <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 mt-1">
                {{ number_format($totalCustomers) }} pelanggan terdaftar
            </p>
        </div>
    </div>

    <!-- 4. Estimasi Laba Bersih Operasional -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-amber-500/50 transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Laba Bersih Cabang</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i class="ph-bold ph-scales text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-2xl font-black {{ $netOperatingIncome >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                Rp {{ number_format($netOperatingIncome, 0, ',', '.') }}
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
                Laba Kotor: Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
