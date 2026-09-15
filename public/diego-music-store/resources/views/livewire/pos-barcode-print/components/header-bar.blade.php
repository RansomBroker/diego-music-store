<!-- Page Header (Title & Breadcrumbs) -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
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
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Cetak Barcode</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Cetak Barcode Produk</h1>
    </div>

    <!-- Action Buttons Header -->
    <div class="flex items-center gap-3">
        <button
            wire:click="addAllProducts"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-bold rounded-xl transition cursor-pointer"
        >
            <i class="ph-bold ph-squares-four text-base text-primary dark:text-blue-400"></i>
            <span>Tambah Semua Produk</span>
        </button>

        <button
            wire:click="openProductModal"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition cursor-pointer"
        >
            <i class="ph-bold ph-plus-circle text-lg"></i>
            <span>Tambah Produk</span>
        </button>
    </div>
</div>
