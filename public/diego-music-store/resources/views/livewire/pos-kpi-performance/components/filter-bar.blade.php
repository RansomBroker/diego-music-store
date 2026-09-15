<!-- Page Header (Title & Breadcrumbs & Header Actions) -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                        <span class="text-slate-400 dark:text-slate-500">SDM</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                        <span class="text-slate-650 dark:text-slate-300 font-bold">KPI & Insentif</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Page Title -->
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Performance KPI & Insentif</h1>
    </div>

    <!-- Header Actions -->
    <div class="flex flex-wrap items-center gap-2">
        <!-- Kalkulasi Realtime -->
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-arrows-clockwise"
            wire:click="recalculateAllKpi()"
        >
            Kalkulasi Realtime
        </x-pos.utility.button>

        <!-- Tambah Template KPI -->
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-plus-circle"
            wire:click="openTemplateModal()"
        >
            Template KPI Baru
        </x-pos.utility.button>
    </div>
</div>
