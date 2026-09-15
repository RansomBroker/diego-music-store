<!-- Toolbar (Search & Actions) -->
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
    <!-- Search Input -->
    <div class="relative w-full sm:max-w-xs">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
        </span>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari..."
            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
        >
    </div>

    <!-- Add Action -->
    <x-pos.utility.button
        variant="primary"
        icon="ph-plus"
        wire:click="openCreate"
    >
        Tambah Kategori
    </x-pos.utility.button>
</div>
