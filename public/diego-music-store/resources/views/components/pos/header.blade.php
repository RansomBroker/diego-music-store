@props(['branches', 'selectedBranchId', 'selectedStoreName', 'selectedBranchName'])

<header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 z-10 transition-colors">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $selectedStoreName }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Pilih produk atau jasa reparasi</p>
    </div>
    
    <div class="flex items-center gap-4">
        <!-- Branch Selector -->
        <div class="relative">
            <select wire:model.live="selectedBranchId" disabled class="pl-3 pr-8 py-2 bg-slate-100 dark:bg-slate-700 border-none rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 outline-none cursor-not-allowed opacity-80" title="Cabang terkunci oleh sesi kasir aktif. Tutup sesi untuk mengganti cabang.">
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search Bar -->
        <div class="relative hidden sm:block">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari menu, SKU atau barcode..." class="pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-transparent focus:bg-white dark:focus:bg-slate-600 focus:border-primary focus:ring-2 focus:ring-primaryLight rounded-xl w-64 transition-all outline-none text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500">
        </div>
        
        <!-- Profile / Active User -->
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-650 overflow-hidden border-2 border-white dark:border-slate-700 shadow-sm cursor-pointer">
                <img src="https://placehold.co/100x100/3b82f6/ffffff?text={{ substr(auth()->user()->name ?? 'AD', 0, 2) }}" alt="Admin Profile" class="w-full h-full object-cover">
            </div>
            <div class="hidden lg:block text-left">
                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 uppercase">{{ auth()->user()->roles()->first()?->name ?? 'Kasir' }}</div>
            </div>
        </div>
    </div>
</header>
