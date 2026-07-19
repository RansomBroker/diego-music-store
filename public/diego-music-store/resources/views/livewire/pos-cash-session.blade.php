<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200" x-data="{ 
    openModal: @entangle('showSupervisorModal') 
}" @print-z-report.window="window.open($event.detail[0].url, '_blank', 'width=400,height=600,menubar=no,toolbar=no,location=no,status=no')">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header — menggunakan komponen navbar POS global -->
        <x-pos.navbar
            pageTitle="Sesi Kasir"
            backLabel="Dashboard"
            :activeSessionInfo="$activeSession ? [
                'id'           => $activeSession->id,
                'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
                'opening_cash' => $activeSession->opening_cash,
            ] : null"
        >
            {{-- Tombol Kembali ke POS Kasir (hanya tampil jika sesi aktif) --}}
            @if ($activeSession)
                <a href="/pos" class="flex items-center gap-2 px-4 h-11 bg-primary hover:bg-primaryDark text-white font-semibold text-sm rounded-xl shadow-md shadow-primary/20 transition-all">
                    <i class="ph-bold ph-squares-four text-lg"></i>
                    <span>Ke POS Kasir</span>
                </a>
            @endif
        </x-pos.navbar>

        <!-- Tabs Sub-navigation -->
        <div class="flex border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition-colors flex-shrink-0 px-8">
            <button wire:click="$set('activeTab', 'sesi')" class="py-4 px-6 font-bold text-sm border-b-2 transition-all outline-none focus:outline-none {{ $activeTab === 'sesi' ? 'border-primary text-primary dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300' }}">
                Sesi Kasir Saat Ini
            </button>
            <button wire:click="$set('activeTab', 'riwayat')" class="py-4 px-6 font-bold text-sm border-b-2 transition-all outline-none focus:outline-none {{ $activeTab === 'riwayat' ? 'border-primary text-primary dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300' }}">
                Riwayat Sesi Shift
            </button>
        </div>

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
            
            @if ($activeTab === 'sesi')
                @if ($activeSession)
                    <!-- ================= ACTIVE SESSION VIEW ================= -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left: Session Info Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm flex flex-col justify-between transition-colors">
                            <div>
                                <div class="flex justify-between items-start mb-6">
                                    <span class="text-xs font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase">Status Sesi Anda</span>
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        AKTIF (OPEN)
                                    </span>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl space-y-1">
                                        <div class="text-xs text-slate-400">Nama Cabang</div>
                                        <div class="text-base font-bold text-slate-800 dark:text-slate-200">{{ $activeSession->branch->name }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl space-y-1">
                                        <div class="text-xs text-slate-400">Waktu Mulai Sesi</div>
                                        <div class="text-base font-bold text-slate-800 dark:text-slate-200">{{ $activeSession->opened_at->format('d M Y - H:i') }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl space-y-1">
                                        <div class="text-xs text-slate-400">Sesi ID</div>
                                        <div class="text-base font-mono font-bold text-slate-650 dark:text-slate-400">#{{ str_pad($activeSession->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 border-t border-slate-100 dark:border-slate-700/50 pt-4 text-xs text-slate-400 text-center">
                                Semua transaksi POS Anda akan otomatis terekam pada sesi ini.
                            </div>
                        </div>

                        <!-- Right: Close Shift reconciliation form -->
                        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm flex flex-col justify-between transition-colors">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                                    <i class="ph-bold ph-lock text-xl text-amber-500"></i>
                                    Tutup Sesi & Rekonsiliasi Kas Laci
                                </h2>

                                <!-- Display calculation cards -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div class="bg-slate-50 dark:bg-slate-900/30 p-5 rounded-xl border border-slate-100 dark:border-slate-700/40 text-center">
                                        <div class="text-xs text-slate-400 font-medium mb-1">Modal Awal</div>
                                        <div class="text-lg font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($activeSession->opening_cash, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900/30 p-5 rounded-xl border border-slate-100 dark:border-slate-700/40 text-center">
                                        <div class="text-xs text-slate-400 font-medium mb-1">Penjualan</div>
                                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-450">Rp {{ number_format($cashSales, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-primary/5 dark:bg-blue-950/20 p-5 rounded-xl border border-primary/10 dark:border-blue-900/35 text-center">
                                        <div class="text-xs text-primary dark:text-blue-400 font-bold mb-1">Ekspektasi Kas Laci</div>
                                        <div class="text-xl font-extrabold text-primary dark:text-blue-400">Rp {{ number_format($expectedCash, 0, ',', '.') }}</div>
                                    </div>
                                </div>

                                <!-- Input form -->
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-2">Kas Fisik Riil di Laci (Actual Cash)</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Rp</span>
                                            <input type="number" wire:model.live="actualCash" class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent font-bold text-lg text-slate-850 dark:text-white transition-colors" placeholder="0">
                                        </div>
                                        @error('actualCash') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Preset values for actual cash -->
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" wire:click="selectActualPreset({{ $expectedCash }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">Sesuai Ekspektasi</button>
                                        <button type="button" wire:click="selectActualPreset({{ $expectedCash + 50000 }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">+ Rp 50.000</button>
                                        <button type="button" wire:click="selectActualPreset({{ $expectedCash - 50000 }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">- Rp 50.000</button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-2">Catatan Penutupan</label>
                                        <textarea wire:model="closingNotes" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-slate-800 dark:text-slate-200 transition-colors" placeholder="Tuliskan catatan opsional mengenai shift/laci hari ini..."></textarea>
                                    </div>

                                    <!-- Difference indicators -->
                                    @php
                                        $diff = $actualCash - $expectedCash;
                                    @endphp
                                    @if ($diff !== 0)
                                        <div class="p-4 rounded-xl border flex items-center justify-between {{ $diff < 0 ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/20 dark:border-rose-900 dark:text-rose-455' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/20 dark:border-emerald-900 dark:text-emerald-455' }}">
                                            <div class="flex items-center gap-2.5">
                                                <i class="ph-fill {{ $diff < 0 ? 'ph-warning-octagon' : 'ph-check-circle' }} text-xl"></i>
                                                <div class="text-xs">
                                                    <span class="font-bold">Selisih Kas Terdeteksi:</span>
                                                    {{ $diff < 0 ? 'Kas fisik kurang dari hitungan sistem. Perlu otorisasi PIN supervisor/owner.' : 'Kas fisik lebih dari hitungan sistem. Perlu otorisasi PIN supervisor/owner.' }}
                                                </div>
                                            </div>
                                            <div class="text-sm font-extrabold whitespace-nowrap">
                                                {{ $diff < 0 ? '-' : '+' }} Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 bg-emerald-50/45 border border-emerald-100 text-emerald-800 dark:bg-emerald-950/10 dark:border-emerald-950/30 dark:text-emerald-455 rounded-xl flex items-center gap-2.5">
                                            <i class="ph-bold ph-check text-emerald-600 dark:text-emerald-450 text-lg"></i>
                                            <span class="text-xs font-semibold">Kas Seimbang (Sesuai Ekspektasi)</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-8 border-t border-slate-100 dark:border-slate-700/50 pt-6">
                                <button type="button" wire:click="confirmCloseSession" class="w-full flex items-center justify-center gap-2 h-13 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg transition-colors">
                                    <i class="ph-bold ph-power text-lg"></i>
                                    <span>Tutup Shift & Cetak Z-Report</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- ================= NO ACTIVE SESSION / OPEN SESSION FORM ================= -->
                    <div class="max-w-xl mx-auto bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-8 shadow-xl transition-colors">
                        <div class="text-center mb-8">
                            <div class="w-16 h-16 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center mx-auto mb-4">
                                <i class="ph-bold ph-key text-3xl"></i>
                            </div>
                            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Buka Sesi Kasir Baru</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan modal awal laci kasir Anda untuk memulai transaksi hari ini.</p>
                        </div>

                        <form wire:submit.prevent="openSession" class="space-y-6">
                            <!-- Branch Select -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Pilih Cabang Bertugas</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500">
                                        <i class="ph-bold ph-storefront text-lg"></i>
                                    </span>
                                    <select wire:model="selectedBranchId" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm font-semibold text-slate-850 dark:text-slate-200 transition-colors">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->store_name ?: $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('selectedBranchId') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Opening Cash -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Modal Uang Tunai Awal (Opening Cash)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Rp</span>
                                    <input type="number" wire:model.live="openingCash" class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent font-bold text-lg text-slate-850 dark:text-white transition-colors" placeholder="0">
                                </div>
                                @error('openingCash') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Quick presets -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="selectOpeningPreset(200000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">Rp 200.000</button>
                                <button type="button" wire:click="selectOpeningPreset(500000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">Rp 500.000</button>
                                <button type="button" wire:click="selectOpeningPreset(1000000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors">Rp 1.000.000</button>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Catatan Tambahan</label>
                                <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-slate-800 dark:text-slate-200 transition-colors" placeholder="Catatan opsional pembukaan shift..."></textarea>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="w-full flex items-center justify-center gap-2 h-13 bg-primary hover:bg-primaryDark text-white font-bold rounded-xl shadow-lg shadow-primary/20 transition-all">
                                <i class="ph-bold ph-keyhole text-lg"></i>
                                <span>Buka Sesi & Mulai Transaksi</span>
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <!-- ================= SHIFT HISTORY TABLE ================= -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
                        <h3 class="text-base font-black text-slate-850 dark:text-white leading-tight">Riwayat Sesi Kasir Anda (10 Terakhir)</h3>
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Total riwayat shift kasir aktif</span>
                    </div>
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th>No Sesi</x-pos.table.th>
                                    <x-pos.table.th>Cabang</x-pos.table.th>
                                    <x-pos.table.th>Waktu Mulai</x-pos.table.th>
                                    <x-pos.table.th>Waktu Selesai</x-pos.table.th>
                                    <x-pos.table.th>Modal Awal</x-pos.table.th>
                                    <x-pos.table.th>Ekspektasi</x-pos.table.th>
                                    <x-pos.table.th>Kas Fisik</x-pos.table.th>
                                    <x-pos.table.th>Selisih</x-pos.table.th>
                                    <x-pos.table.th>Status</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse($history as $item)
                                    <x-pos.table.tr wire:key="session-{{ $item->id }}" wire:click="showTransactions({{ $item->id }})" class="cursor-pointer">
                                        <x-pos.table.td class="whitespace-nowrap text-sm font-mono font-medium text-slate-900 dark:text-slate-100">#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->branch->name }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">{{ $item->opened_at->format('d/m H:i') }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">{{ $item->closed_at ? $item->closed_at->format('d/m H:i') : '-' }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($item->opening_cash, 0, ',', '.') }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">Rp {{ number_format($item->expected_cash, 0, ',', '.') }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->actual_cash !== null ? 'Rp ' . number_format($item->actual_cash, 0, ',', '.') : '-' }}</x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold">
                                            @if($item->difference === null)
                                                -
                                            @elseif($item->difference === 0)
                                                <span class="text-emerald-600 dark:text-emerald-455 font-bold">Seimbang</span>
                                            @elseif($item->difference < 0)
                                                <span class="text-rose-600 dark:text-rose-455 font-bold">-Rp {{ number_format(abs($item->difference), 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-emerald-600 dark:text-emerald-455 font-bold">+Rp {{ number_format($item->difference, 0, ',', '.') }}</span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm">
                                            @if($item->status === 'open')
                                                <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30 animate-pulse">Aktif</span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-655 dark:text-slate-350 text-xs font-bold rounded-full border border-slate-200 dark:border-slate-700">Closed</span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($item->status === 'closed')
                                                    <button onclick="window.open('{{ route('pos.z-report', $item->id) }}', '_blank', 'width=400,height=600,menubar=no,toolbar=no')" @click.stop class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-850 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer" title="Cetak Z-Report">
                                                        <i class="ph-bold ph-printer text-base"></i>
                                                    </button>
                                                    @if($loop->first)
                                                        <button wire:click.stop="requestReopenSession({{ $item->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:text-amber-700 transition-colors cursor-pointer" title="Buka Kembali Sesi">
                                                            <i class="ph-bold ph-key-return text-base"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="10" icon="ph-calendar" message="Belum ada riwayat sesi kasir" />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>
                </div>
            @endif
        </div>
    </main>

    <!-- ================= SUPERVISOR APPROVAL MODAL ================= -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6 shadow-2xl space-y-6 text-left transition-colors">
            
            <div class="flex items-center gap-3.5 text-amber-600 dark:text-amber-500">
                <i class="ph-fill ph-warning-octagon text-3xl"></i>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Otorisasi Selisih Kasir</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Persetujuan dari Owner / Admin dibutuhkan.</p>
                </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-250/25 dark:border-amber-900 p-4 rounded-xl text-xs text-amber-805 dark:text-amber-455 font-semibold space-y-1">
                <div>Ekspektasi Kasir: Rp {{ number_format($expectedCash, 0, ',', '.') }}</div>
                <div>Kas Fisik Diinput: Rp {{ number_format($actualCash, 0, ',', '.') }}</div>
                <div class="border-t border-amber-250/25 dark:border-amber-900 mt-2 pt-1 font-bold text-sm">
                    Selisih: Rp {{ number_format($actualCash - $expectedCash, 0, ',', '.') }}
                </div>
            </div>

            <form wire:submit.prevent="authorizeAndClose" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Email Supervisor/Owner</label>
                    <input type="email" wire:model="supervisorEmail" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-white transition-colors" placeholder="email@domain.com">
                    @error('supervisorEmail') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Password Supervisor/Owner</label>
                    <input type="password" wire:model="supervisorPassword" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-white transition-colors" placeholder="••••••••">
                    @error('supervisorPassword') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showSupervisorModal', false)" class="flex-1 h-12 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-650 dark:text-slate-200 font-bold rounded-xl text-sm transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 h-12 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm shadow-md shadow-amber-600/10 transition-colors">
                        Otorisasi & Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TRANSACTIONS DETAILS MODAL ================= -->
    <x-pos.modal 
        wire:model="showTransactionsModal" 
        title="Detail Transaksi Sesi #{{ str_pad($selectedSessionId ?? '', 5, '0', STR_PAD_LEFT) }}"
        subtitle="Daftar transaksi penjualan yang tercatat pada sesi kasir ini."
        icon="ph-receipt"
        maxWidth="3xl"
    >
        <div class="space-y-6 text-left">
            <!-- Summary Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Transaksi</span>
                    <span class="text-lg font-black text-slate-850 dark:text-white">{{ $selectedSessionSummary['transaction_count'] ?? 0 }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Omset</span>
                    <span class="text-lg font-black text-slate-850 dark:text-white">Rp {{ number_format($selectedSessionSummary['total_sales'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tunai (Cash)</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-450">Rp {{ number_format($selectedSessionSummary['cash_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Non-Tunai</span>
                    <span class="text-lg font-black text-blue-650 dark:text-blue-400">Rp {{ number_format($selectedSessionSummary['non_cash_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

                <x-pos.table.container>
                    <x-pos.table>
                        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                            <tr>
                                <x-pos.table.th>No. Invoice</x-pos.table.th>
                                <x-pos.table.th>Waktu</x-pos.table.th>
                                <x-pos.table.th>Pelanggan</x-pos.table.th>
                                <x-pos.table.th>Pembayaran</x-pos.table.th>
                                <x-pos.table.th class="text-right">Total Belanja</x-pos.table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($selectedSessionTransactions as $sale)
                                <x-pos.table.tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-100">
                                    <x-pos.table.td class="whitespace-nowrap text-sm font-mono font-medium text-slate-900 dark:text-slate-100">
                                        {{ $sale->invoice_number ?? '-' }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-350">
                                        {{ $sale->created_at->format('H:i:s') }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $sale->customer->name ?? 'Umum / Walk-in' }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm">
                                        @if($sale->payment_method === 'cash')
                                            <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-250/20 dark:border-emerald-800/30">Tunai</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-350 text-xs font-bold rounded-full border border-blue-250/20 dark:border-blue-800/30">
                                                {{ ucfirst($sale->payment_method) }}
                                            </span>
                                        @endif
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm font-black text-right text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                    </x-pos.table.td>
                                </x-pos.table.tr>
                            @empty
                                <x-pos.table.empty colspan="5" icon="ph-receipt" message="Tidak ada transaksi tercatat pada sesi ini" />
                            @endforelse
                        </tbody>
                    </x-pos.table>
                </x-pos.table.container>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showTransactionsModal', false)"
                    class="px-5 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
