<!-- Navigation Tabs -->
<div class="flex border-b border-slate-200 dark:border-slate-700/80 overflow-x-auto no-scrollbar">
    <button
        type="button"
        wire:click="$set('activeTab', 'recap')"
        class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'recap' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
    >
        <i class="ph-bold ph-chart-pie-slice text-base"></i>
        <span>1. Rekap Potongan Presensi</span>
    </button>

    <button
        type="button"
        wire:click="$set('activeTab', 'rules')"
        class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'rules' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
    >
        <i class="ph-bold ph-gear text-base"></i>
        <span>2. Aturan & Denda Pelanggaran</span>
    </button>

    <button
        type="button"
        wire:click="$set('activeTab', 'logs')"
        class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'logs' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
    >
        <i class="ph-bold ph-list-numbers text-base"></i>
        <span>3. Log Detail Pelanggaran</span>
    </button>
</div>
