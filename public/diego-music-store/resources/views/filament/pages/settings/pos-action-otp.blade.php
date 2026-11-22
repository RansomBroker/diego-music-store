<x-filament-panels::page>
    <div
        x-data="{
            expiresAt: @entangle('expiresAtTimestamp'),
            nowTs: Math.floor(Date.now() / 1000),
            copied: false,
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
        class="space-y-6"
    >
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                        <span>Otorisasi Backoffice</span>
                        <span>•</span>
                        <span>Berlaku 1 Menit</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">
                        Kode OTP Edit &amp; Hapus Transaksi POS
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xl">
                        Berikan kode OTP 6 digit ini kepada kasir/staf saat mereka perlu mengedit atau menghapus transaksi di Daftar Transaksi POS. Owner tetap dapat mengedit dan menghapus transaksi tanpa OTP.
                    </p>
                </div>

                <div class="flex flex-col items-center md:items-end gap-3">
                    <div
                        class="flex items-center gap-3 px-6 py-4 rounded-2xl border-2 transition-colors"
                        :class="remaining > 0
                            ? 'border-primary-500/40 bg-primary-50/70 dark:bg-primary-950/30'
                            : 'border-danger-500/40 bg-danger-50/70 dark:bg-danger-950/30'"
                    >
                        <span
                            class="font-mono text-4xl font-black tracking-[0.28em] select-all"
                            :class="remaining > 0 ? 'text-gray-950 dark:text-white' : 'text-gray-400 line-through'"
                        >
                            {{ $currentCode }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-xs font-semibold">
                        <template x-if="remaining > 0">
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif — sisa <span x-text="remaining"></span> detik (s/d {{ $expiresAtFormatted }})
                            </span>
                        </template>
                        <template x-if="remaining <= 0">
                            <span class="inline-flex items-center gap-1.5 text-danger-600 dark:text-danger-400">
                                <span class="w-2 h-2 rounded-full bg-danger-500"></span>
                                Kedaluwarsa — klik Refresh OTP untuk kode baru
                            </span>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <x-filament::button
                            type="button"
                            color="gray"
                            x-bind:disabled="remaining <= 0"
                            x-on:click="copyCode(@js($currentCode))"
                        >
                            <span x-show="!copied">Salin Kode</span>
                            <span x-show="copied" x-cloak>Tersalin!</span>
                        </x-filament::button>

                        @if (\App\Helpers\PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.pos_action_otp.refresh'))
                            <x-filament::button
                                type="button"
                                color="primary"
                                wire:click="refreshOtp"
                                wire:loading.attr="disabled"
                            >
                                Refresh OTP
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-950 dark:text-white">Riwayat Pemakaian OTP Terbaru</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">15 aktivitas verifikasi OTP terakhir untuk Edit/Hapus transaksi POS.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Pengguna</th>
                            <th class="px-6 py-3">Aksi</th>
                            <th class="px-6 py-3">No. Invoice</th>
                            <th class="px-6 py-3">Cabang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($this->recentLogs as $log)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    {{ $log->used_at?->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                    {{ $log->user?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if ($log->action === 'delete_sale')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-danger-50 text-danger-700 dark:bg-danger-950/40 dark:text-danger-400">
                                            Hapus Transaksi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-50 text-warning-700 dark:bg-warning-950/40 dark:text-warning-400">
                                            Edit Transaksi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap font-mono text-xs font-semibold">
                                    {{ $log->invoice_number ?? ('#' . $log->sale_id) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-xs">
                                    {{ $log->branch?->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                    Belum ada riwayat pemakaian OTP.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

