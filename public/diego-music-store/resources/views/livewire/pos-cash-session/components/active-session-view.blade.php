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
                    <x-money-input 
                        wire:model.live="actualCash"
                        class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent font-bold text-lg text-slate-850 dark:text-white transition-colors"
                        placeholder="0"
                    />
                    @error('actualCash') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Preset values for actual cash -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="selectActualPreset({{ $expectedCash }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Sesuai Ekspektasi</button>
                    <button type="button" wire:click="selectActualPreset({{ $expectedCash + 50000 }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">+ Rp 50.000</button>
                    <button type="button" wire:click="selectActualPreset({{ $expectedCash - 50000 }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">- Rp 50.000</button>
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
            <x-pos.utility.button
                type="button"
                variant="primary"
                wire:click="confirmCloseSession"
                class="w-full justify-center !py-3.5 !rounded-xl"
                icon="ph-power"
            >
                Tutup Shift & Cetak Z-Report
            </x-pos.utility.button>
        </div>
    </div>
</div>
