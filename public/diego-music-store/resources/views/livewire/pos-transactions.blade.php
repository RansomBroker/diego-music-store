<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Daftar Transaksi"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

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
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Daftar Transaksi</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Daftar Transaksi</h1>
                </div>

                <!-- Table Card Wrapper (Filament Style) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

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

                    <!-- Table List -->
                    <div class="w-full overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider transition-colors">
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">No. Invoice</th>
                                    <th class="px-6 py-4">Cabang</th>
                                    <th class="px-6 py-4">Pelanggan</th>
                                    <th class="px-6 py-4">Kasir</th>
                                    <th class="px-6 py-4">Metode Bayar</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                @forelse ($sales as $sale)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors cursor-pointer" wire:click="showDetails({{ $sale->id }})">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                            {{ $sale->invoice_date->format('d/m/Y') }}
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ $sale->created_at->format('H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-black text-slate-900 dark:text-white">
                                            {{ $sale->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $sale->branch->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $sale->customer->name ?? 'Umum' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            {{ $sale->salesRep->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300">
                                                @if (str_contains(strtolower($sale->payment_method), 'tunai') || strtolower($sale->payment_method) === 'cash')
                                                    <i class="ph ph-money text-emerald-500 text-sm"></i>
                                                @elseif (str_contains(strtolower($sale->payment_method), 'debit'))
                                                    <i class="ph ph-credit-card text-blue-500 text-sm"></i>
                                                @else
                                                    <i class="ph ph-file-text text-amber-500 text-sm"></i>
                                                @endif
                                                {{ $sale->payment_method }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-900 dark:text-white">
                                            Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($sale->status === 'completed')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40 rounded-full text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Selesai
                                                </span>
                                            @elseif ($sale->status === 'draft')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full text-xs font-bold text-slate-600 dark:text-slate-405">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    Draft
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40 rounded-full text-xs font-bold text-rose-700 dark:text-rose-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Dibatalkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center" wire:click.stop>
                                            <div class="flex items-center justify-center gap-2">
                                                <button
                                                    type="button"
                                                    wire:click="showDetails({{ $sale->id }})"
                                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-primary-light dark:bg-slate-800 dark:hover:bg-blue-950/50 text-slate-500 hover:text-primary dark:hover:text-blue-400 flex items-center justify-center transition-colors"
                                                    title="Lihat Detail"
                                                >
                                                    <i class="ph-bold ph-eye text-base"></i>
                                                </button>
                                                @if ($sale->status !== 'cancelled')
                                                    <a
                                                        href="/pos?edit={{ $sale->id }}"
                                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-amber-50 dark:bg-slate-800 dark:hover:bg-amber-900/40 text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 flex items-center justify-center transition-colors"
                                                        title="Edit Transaksi"
                                                    >
                                                        <i class="ph-bold ph-pencil text-base"></i>
                                                    </a>
                                                @endif
                                                @if ($sale->status !== 'cancelled' && $sale->items->sum(fn($item) => $item->quantity - $item->returnItems()->sum('quantity')) > 0)
                                                    <button
                                                        type="button"
                                                        wire:click="startReturn({{ $sale->id }})"
                                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-500 hover:text-rose-600 dark:hover:text-rose-450 flex items-center justify-center transition-colors"
                                                        title="Retur Barang"
                                                    >
                                                        <i class="ph-bold ph-arrow-counter-clockwise text-base"></i>
                                                    </button>
                                                @endif
                                                <button
                                                    type="button"
                                                    wire:click="printReceipt({{ $sale->id }})"
                                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-950/40 text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center justify-center transition-colors"
                                                    title="Cetak Struk"
                                                >
                                                    <i class="ph-bold ph-printer text-base"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium">
                                            <i class="ph ph-receipt text-4xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                                            Tidak ada transaksi ditemukan untuk filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($sales->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                            {{ $sales->links() }}
                        </div>
                    @endif

                </div>

            </div>
        </div>

    </main>

    <!-- Modal Detail Transaksi -->
    @if ($showDetailsModal && $selectedSale)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeDetails" aria-hidden="true"></div>

                <!-- Spacer to center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="relative inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-800">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-light dark:bg-blue-950/50 text-primary dark:text-blue-400 flex items-center justify-center">
                                <i class="ph-bold ph-receipt text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white" id="modal-title">
                                    Detail Transaksi {{ $selectedSale->invoice_number }}
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                    {{ $selectedSale->invoice_date->format('d F Y') }} • {{ $selectedSale->created_at->format('H:i') }} WIB
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeDetails" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto no-scrollbar">
                        <!-- Transaction Metadata -->
                        <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-950/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800/40 text-xs">
                            <div>
                                <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Kasir / Sales Rep</span>
                                <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->salesRep->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</span>
                                <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->customer->name ?? 'Pelanggan Umum' }}</span>
                            </div>
                            <div class="mt-2">
                                <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Metode Pembayaran</span>
                                <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->payment_method }}</span>
                            </div>
                            <div class="mt-2">
                                <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Kategori Penjualan</span>
                                <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->sale_category }}</span>
                            </div>
                        </div>

                        <!-- Product Items Table -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Item Produk</h4>
                            <div class="border border-slate-200 dark:border-slate-850 rounded-xl overflow-hidden">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-850 text-slate-400 font-bold uppercase tracking-wider">
                                            <th class="px-4 py-3">Barang</th>
                                            <th class="px-4 py-3 text-center">Qty</th>
                                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                                            <th class="px-4 py-3 text-right">Diskon</th>
                                            <th class="px-4 py-3 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-150 dark:divide-slate-850 text-slate-700 dark:text-slate-300 font-semibold">
                                        @foreach($selectedSale->items as $item)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="font-black text-slate-850 dark:text-slate-200 block">
                                                        {{ $item->variant->product->name ?? 'Produk Tidak Dikenal' }}
                                                    </span>
                                                    @if($item->variant && $item->variant->name && $item->variant->name !== 'Default')
                                                        <span class="block text-[10px] text-slate-400 dark:text-slate-550 font-medium mt-0.5">Varian: {{ $item->variant->name }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-red-500">
                                                    @if($item->discount_amount > 0)
                                                        -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">
                                                    Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="space-y-2 border-t border-slate-150 dark:border-slate-800 pt-4 text-xs font-semibold">
                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($selectedSale->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($selectedSale->discount_amount > 0)
                                <div class="flex items-center justify-between text-red-500 font-bold">
                                    <span>Diskon Invoice</span>
                                    <span>-Rp {{ number_format($selectedSale->discount_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($selectedSale->tax_amount > 0)
                                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span>Pajak (11%)</span>
                                    <span>Rp {{ number_format($selectedSale->tax_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span>Total Belanja</span>
                                <span class="text-primary dark:text-blue-400">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-850 flex items-center justify-end gap-3">
                        @if ($selectedSale->status !== 'cancelled')
                            <a
                                href="/pos?edit={{ $selectedSale->id }}"
                                class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-black text-white hover:text-white bg-amber-600 hover:bg-amber-700 active:scale-95 rounded-xl shadow-md shadow-amber-600/15 hover:shadow-amber-600/25 transition-all cursor-pointer"
                            >
                                <i class="ph-bold ph-pencil text-sm"></i>
                                <span>Edit Transaksi</span>
                            </a>
                        @endif
                        @if ($selectedSale->status !== 'cancelled' && $selectedSale->items->sum(fn($item) => $item->quantity - $item->returnItems()->sum('quantity')) > 0)
                            <button
                                type="button"
                                wire:click="startReturn({{ $selectedSale->id }})"
                                class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-black text-white hover:text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-md shadow-rose-600/15 hover:shadow-rose-600/25 transition-all cursor-pointer"
                            >
                                <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
                                <span>Retur Barang</span>
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="printReceipt({{ $selectedSale->id }})"
                            class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-black text-white hover:text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-xl shadow-md shadow-emerald-650/15 hover:shadow-emerald-650/25 transition-all cursor-pointer"
                        >
                            <i class="ph-bold ph-printer text-sm"></i>
                            <span>Cetak Struk</span>
                        </button>
                        <button
                            type="button"
                            wire:click="closeDetails"
                            class="px-4 py-2.5 text-xs font-black text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-all"
                        >
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- Return Transaction Modal -->
    @if ($showReturnModal && $returnSale)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="cancelReturn"></div>

                <!-- Centering helper -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100 dark:border-slate-800">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-955/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                <i class="ph-bold ph-arrow-counter-clockwise text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white" id="modal-title">
                                    Retur Penjualan {{ $returnSale->invoice_number }}
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                    {{ $returnSale->invoice_date->format('d F Y') }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="cancelReturn" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto no-scrollbar">
                        <!-- Alert Box -->
                        <div class="p-3 bg-amber-50 dark:bg-amber-955/10 border border-amber-250 dark:border-amber-900/60 rounded-xl flex gap-3 text-amber-800 dark:text-amber-400">
                            <i class="ph-fill ph-info text-lg mt-0.5"></i>
                            <div class="text-xs">
                                <span class="font-extrabold block">Penting sebelum memproses retur:</span>
                                <ul class="list-disc pl-4 mt-1 space-y-0.5 font-medium text-slate-600 dark:text-slate-400">
                                    <li>Kuantitas retur tidak boleh melebihi sisa barang yang dapat diretur.</li>
                                    <li>Refund dihitung berdasarkan harga bersih setelah diskon dibagi kuantitas barang saat pembelian.</li>
                                    <li>Proses ini akan mengembalikan stok fisik dan memotong kas pada sesi kasir aktif saat ini.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Return Items List -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Pilih Item untuk Diretur</h4>
                            <div class="border border-slate-200 dark:border-slate-850 rounded-xl overflow-hidden">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-850 text-slate-400 font-bold uppercase tracking-wider">
                                            <th class="px-4 py-3">Barang</th>
                                            <th class="px-4 py-3 text-center w-28">Beli / Diretur</th>
                                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                                            <th class="px-4 py-3 text-center w-36">Qty Retur</th>
                                            <th class="px-4 py-3 text-right w-36">Refund Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-150 dark:divide-slate-850 text-slate-700 dark:text-slate-300 font-semibold">
                                        @foreach($returnItems as $itemId => $item)
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20">
                                                <td class="px-4 py-3">
                                                    <span class="font-black text-slate-850 dark:text-slate-200 block">
                                                        {{ $item['name'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-slate-500">
                                                    {{ $item['original_qty'] }} / {{ $item['returned_qty'] }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-slate-500">
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    <div class="flex items-center justify-center gap-1 bg-slate-100 dark:bg-slate-950 border border-slate-250 dark:border-slate-850 rounded-lg p-0.5 w-fit mx-auto">
                                                        <button 
                                                            type="button" 
                                                            wire:click="$set('returnItems.{{ $itemId }}.qty', {{ max(0, intval($item['qty'] ?? 0) - 1) }})" 
                                                            class="w-6 h-6 rounded bg-white dark:bg-slate-800 shadow border border-slate-200 dark:border-slate-750 flex items-center justify-center text-slate-755 dark:text-slate-300 hover:text-rose-600 transition-colors"
                                                        >
                                                            <i class="ph-bold ph-minus text-[10px]"></i>
                                                        </button>
                                                        <input 
                                                            type="number" 
                                                            wire:model.live="returnItems.{{ $itemId }}.qty" 
                                                            min="0" 
                                                            max="{{ $item['max'] }}" 
                                                            class="w-10 bg-transparent text-center border-none p-0 text-xs font-black text-slate-900 dark:text-white focus:ring-0 appearance-none h-5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                        >
                                                        <button 
                                                            type="button" 
                                                            wire:click="$set('returnItems.{{ $itemId }}.qty', {{ min(intval($item['max']), intval($item['qty'] ?? 0) + 1) }})" 
                                                            class="w-6 h-6 rounded bg-white dark:bg-slate-800 shadow border border-slate-200 dark:border-slate-750 flex items-center justify-center text-slate-755 dark:text-slate-300 hover:text-rose-600 transition-colors"
                                                        >
                                                            <i class="ph-bold ph-plus text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400">
                                                    Rp {{ number_format(intval($item['qty'] ?? 0) * $item['refund_per_unit'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Return Reason & Refund Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Alasan Retur</label>
                                <textarea 
                                    wire:model="returnReason" 
                                    placeholder="Masukkan alasan pengembalian barang..." 
                                    rows="3" 
                                    class="w-full px-3 py-2 bg-slate-550 dark:bg-slate-950 border border-slate-250 dark:border-slate-850 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-550 focus:border-rose-500 focus:ring-rose-500 outline-none transition-colors"
                                ></textarea>
                            </div>
                            <div class="bg-slate-50/50 dark:bg-slate-950/40 p-4.5 rounded-xl border border-slate-100 dark:border-slate-800/40 flex flex-col justify-between">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                                    <span>Total Barang Diretur</span>
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ collect($returnItems)->sum('qty') }} unit
                                    </span>
                                </div>
                                <div class="w-full border-t border-dashed border-slate-200 dark:border-slate-800 my-3"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-slate-700 dark:text-slate-350">Estimasi Total Refund</span>
                                    <span class="text-xl font-black text-rose-600 dark:text-rose-450">
                                        Rp {{ number_format(collect($returnItems)->sum(fn($i) => intval($i['qty'] ?? 0) * intval($i['refund_per_unit'])), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-850 flex items-center justify-end gap-3 font-semibold">
                        <button
                            type="button"
                            wire:click="processReturn"
                            class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-black text-white hover:text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-md shadow-rose-600/15 hover:shadow-rose-600/25 transition-all cursor-pointer"
                        >
                            <i class="ph-bold ph-check text-sm"></i>
                            <span>Proses Retur</span>
                        </button>
                        <button
                            type="button"
                            wire:click="cancelReturn"
                            class="px-4 py-2.5 text-xs font-black text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-250 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-all"
                        >
                            Batal
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
