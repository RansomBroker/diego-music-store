<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Pelunasan Piutang"
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
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Pelunasan Piutang</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <!-- Page Title -->
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pelunasan Piutang Pelanggan</h1>
                    </div>

                    <!-- Add Action -->
                    <button
                        wire:click="openCreate"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                    >
                        <i class="ph-bold ph-plus text-sm"></i>
                        <span>Pelunasan Piutang</span>
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
                                placeholder="Cari No. Transaksi / Pelanggan..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            >
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status:</label>
                            <select
                                wire:model.live="statusFilter"
                                class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-755 dark:text-slate-250 py-1.5 px-3 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition duration-150"
                            >
                                <option value="">Semua Status</option>
                                <option value="unpaid">Belum Lunas (Piutang)</option>
                                <option value="completed">Lunas (Completed)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th sortable field="invoice_number" :sortField="$sortField" :sortDirection="$sortDirection">
                                        No. Transaksi / Invoice
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="invoice_date" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Tanggal
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Pelanggan
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Metode Bayar
                                    </x-pos.table.th>
                                    <x-pos.table.th class="text-right" sortable field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Total Tagihan / Piutang
                                    </x-pos.table.th>
                                    <x-pos.table.th class="text-center">
                                        Status
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($payments as $payment)
                                    <x-pos.table.tr wire:click="showDetails({{ $payment->id }})" class="cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <x-pos.table.td class="whitespace-nowrap font-mono font-medium text-slate-900 dark:text-slate-100">
                                            {{ $payment->invoice_number }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                            {{ $payment->invoice_date?->format('d/m/Y') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $payment->customer?->name ?? 'Pelanggan Umum' }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm">
                                            {{ $payment->payment_method }}
                                        </x-pos.table.td>
                                        @php
                                            $piutangDue = $payment->getPiutangAmount();
                                        @endphp
                                        <x-pos.table.td class="whitespace-nowrap text-right font-mono">
                                            <div class="font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($payment->grand_total, 0, ',', '.') }}</div>
                                            @if ($piutangDue > 0)
                                                <div class="text-[11px] font-bold text-rose-500">Sisa: Rp {{ number_format($piutangDue, 0, ',', '.') }}</div>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-center">
                                            @if ($piutangDue <= 0)
                                                <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30">
                                                    Lunas (Completed)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-350 text-xs font-bold rounded-full border border-amber-200/50 dark:border-amber-850/30">
                                                    Belum Lunas (Piutang)
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="6" icon="ph-hand-coins" message="Belum ada riwayat transaksi piutang pelanggan." />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Pagination -->
                    @if ($payments->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-555 dark:text-slate-400">
                                <div>
                                    Menampilkan
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->firstItem() }}</span>
                                    sampai
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $payments->total() }}</span>
                                    hasil
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

    {{-- ===================== MODAL: PELUNASAN PIUTANG (CREATE) ===================== --}}
    <x-pos.modal
        wire:model="showCreateModal"
        title="Input Pelunasan Piutang"
        subtitle="Proses pelunasan piutang transaksi penjualan pelanggan"
        icon="ph-plus"
        maxWidth="5xl"
    >
        <div class="space-y-6">
            <!-- Informasi Utama -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Tanggal Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Pelunasan <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        wire:model="payment_date"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                    @error('payment_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Pelanggan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pelanggan <span class="text-rose-500">*</span></label>
                    <select
                        wire:model.live="customer_id"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="">Pilih Pelanggan</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} (Hutang: Rp {{ number_format($customer->total_piutang, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
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
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Metode Pelunasan <span class="text-rose-500">*</span></label>
                    <select
                        wire:model="payment_method"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="Tunai">Tunai / Cash</option>
                        <option value="Transfer BCA">Transfer BCA</option>
                        <option value="Transfer Mandiri">Transfer Mandiri</option>
                        <option value="Transfer BNI">Transfer BNI</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                    @error('payment_method') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Referensi Pembayaran -->
                <div>
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
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Rincian Invoice Penjualan yang Dibayar</h4>

                @if (empty($items))
                    <div class="p-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-center text-slate-400 dark:text-slate-500">
                        <i class="ph ph-receipt text-3xl mb-1.5"></i>
                        <p class="text-sm font-medium">Pilih pelanggan terlebih dahulu untuk menampilkan daftar invoice yang belum lunas.</p>
                    </div>
                @else
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <th class="px-5 py-3 text-left w-12">Pilih</th>
                                    <x-pos.table.th>No. Invoice</x-pos.table.th>
                                    <x-pos.table.th>Tanggal</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Total</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Sisa Piutang</x-pos.table.th>
                                    <x-pos.table.th class="text-right w-44">Jumlah Bayar</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach ($items as $idx => $item)
                                    <x-pos.table.tr>
                                        <td class="px-5 py-3">
                                            <input
                                                type="checkbox"
                                                wire:model.live="items.{{ $idx }}.is_selected"
                                                wire:change="toggleItemSelection({{ $idx }})"
                                                class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary w-4.5 h-4.5 transition cursor-pointer"
                                            >
                                        </td>
                                        <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                            <div class="font-mono font-bold">{{ $item['invoice_number'] }}</div>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                            {{ date('d/m/Y', strtotime($item['transaction_date'])) }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right text-sm text-slate-600 dark:text-slate-355 font-mono">
                                            Rp {{ number_format($item['grand_total'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-200 font-mono">
                                            Rp {{ number_format($item['amount_due'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <x-money-input
                                                wire:model.live.debounce.300ms="items.{{ $idx }}.amount_paid"
                                                class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-right text-slate-900 dark:text-white font-bold focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors font-mono"
                                                placeholder="0"
                                            />
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
                            <span class="text-lg font-black text-slate-850 dark:text-slate-100 font-mono">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Pembayaran</span>
                            <span class="text-2xl font-black text-primary dark:text-blue-400 font-mono">Rp {{ number_format($totalPayment, 0, ',', '.') }}</span>
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
                    wire:click="save('posted')"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow transition duration-150 cursor-pointer active:scale-[0.98] disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save('posted')">Simpan & Posting</span>
                    <span wire:loading wire:target="save('posted')">Memproses...</span>
                </button>
            </div>
        </div>
    </x-pos.modal>

    {{-- ===================== MODAL: DETAIL & HISTORI PELUNASAN PIUTANG ===================== --}}
    <x-pos.modal
        wire:model="showDetailModal"
        title="Detail & Histori Pembayaran Pelunasan"
        subtitle="Rincian transaksi invoice dan riwayat pembayaran cicilan/pelunasan"
        icon="ph-receipt"
        maxWidth="8xl"
    >
        @if ($selectedSale)
            @php
                $piutangRemaining = $selectedSale->getPiutangAmount();
                $totalPaidCalculated = max(0, floatval($selectedSale->grand_total) - $piutangRemaining);
            @endphp
            <div class="space-y-6">
                <!-- Summary Meta Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">No. Invoice</span>
                        <span class="text-base font-mono font-extrabold text-slate-900 dark:text-white">{{ $selectedSale->invoice_number }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</span>
                        <span class="text-base font-bold text-slate-900 dark:text-white">{{ $selectedSale->customer->name ?? 'Pelanggan Umum' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Transaksi</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $selectedSale->invoice_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status Pembayaran</span>
                        @if ($piutangRemaining <= 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-900/40">
                                Lunas (Completed)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full border border-amber-200 dark:border-amber-900/40">
                                Belum Lunas (Piutang)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Financial Calculation Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Total Belanja Invoice</span>
                        <span class="text-lg font-mono font-black text-slate-900 dark:text-white">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-emerald-50/40 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 text-center">
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider block mb-1">Total Sudah Dibayar</span>
                        <span class="text-lg font-mono font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPaidCalculated, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-rose-50/40 dark:bg-rose-950/20 p-4 rounded-xl border border-rose-200/60 dark:border-rose-900/40 text-center">
                        <span class="text-xs text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider block mb-1">Sisa Piutang / Tagihan</span>
                        <span class="text-xl font-mono font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($piutangRemaining, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Settlement History Table -->
                <div>
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Histori Riwayat Pembayaran / Cicilan</h4>
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th>No. Jurnal / Ref</x-pos.table.th>
                                    <x-pos.table.th>Tanggal Pelunasan</x-pos.table.th>
                                    <x-pos.table.th>Akun Penerima (Kas/Bank)</x-pos.table.th>
                                    <x-pos.table.th>Keterangan</x-pos.table.th>
                                    <x-pos.table.th>Kasir / Operator</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Nominal Dibayar</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($settlementHistory as $history)
                                    <x-pos.table.tr>
                                        <x-pos.table.td class="whitespace-nowrap font-mono font-bold text-xs text-slate-900 dark:text-slate-100">
                                            {{ $history['entry_number'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['date'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs font-bold text-blue-600 dark:text-blue-400">
                                            {{ $history['account_name'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['description'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['user_name'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right font-mono font-extrabold text-sm text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($history['amount'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="6" icon="ph-receipt" message="Belum ada riwayat cicilan/pelunasan yang tercatat untuk invoice ini." />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button
                        type="button"
                        wire:click="closeDetails"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        @endif
    </x-pos.modal>
</div>
