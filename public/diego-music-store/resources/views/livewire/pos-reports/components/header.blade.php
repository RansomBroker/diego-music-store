<!-- Header & Navigation Bar -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Laporan ERP</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pusat Laporan ERP & Akuntansi</h1>
    </div>

    <!-- Print / Export Action Button -->
    <div class="flex items-center gap-2">
        <button
            onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow transition cursor-pointer"
        >
            <i class="ph-bold ph-printer text-base text-blue-400"></i>
            <span>Cetak Laporan</span>
        </button>
    </div>
</div>
