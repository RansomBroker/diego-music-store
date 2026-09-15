<!-- Tabs Sub-navigation -->
<div class="flex border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition-colors flex-shrink-0 px-8">
    <button wire:click="$set('activeTab', 'sesi')" class="py-4 px-6 font-bold text-sm border-b-2 transition-all outline-none focus:outline-none cursor-pointer {{ $activeTab === 'sesi' ? 'border-primary text-primary dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300' }}">
        Sesi Kasir Saat Ini
    </button>
    <button wire:click="$set('activeTab', 'riwayat')" class="py-4 px-6 font-bold text-sm border-b-2 transition-all outline-none focus:outline-none cursor-pointer {{ $activeTab === 'riwayat' ? 'border-primary text-primary dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300' }}">
        Riwayat Sesi Shift
    </button>
</div>
