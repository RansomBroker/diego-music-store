<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Pelunasan Hutang"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

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
                                        <i class="ph ph-caret-right text-[10px] text-slate-355 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Pelunasan Hutang</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <!-- Page Title -->
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pelunasan Hutang</h1>
                    </div>

                    <!-- Add Action -->
                    <button
                        wire:click="openCreate"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                    >
                        <i class="ph-bold ph-plus text-sm"></i>
                        <span>Bayar Hutang</span>
                    </button>
                </div>

                <!-- Filters & Table Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    
                    <!-- Toolbar (Search & Filter Status) -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
                        <!-- Search Input -->
                        <div class="relative w-full sm:max-w-xs">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Cari No. Pembayaran / Supplier..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            >
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status:</label>
                            <select
                                wire:model.live="statusFilter"
                                class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-750 dark:text-slate-250 py-1.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition duration-150"
                            >
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="posted">Posted (Selesai)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th sortable field="payment_no" :sortField="$sortField" :sortDirection="$sortDirection">
                                        No. Pembayaran
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="payment_date" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Tanggal
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Supplier
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Metode Bayar
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Akun Kas/Bank
                                    </x-pos.table.th>
                                    <x-pos.table.th class="text-right" sortable field="total_amount" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Total Jumlah
                                    </x-pos.table.th>
                                    <x-pos.table.th class="text-center">
                                        Status
                                    </x-pos.table.th>
                                    <x-pos.table.th class="text-right">
                                        Aksi
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($payments as $payment)
                                    <x-pos.table.tr>
                                        <x-pos.table.td class="whitespace-nowrap font-mono font-medium text-slate-900 dark:text-slate-100">
                                            {{ $payment->payment_no }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                            {{ $payment->payment_date->format('d/m/Y') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $payment->supplier->name }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm">
                                            {{ $payment->payment_method }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                            {{ $payment->account->name }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap font-bold text-right text-slate-900 dark:text-slate-100">
                                            Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-center">
                                            @if ($payment->status === 'draft')
                                                <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-350 text-xs font-bold rounded-full border border-amber-200/50 dark:border-amber-850/30">
                                                    Draft
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30">
                                                    Posted
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Detail -->
                                                <button
                                                    wire:click="showDetails({{ $payment->id }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors cursor-pointer"
                                                    title="Lihat Detail"
                                                >
                                                    <i class="ph ph-eye text-base"></i>
                                                </button>

                                                @if ($payment->status === 'draft')
                                                    <!-- Posting -->
                                                    <button
                                                        wire:click="confirmPost({{ $payment->id }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition-colors cursor-pointer"
                                                        title="Posting Pelunasan"
                                                    >
                                                        <i class="ph ph-check-square text-base"></i>
                                                    </button>

                                                    <!-- Hapus -->
                                                    <button
                                                        wire:click="confirmDelete({{ $payment->id }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-455 transition-colors cursor-pointer"
                                                        title="Hapus Draft"
                                                    >
                                                        <i class="ph ph-trash text-base"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="8" icon="ph-credit-card" message="Belum ada riwayat pelunasan hutang." />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Pagination -->
                    @if ($payments->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                                <div>
                                    Menampilkan
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->firstItem() }}</span>
                                    sampai
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->total() }}</span>
                                    hasil
                                </div>
                                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-xs">Per halaman:</label>
                                    <select
                                        wire:model.live="perPage"
                                        class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-750 dark:text-slate-250 py-1 px-2 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition duration-150"
                                    >
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            @if ($payments->hasPages())
                                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                                    @if ($payments->onFirstPage())
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </span>
                                    @else
                                        <button
                                            wire:click="previousPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </button>
                                    @endif

                                    @foreach ($payments->getUrlRange(max(1, $payments->currentPage() - 2), min($payments->lastPage(), $payments->currentPage() + 2)) as $page => $url)
                                        @if ($page == $payments->currentPage())
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <button
                                                wire:click="gotoPage({{ $page }})"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                                            >
                                                {{ $page }}
                                            </button>
                                        @endif
                                    @endforeach

                                    @if ($payments->hasMorePages())
                                        <button
                                            wire:click="nextPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-right text-sm"></i>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                                            <i class="ph-bold ph-caret-right text-sm"></i>
                                        </span>
                                    @endif
                                </nav>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

    {{-- ===================== MODAL: BAYAR HUTANG (CREATE) ===================== --}}
    <x-pos.modal
        wire:model="showCreateModal"
        title="Input Pelunasan Hutang"
        subtitle="Buat transaksi pelunasan hutang pembelian ke supplier"
        icon="ph-plus"
        maxWidth="5xl"
    >
        <div class="space-y-6">
            <!-- Informasi Utama -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- No. Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">No. Pembayaran</label>
                    <input
                        type="text"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-500 cursor-not-allowed"
                        value="AUTO-GENERATED"
                        disabled
                    >
                </div>

                <!-- Tanggal Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        wire:model="payment_date"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                    @error('payment_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Supplier <span class="text-rose-500">*</span></label>
                    <select
                        wire:model.live="supplier_id"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }} (Hutang: Rp {{ number_format($supplier->outstanding_debt, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Akun Kas / Bank -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Akun Kas / Bank <span class="text-rose-500">*</span></label>
                    <select
                        wire:model="account_id"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="">Pilih Akun Kas/Bank</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select
                        wire:model="payment_method"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="Cash">Tunai / Cash</option>
                        <option value="Bank Transfer">Transfer Bank</option>
                        <option value="Giro">Giro</option>
                        <option value="Cheque">Cek</option>
                    </select>
                    @error('payment_method') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Referensi Pembayaran -->
                <div class="md:column-span-1">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Referensi Pembayaran</label>
                    <input
                        type="text"
                        wire:model="payment_reference"
                        placeholder="e.g. No. Rek / Bukti Transfer"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                    @error('payment_reference') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Catatan -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan</label>
                    <textarea
                        wire:model="notes"
                        rows="1"
                        placeholder="Tambahkan catatan khusus..."
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors resize-none"
                    ></textarea>
                    @error('notes') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Invoices List -->
            <div>
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Rincian Invoice Pembelian yang Dibayar</h4>

                @if (empty($items))
                    <div class="p-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-center text-slate-400 dark:text-slate-500">
                        <i class="ph ph-receipt text-3xl mb-1.5"></i>
                        <p class="text-sm font-medium">Pilih supplier terlebih dahulu untuk menampilkan daftar invoice yang belum lunas.</p>
                    </div>
                @else
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <th class="px-5 py-3 text-left w-12">Pilih</th>
                                    <x-pos.table.th>No. Invoice / Transaksi</x-pos.table.th>
                                    <x-pos.table.th>Tanggal</x-pos.table.th>
                                    <x-pos.table.th>Jatuh Tempo</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Total</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Sisa Hutang</x-pos.table.th>
                                    <x-pos.table.th class="text-right w-44">Jumlah Bayar</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach ($items as $idx => $item)
                                    <x-pos.table.tr>
                                        <td class="px-5 py-3">
                                            <input
                                                type="checkbox"
                                                wire:model="items.{{ $idx }}.is_selected"
                                                wire:change="toggleItemSelection({{ $idx }})"
                                                class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary w-4.5 h-4.5 transition"
                                            >
                                        </td>
                                        <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                            <div>{{ $item['transaction_no'] }}</div>
                                            @if ($item['invoice_number'])
                                                <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 block">Inv: {{ $item['invoice_number'] }}</span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                            {{ date('d/m/Y', strtotime($item['transaction_date'])) }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                            {{ $item['due_date'] ? date('d/m/Y', strtotime($item['due_date'])) : '-' }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right text-sm text-slate-600 dark:text-slate-355">
                                            Rp {{ number_format($item['grand_total'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-200">
                                            Rp {{ number_format($item['amount_due'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs font-semibold">Rp</span>
                                                <input
                                                    type="number"
                                                    wire:model.live.debounce.500ms="items.{{ $idx }}.amount_paid"
                                                    class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-right text-slate-900 dark:text-white font-bold focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                                                    placeholder="0"
                                                    max="{{ $item['amount_due'] }}"
                                                >
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @endforeach
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    @php
                        $totalOutstanding = collect($items)->sum('amount_due');
                        $totalPayment = collect($items)->filter(fn($item) => $item['is_selected'] ?? false)->sum('amount_paid');
                    @endphp

                    <!-- Summary Area -->
                    <div class="mt-4 p-5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors">
                        <div>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Outstanding</span>
                            <span class="text-lg font-black text-slate-850 dark:text-slate-100">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Pembayaran</span>
                            <span class="text-2xl font-black text-primary dark:text-blue-400">Rp {{ number_format($totalPayment, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                <button
                    type="button"
                    wire:click="$set('showCreateModal', false)"
                    class="px-5 py-2 border border-slate-350 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="save('draft')"
                    class="px-5 py-2 border border-primary text-primary hover:bg-primary-light dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-950/20 text-sm font-bold rounded-xl transition-colors cursor-pointer"
                >
                    Simpan Draft
                </button>
                <button
                    type="button"
                    wire:click="save('posted')"
                    class="px-5 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                >
                    Simpan & Posting
                </button>
            </div>
        </div>
    </x-pos.modal>

    {{-- ===================== MODAL: DETAIL PELUNASAN HUTANG ===================== --}}
    <x-pos.modal
        wire:model="showDetailModal"
        title="Detail Pelunasan Hutang"
        subtitle="Rincian pembayaran hutang pembelian yang sudah tersimpan"
        icon="ph-eye"
        maxWidth="4xl"
    >
        @if ($detailPayment)
            <div class="space-y-6">
                <!-- Meta Info -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-slate-50 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200/50 dark:border-slate-800 transition-colors">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">No. Pembayaran</span>
                        <span class="text-sm font-mono font-bold text-slate-900 dark:text-white">{{ $detailPayment->payment_no }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Pembayaran</span>
                        <span class="text-sm font-semibold text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Supplier</span>
                        <span class="text-sm font-black text-slate-900 dark:text-white">{{ $detailPayment->supplier->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status</span>
                        @if ($detailPayment->status === 'draft')
                            <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-350 text-xs font-bold rounded-full border border-amber-200/50 dark:border-amber-850/30">
                                Draft
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30">
                                Posted
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Akun Kas / Bank</span>
                        <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->account->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Metode Pembayaran</span>
                        <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_method }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Referensi</span>
                        <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_reference ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Journal No</span>
                        <span class="text-sm font-mono text-slate-850 dark:text-slate-250">{{ $detailPayment->journal_no ?? '-' }}</span>
                    </div>
                    @if ($detailPayment->notes)
                        <div class="col-span-full border-t border-slate-200 dark:border-slate-800 pt-3 mt-1">
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Catatan</span>
                            <p class="text-sm text-slate-650 dark:text-slate-350 leading-relaxed">{{ $detailPayment->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Items Details -->
                <div>
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Invoice yang Dilunasi</h4>
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th>No. Invoice / Transaksi</x-pos.table.th>
                                    <x-pos.table.th>Tanggal</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Sisa Hutang Awal</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Jumlah Dibayar</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach ($detailPayment->items as $det)
                                    <x-pos.table.tr>
                                        <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                            <div>{{ $det->purchaseTransaction->transaction_no }}</div>
                                            @if ($det->purchaseTransaction->invoice_number)
                                                <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 block">Inv: {{ $det->purchaseTransaction->invoice_number }}</span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-650 dark:text-slate-350">
                                            {{ $det->purchaseTransaction->transaction_date->format('d/m/Y') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right text-sm text-slate-650 dark:text-slate-350">
                                            Rp {{ number_format($det->amount_due, 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right font-black text-primary dark:text-blue-400 text-sm">
                                            Rp {{ number_format($det->amount_paid, 0, ',', '.') }}
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @endforeach
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <div class="mt-4 p-5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 flex justify-between items-center transition-colors">
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Pelunasan</span>
                        <span class="text-2xl font-black text-primary dark:text-blue-400">Rp {{ number_format($detailPayment->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                    <button
                        type="button"
                        wire:click="$set('showDetailModal', false)"
                        class="px-5 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        @endif
    </x-pos.modal>

    {{-- ===================== CONFIRMATION: POST DRAFT PAYMENT ===================== --}}
    <x-pos.modal
        wire:model="showPostConfirmation"
        title="Posting Pelunasan Hutang"
        subtitle="Konfirmasi posting transaksi pelunasan hutang"
        icon="ph-warning"
        maxWidth="md"
    >
        <div class="space-y-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Apakah Anda yakin ingin memposting pelunasan hutang ini? Transaksi ini akan memperbarui hutang supplier secara permanen dan mencatat jurnal akuntansi secara otomatis.
            </p>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button
                    type="button"
                    wire:click="$set('showPostConfirmation', false)"
                    class="px-4 py-2 border border-slate-350 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="postPayment"
                    class="px-4 py-2 bg-emerald-650 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md transition duration-150"
                >
                    Ya, Posting
                </button>
            </div>
        </div>
    </x-pos.modal>

    {{-- ===================== CONFIRMATION: DELETE DRAFT PAYMENT ===================== --}}
    <x-pos.modal
        wire:model="showDeleteConfirmation"
        title="Hapus Draft Pelunasan"
        subtitle="Hapus transaksi draft secara permanen"
        icon="ph-trash"
        maxWidth="md"
    >
        <div class="space-y-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Apakah Anda yakin ingin menghapus draft pelunasan hutang ini secara permanen? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button
                    type="button"
                    wire:click="$set('showDeleteConfirmation', false)"
                    class="px-4 py-2 border border-slate-350 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="deletePayment"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-md transition duration-150"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
