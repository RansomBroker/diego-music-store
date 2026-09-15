<!-- Toolbar (Filters & Search) -->
<div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Search Input -->
        <div class="relative lg:col-span-2">
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cari Transaksi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="No. Invoice, pelanggan, kasir..."
                    class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
            </div>
        </div>

        <!-- Branch Filter -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cabang</label>
            <select
                wire:model.live="selectedBranchId"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
            <select
                wire:model.live="selectedStatus"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="all">Semua Status</option>
                <option value="completed">Selesai</option>
                <option value="pending">Belum Selesai (Piutang)</option>
                <option value="draft">Draft</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>

        <!-- Payment Method Filter -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Metode Bayar</label>
            <select
                wire:model.live="selectedPaymentMethod"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="all">Semua Metode</option>
                <option value="Tunai">Tunai / Cash</option>
                <option value="Debit">Debit BCA</option>
                <option value="Piutang">Piutang</option>
            </select>
        </div>

        <!-- From Date -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
            <input
                type="date"
                wire:model.live="fromDate"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
        </div>

    </div>

    <div class="flex items-center justify-between pt-2 border-t border-slate-150 dark:border-slate-800">
        <!-- To Date -->
        <div class="flex items-center gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                <input
                    type="date"
                    wire:model.live="toDate"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
            </div>
        </div>

        <!-- Reset Button -->
        <div class="self-end">
            <button
                type="button"
                wire:click="resetFilters"
                class="flex items-center gap-2 px-4 py-2 text-xs font-black text-slate-500 hover:text-red-650 bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-950/20 border border-transparent hover:border-red-200 dark:hover:border-red-900/30 rounded-lg transition-all"
            >
                <i class="ph-bold ph-arrows-counter-clockwise"></i>
                <span>Reset Filter</span>
            </button>
        </div>
    </div>
</div>
