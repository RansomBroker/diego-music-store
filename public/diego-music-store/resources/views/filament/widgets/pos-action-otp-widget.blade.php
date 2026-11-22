<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="{
                expiresAt: @entangle('expiresAtTimestamp'),
                nowTs: Math.floor(Date.now() / 1000),
                copied: false,
                showLogs: false,
                timer: null,
                get remaining() {
                    return Math.max(0, Number(this.expiresAt || 0) - this.nowTs);
                },
                init() {
                    this.timer = setInterval(() => {
                        this.nowTs = Math.floor(Date.now() / 1000);
                    }, 1000);
                },
                destroy() {
                    if (this.timer) clearInterval(this.timer);
                },
                copyCode(code) {
                    if (!code || this.remaining <= 0) return;
                    navigator.clipboard?.writeText(code);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }"
            class="space-y-4"
        >
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                        <span>OTP Otorisasi POS</span>
                        <span>•</span>
                        <span>Berlaku 1 Menit</span>
                    </div>
                    <h2 class="text-base font-bold text-gray-950 dark:text-white">
                        Kode OTP Edit &amp; Hapus Transaksi POS
                    </h2>
                    <p class="text-xs text-gray-600 dark:text-gray-400 max-w-xl">
                        Berikan kode OTP 6 digit ini kepada kasir/staf saat mereka perlu mengedit atau menghapus transaksi di Daftar Transaksi POS. Owner tetap dapat mengedit dan menghapus transaksi tanpa OTP.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div
                        class="flex items-center justify-center px-5 py-3 rounded-xl border-2 transition-colors"
                        :class="remaining > 0
                            ? 'border-primary-500/40 bg-primary-50/70 dark:bg-primary-950/30'
                            : 'border-danger-500/40 bg-danger-50/70 dark:bg-danger-950/30'"
                    >
                        <span
                            class="font-mono text-3xl font-black tracking-[0.26em] select-all"
                            :class="remaining > 0 ? 'text-gray-950 dark:text-white' : 'text-gray-400 line-through'"
                        >
                            {{ $currentCode }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="text-xs font-semibold">
                            <template x-if="remaining > 0">
                                <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif — <span x-text="remaining"></span> dtk (s/d {{ $expiresAtFormatted }})
                                </span>
                            </template>
                            <template x-if="remaining <= 0">
                                <span class="inline-flex items-center gap-1.5 text-danger-600 dark:text-danger-400">
                                    <span class="w-2 h-2 rounded-full bg-danger-500"></span>
                                    Kedaluwarsa — klik Refresh OTP
                                </span>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-filament::button
                                type="button"
                                size="sm"
                                color="gray"
                                x-bind:disabled="remaining <= 0"
                                x-on:click="copyCode(@js($currentCode))"
                            >
                                <span x-show="!copied">Salin</span>
                                <span x-show="copied" x-cloak>Tersalin!</span>
                            </x-filament::button>

                            @if (\App\Helpers\PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.pos_action_otp.refresh'))
                                <x-filament::button
                                    type="button"
                                    size="sm"
                                    color="primary"
                                    wire:click="refreshOtp"
                                    wire:loading.attr="disabled"
                                >
                                    Refresh OTP
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                type="button"
                                size="sm"
                                color="gray"
                                outlined
                                x-on:click="showLogs = !showLogs"
                            >
                                <span x-show="!showLogs">Riwayat ({{ $this->recentLogs->count() }})</span>
                                <span x-show="showLogs" x-cloak>Tutup Riwayat</span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showLogs" x-cloak class="pt-3 border-t border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-2">Waktu</th>
                                <th class="px-4 py-2">Pengguna</th>
                                <th class="px-4 py-2">Aksi</th>
                                <th class="px-4 py-2">No. Invoice</th>
                                <th class="px-4 py-2">Cabang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            @forelse ($this->recentLogs as $log)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $log->used_at?->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                        {{ $log->user?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        @if ($log->action === 'delete_sale')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-danger-50 text-danger-700 dark:bg-danger-950/40 dark:text-danger-400">
                                                Hapus Transaksi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-warning-50 text-warning-700 dark:bg-warning-950/40 dark:text-warning-400">
                                                Edit Transaksi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap font-mono font-semibold">
                                        {{ $log->invoice_number ?? ('#' . $log->sale_id) }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{ $log->branch?->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada riwayat pemakaian OTP.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

