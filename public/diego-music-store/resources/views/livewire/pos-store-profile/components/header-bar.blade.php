<!-- Page Header (Title & Breadcrumbs) -->
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
                        <span class="text-slate-400 dark:text-slate-500">Utility</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Register Nama Toko</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Register & Profil Toko</h1>
    </div>

    <button
        wire:click="openCreateStore"
        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer self-start sm:self-auto"
    >
        <i class="ph-bold ph-plus text-sm"></i>
        <span>Register Toko Baru</span>
    </button>
</div>
