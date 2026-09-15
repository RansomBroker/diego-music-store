<!-- Page Header (Title & Breadcrumbs) -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Kas Harian</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Page Title -->
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Kas Harian (Petty Cash)</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat dan pantau transaksi kas masuk & keluar di luar penjualan toko.</p>
    </div>

    @if($activeSession)
        <div class="flex items-center gap-3">
            <button
                wire:click="openInModal"
                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 duration-200 cursor-pointer"
            >
                <i class="ph ph-arrow-up-right text-base"></i>
                Catat Kas Masuk
            </button>
            <button
                wire:click="openOutModal"
                class="flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-bold rounded-xl shadow-md shadow-rose-500/20 transition-all transform hover:-translate-y-0.5 duration-200 cursor-pointer"
            >
                <i class="ph ph-arrow-down-left text-base"></i>
                Catat Kas Keluar
            </button>
        </div>
    @endif
</div>
