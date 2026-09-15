<!-- Sales KPI Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Card 1: Total Omzet Penjualan -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-sm font-semibold text-slate-400 uppercase">Total Omzet Penjualan</span>
        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
            Rp {{ number_format($summaryData['grand_total'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">
            {{ number_format($summaryData['total_transactions'] ?? 0, 0, ',', '.') }} Transaksi Selesai
        </span>
    </div>

    <!-- Card 2: Total HPP Barang -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-sm font-semibold text-slate-400 uppercase">Total HPP Barang</span>
        <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
            Rp {{ number_format($summaryData['total_cogs'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">
            Harga Pokok Penjualan
        </span>
    </div>

    <!-- Card 3: Laba Kotor (Gross Profit) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-sm font-semibold text-slate-400 uppercase">Laba Kotor (Gross Profit)</span>
        <div class="text-xl font-black text-primary dark:text-blue-400 mt-1">
            Rp {{ number_format($summaryData['gross_profit'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
            Margin: {{ $summaryData['profit_margin'] ?? 0 }}%
        </span>
    </div>

    <!-- Card 4: Diskon & Pajak -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-sm font-semibold text-slate-400 uppercase">Diskon & Pajak</span>
        <div class="text-sm font-bold text-slate-700 dark:text-slate-200 mt-1 space-y-0.5 ">
            <div>Disc: <span class="text-rose-600">Rp {{ number_format($summaryData['total_discount'] ?? 0, 0, ',', '.') }}</span></div>
            <div>Pajak: <span class="text-blue-600">Rp {{ number_format($summaryData['total_tax'] ?? 0, 0, ',', '.') }}</span></div>
        </div>
    </div>
</div>
