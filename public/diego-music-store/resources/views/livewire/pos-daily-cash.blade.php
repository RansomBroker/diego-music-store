<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200" style="font-family: 'Outfit', sans-serif;">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Kas Harian"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

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
                                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 duration-200"
                            >
                                <i class="ph ph-arrow-up-right text-base"></i>
                                Catat Kas Masuk
                            </button>
                            <button
                                wire:click="openOutModal"
                                class="flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-bold rounded-xl shadow-md shadow-rose-500/20 transition-all transform hover:-translate-y-0.5 duration-200"
                            >
                                <i class="ph ph-arrow-down-left text-base"></i>
                                Catat Kas Keluar
                            </button>
                        </div>
                    @endif
                </div>

                @if(!$activeSession)
                    <!-- Warning: No Active Session -->
                    <div class="bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-900/50 shadow-md rounded-2xl p-8 text-center max-w-2xl mx-auto my-12 transition-all">
                        <div class="w-16 h-16 bg-amber-50 dark:bg-amber-950/40 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                            <i class="ph ph-lock text-3xl"></i>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-850 dark:text-white mb-2">Sesi Kasir Belum Dibuka</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">
                            Anda harus membuka sesi kasir (shift) terlebih dahulu sebelum dapat mencatat transaksi Kas Harian atau melakukan penjualan.
                        </p>
                        <a
                            href="/pos/session"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl shadow-lg shadow-blue-500/25 transition-all transform hover:-translate-y-0.5 duration-200"
                        >
                            <i class="ph ph-key text-base"></i>
                            Buka Sesi Kasir Sekarang
                        </a>
                    </div>
                @else
                    <!-- Summary Metrics Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Card 1: Modal Awal -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-vault text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Modal Awal Sesi</span>
                                <span class="text-base font-bold text-slate-800 dark:text-slate-100">
                                    Rp {{ number_format($openingCash, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card 2: Penjualan Tunai -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-tag text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Penjualan Tunai</span>
                                <span class="text-base font-bold text-slate-800 dark:text-slate-100">
                                    Rp {{ number_format($cashSales, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card 3: Kas Masuk -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-arrow-circle-up-right text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Kas Masuk</span>
                                <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                    +Rp {{ number_format($cashIn, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card 4: Kas Keluar -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl p-4 flex items-center gap-4 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-arrow-circle-down-left text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Kas Keluar</span>
                                <span class="text-base font-bold text-rose-650 dark:text-rose-400">
                                    -Rp {{ number_format($cashOut, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card 5: Kas Laci (Net) -->
                        <div class="bg-gradient-to-br from-primary-dark to-primary dark:from-blue-900 dark:to-blue-800 text-white rounded-xl p-4 flex items-center gap-4 shadow-md shadow-blue-500/10">
                            <div class="w-10 h-10 rounded-lg bg-white/10 text-white flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-coins text-xl"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-white/70 uppercase tracking-wider">Uang Laci (Teoritis)</span>
                                <span class="text-base font-black">
                                    Rp {{ number_format($expectedCash, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Transactions Table Card Wrapper (Filament Style) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-850 dark:text-white">Riwayat Kas Sesi Ini</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar transaksi kas masuk & keluar yang tercatat pada shift saat ini.</p>
                            </div>
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider rounded-md self-start sm:self-center">
                                Sesi #{{ str_pad($activeSession->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        <!-- Table Container -->
                        <x-pos.table.container>
                            <x-pos.table>
                                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                    <tr>
                                        <x-pos.table.th>Waktu</x-pos.table.th>
                                        <x-pos.table.th>No. Transaksi</x-pos.table.th>
                                        <x-pos.table.th>Tipe</x-pos.table.th>
                                        <x-pos.table.th>Kategori / Akun Kontra</x-pos.table.th>
                                        <x-pos.table.th>Catatan / Keterangan</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Nominal</x-pos.table.th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @forelse($transactions as $tx)
                                        <x-pos.table.tr>
                                            <x-pos.table.td class="whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400">
                                                {{ $tx->created_at->format('d M Y, H:i') }}
                                            </x-pos.table.td>
                                            <x-pos.table.td class="whitespace-nowrap font-mono text-xs font-bold text-slate-900 dark:text-slate-100">
                                                {{ $tx->transaction_no }}
                                            </x-pos.table.td>
                                            <x-pos.table.td class="whitespace-nowrap">
                                                @if($tx->type === 'in')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450 border border-emerald-100 dark:border-emerald-900/40">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Kas Masuk
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-455 border border-rose-100 dark:border-rose-900/40">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Kas Keluar
                                                    </span>
                                                @endif
                                            </x-pos.table.td>
                                            <x-pos.table.td class="font-semibold text-slate-800 dark:text-slate-200">
                                                @if($tx->type === 'in')
                                                    {{ $tx->sourceAccount ? $tx->sourceAccount->name : 'N/A' }}
                                                @else
                                                    {{ $tx->destinationAccount ? $tx->destinationAccount->name : 'N/A' }}
                                                @endif
                                            </x-pos.table.td>
                                            <x-pos.table.td class="text-xs font-medium text-slate-555 dark:text-slate-400 max-w-xs truncate" title="{{ $tx->notes }}">
                                                {{ $tx->notes ?: '-' }}
                                            </x-pos.table.td>
                                            <x-pos.table.td class="whitespace-nowrap text-right font-bold text-sm {{ $tx->type === 'in' ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-600 dark:text-rose-450' }}">
                                                {{ $tx->type === 'in' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                            </x-pos.table.td>
                                        </x-pos.table.tr>
                                    @empty
                                        <x-pos.table.empty colspan="6" icon="ph-receipt-x" message="Belum ada transaksi kas tercatat di sesi ini." />
                                    @endforelse
                                </tbody>
                            </x-pos.table>
                        </x-pos.table.container>

                        <!-- Footer (Filament v3 Style Pagination) -->
                        @if ($transactions && $transactions->total() > 0)
                            <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                                <!-- Left: Statistics & Per Page -->
                                <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                                    <div>
                                        Menampilkan
                                        <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->firstItem() }}</span>
                                        sampai
                                        <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->lastItem() }}</span>
                                        dari
                                        <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->total() }}</span>
                                        hasil
                                    </div>
                                </div>

                                <!-- Right: Navigation Pages -->
                                @if ($transactions->hasPages())
                                    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                                        {{-- Previous Page Button --}}
                                        @if ($transactions->onFirstPage())
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                                                <i class="ph-bold ph-caret-left text-sm"></i>
                                            </span>
                                        @else
                                            <button
                                                wire:click="previousPage"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                            >
                                                <i class="ph-bold ph-caret-left text-sm"></i>
                                            </button>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @foreach ($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                                            @if ($page == $transactions->currentPage())
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <button
                                                    wire:click="gotoPage({{ $page }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                                                >
                                                    {{ $page }}
                                                </button>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Button --}}
                                        @if ($transactions->hasMorePages())
                                            <button
                                                wire:click="nextPage"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                            >
                                                <i class="ph-bold ph-caret-right text-sm"></i>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
                                                <i class="ph-bold ph-caret-right text-sm"></i>
                                            </span>
                                        @endif
                                    </nav>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Modal: Kas Masuk -->
    <x-pos.modal
        wire:model="showInModal"
        title="Pemasukan Kas (Kas Masuk)"
        subtitle="Catat penambahan modal atau dana masuk lainnya ke laci kasir"
        icon="ph-arrow-up-right"
        maxWidth="md"
    >
        <!-- Form Body -->
        <form wire:submit.prevent="saveInflow" class="space-y-4">
            <!-- Nominal -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Uang (Rp) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500 font-bold text-sm">Rp</span>
                    <input
                        type="number"
                        wire:model="inAmount"
                        placeholder="0"
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-colors"
                    >
                </div>
                @error('inAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Source/Kontra Akun -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sumber Pemasukan Kas</label>
                <select
                    wire:model="inSourceAccountId"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-colors"
                >
                    @foreach($inflowSources as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                    @endforeach
                </select>
                @error('inSourceAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Alasan</label>
                <textarea
                    wire:model="inNotes"
                    rows="3"
                    placeholder="Contoh: Tambahan modal laci kasir, Pengembalian dana belanja ATK, dll."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-colors resize-none"
                ></textarea>
                @error('inNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showInModal', false)"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                >
                    <i class="ph-bold ph-check text-xs"></i>
                    <span>Simpan Transaksi</span>
                </button>
            </div>
        </form>
    </x-pos.modal>

    <!-- Modal: Kas Keluar -->
    <x-pos.modal
        wire:model="showOutModal"
        title="Pengeluaran Kas (Kas Keluar)"
        subtitle="Catat pengeluaran operasional kecil menggunakan uang laci kasir"
        icon="ph-arrow-down-left"
        maxWidth="md"
    >
        <!-- Form Body -->
        <form wire:submit.prevent="saveOutflow" class="space-y-4">
            <!-- Nominal -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Uang (Rp) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-550 font-bold text-sm">Rp</span>
                    <input
                        type="number"
                        wire:model="outAmount"
                        placeholder="0"
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors"
                    >
                </div>
                @error('outAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Destination/Kontra Akun -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kategori Pengeluaran (Beban)</label>
                <select
                    wire:model="outDestinationAccountId"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors"
                >
                    @foreach($outflowDestinations as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                    @endforeach
                </select>
                @error('outDestinationAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Keterangan Belanja</label>
                <textarea
                    wire:model="outNotes"
                    rows="3"
                    placeholder="Contoh: Beli air galon aqua toko, Uang parkir kurir, Beli stop kontak, dll."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors resize-none"
                ></textarea>
                @error('outNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showOutModal', false)"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                >
                    <i class="ph-bold ph-check text-xs"></i>
                    <span>Simpan Transaksi</span>
                </button>
            </div>
        </form>
    </x-pos.modal>
</div>
