<!-- Tabs Navigation -->
<div class="flex border-b border-slate-200 dark:border-slate-800 space-x-4">
    <button
        type="button"
        wire:click="$set('activeTab', 'recap')"
        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'recap' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
    >
        <i class="ph-bold ph-list-checks mr-1.5"></i>
        1. Rekap Evaluasi Bulanan
    </button>
    <button
        type="button"
        wire:click="$set('activeTab', 'dashboard')"
        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'dashboard' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
    >
        <i class="ph-bold ph-gauge mr-1.5"></i>
        2. Dashboard Realtime KPI Saya
    </button>
    <button
        type="button"
        wire:click="$set('activeTab', 'templates')"
        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'templates' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
    >
        <i class="ph-bold ph-sliders-horizontal mr-1.5"></i>
        3. Template KPI per Jabatan/User
    </button>
</div>
