<!-- 5 Tab Executive Navigation Bar -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1.5 shadow-sm overflow-x-auto no-scrollbar">
    <div class="flex items-center min-w-max gap-1">
        <button
            wire:click="setTab('sales')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'sales' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
        >
            <i class="ph-bold ph-chart-line-up text-base"></i>
            <span>1. Laporan Penjualan</span>
        </button>

        <button
            wire:click="setTab('ar-aging')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'ar-aging' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
        >
            <i class="ph-bold ph-credit-card text-base"></i>
            <span>2. Laporan Piutang</span>
        </button>

        <button
            wire:click="setTab('ar-settlement')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'ar-settlement' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
        >
            <i class="ph-bold ph-hand-coins text-base"></i>
            <span>3. Pelunasan Piutang</span>
        </button>

        <button
            wire:click="setTab('daily-cash')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'daily-cash' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
        >
            <i class="ph-bold ph-wallet text-base"></i>
            <span>4. Kas Harian</span>
        </button>

        <button
            wire:click="setTab('stock-prices')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'stock-prices' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
        >
            <i class="ph-bold ph-package text-base"></i>
            <span>5. Stok & Harga</span>
        </button>
    </div>
</div>
