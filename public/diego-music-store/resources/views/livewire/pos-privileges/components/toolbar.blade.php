<!-- Page Header (Title & Breadcrumbs) -->
<div>
    <!-- Breadcrumbs -->
    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                    <span class="text-slate-400 dark:text-slate-500">Utility</span>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                    <span class="text-slate-650 dark:text-slate-300 font-bold">Privilege User</span>
                </div>
            </li>
        </ol>
    </nav>
    <!-- Page Title -->
    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Setting Privilege User</h1>
</div>

<!-- Toolbar (Search & Actions) Card Header -->
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
    <!-- Search Input -->
    <div class="relative w-full sm:max-w-xs">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
        </span>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari role..."
            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
        >
    </div>

    <!-- Add Action -->
    <button
        wire:click="openCreate"
        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
    >
        <i class="ph-bold ph-plus text-sm"></i>
        <span>Tambah Role Baru</span>
    </button>
</div>
