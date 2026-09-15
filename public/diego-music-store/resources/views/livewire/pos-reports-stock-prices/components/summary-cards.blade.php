<!-- KPI Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total SKU / Varian</span>
        <div class="text-xl font-black text-slate-900 dark:text-white mt-1 ">
            {{ number_format($reportData['total_variants'] ?? 0, 0, ',', '.') }} Item
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">Total Fisik: {{ number_format($reportData['total_physical_qty'] ?? 0, 0, ',', '.') }} pcs</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Status Stok Kritis</span>
        <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1 ">
            {{ number_format($reportData['total_out_of_stock_count'] ?? 0, 0, ',', '.') }} Habis
        </div>
        <span class="text-[11px] text-amber-500 font-bold mt-1 block">{{ number_format($reportData['total_low_stock_count'] ?? 0, 0, ',', '.') }} Stok Rendah</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Nilai Aset (HPP)</span>
        <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 ">
            Rp {{ number_format($reportData['grand_total_valuation'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">HPP Inventory Valuation</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Potensi Nilai Jual</span>
        <div class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1 ">
            Rp {{ number_format($reportData['grand_total_retail_value'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] text-purple-500 font-bold mt-1 block">Retail Sales Value</span>
    </div>
</div>
