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
                    <div class="text-base  font-bold text-slate-650 dark:text-slate-400">#{{ str_pad($activeSession->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-slate-100 dark:border-slate-700/50 pt-4 space-y-3 text-center">
            <a
                href="{{ route('pos.z-report', $activeSession->id) }}"
                target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-bold rounded-xl transition-colors"
            >
                <i class="ph-bold ph-printer text-base text-primary dark:text-blue-400"></i>
                <span>Lihat Z-Report (Sementara)</span>
            </a>
            <div class="text-xs text-slate-400">
                Semua transaksi POS Anda akan otomatis terekam pada sesi ini.
            </div>
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

            <!-- Status Setoran Kas (Tipe 1: sudah setor di Kas Harian -> Done ✅ | Tipe 2: belum setor -> tombol Setor Kas Sekarang) -->
            @if ($cashTransferCount > 0)
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/25 border border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="ph-fill ph-check-circle text-xl"></i>
                        </div>
                        <div class="text-xs">
                            <div class="font-extrabold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                                <span>Setoran Kas Sudah Dilakukan</span>
                                <span class="px-1.5 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider">Done</span>
                            </div>
                            <p class="text-emerald-700/90 dark:text-emerald-400/90 mt-0.5">
                                Total setor <strong>Rp {{ number_format($cashTransferOut, 0, ',', '.') }}</strong> ({{ $cashTransferCount }}x{{ $lastTransferLabel ? ' • ' . $lastTransferLabel : '' }}) sudah mengurangi ekspektasi laci.
                            </p>
                        </div>
                    </div>
                    @if (\App\Helpers\PermissionHelper::can(Auth::user(), ['fo.daily_cash.create', 'fo.cash_session.close']))
                        <button
                            type="button"
                            wire:click="openTransferModal"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-emerald-100/70 dark:hover:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold rounded-lg transition-colors cursor-pointer flex-shrink-0"
                        >
                            <i class="ph-bold ph-plus text-xs"></i>
                            <span>Tambah Setoran</span>
                        </button>
                    @endif
                </div>
            @else
                <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="ph-fill ph-warning-circle text-xl"></i>
                        </div>
                        <div class="text-xs">
                            <div class="font-extrabold text-amber-800 dark:text-amber-300">Belum Ada Setoran Kas di Sesi Ini</div>
                            <p class="text-amber-700/90 dark:text-amber-400/90 mt-0.5">
                                Jika uang tunai sudah/ingin dipindahkan ke Kas Kecil atau Owner, catat setoran dulu agar ekspektasi laci pas.
                            </p>
                        </div>
                    </div>
                    @if (\App\Helpers\PermissionHelper::can(Auth::user(), ['fo.daily_cash.create', 'fo.cash_session.close']))
                        <button
                            type="button"
                            wire:click="openTransferModal"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer flex-shrink-0"
                        >
                            <i class="ph-bold ph-arrows-left-right text-sm"></i>
                            <span>Setor Kas Sekarang</span>
                        </button>
                    @endif
                </div>
            @endif

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

        @if (\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.cash_session.close'))
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
        @endif
    </div>
</div>
