<!-- Modal Otorisasi OTP & Konfirmasi Hapus Transaksi -->
@if ($showOtpActionModal)
    @php
        $requiresOtp = \App\Helpers\PosOtpHelper::requiresOtp(Auth::user());
        $isDelete = $otpActionType === 'delete_sale';
    @endphp
    <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="otp-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeOtpActionModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800">
                <form wire:submit.prevent="submitOtpAction">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $isDelete ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }} flex items-center justify-center">
                                <i class="ph-bold {{ $isDelete ? 'ph-trash' : 'ph-shield-check' }} text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white" id="otp-modal-title">
                                    {{ $isDelete ? 'Hapus Transaksi' : 'Verifikasi OTP Edit Transaksi' }}
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                    Invoice: {{ $otpTargetInvoice }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeOtpActionModal" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        @if ($isDelete)
                            <div class="rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-900/50 p-3.5 text-xs text-rose-700 dark:text-rose-300 leading-relaxed">
                                <p class="font-bold mb-1">Tindakan ini bersifat permanen!</p>
                                <p>Transaksi <strong>{{ $otpTargetInvoice }}</strong> akan dihapus permanen, stok barang fisik akan dikembalikan, dan jurnal akuntansi serta komisi terkait akan dihapus.</p>
                            </div>
                        @else
                            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/50 p-3.5 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                Masukkan kode <strong>OTP 6 digit</strong> dari <strong>Dashboard Backoffice</strong> untuk melanjutkan edit transaksi <strong>{{ $otpTargetInvoice }}</strong>.
                            </div>
                        @endif

                        @if ($requiresOtp)
                            <div>
                                <label for="pos-otp-code" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                    Kode OTP (6 Digit)
                                </label>
                                <input
                                    id="pos-otp-code"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    wire:model="otpCode"
                                    placeholder="000000"
                                    class="w-full px-4 py-3 text-center font-mono text-2xl font-black tracking-[0.35em] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary"
                                    autofocus
                                />
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                                    Minta kode OTP aktif (berlaku 1 menit) kepada Owner.
                                </p>
                            </div>
                        @endif

                        @if ($otpError)
                            <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 px-3.5 py-2.5 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                {{ $otpError }}
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                        <x-pos.utility.button
                            type="button"
                            size="sm"
                            variant="secondary"
                            wire:click="closeOtpActionModal"
                        >
                            Batal
                        </x-pos.utility.button>
                        <x-pos.utility.button
                            type="submit"
                            size="sm"
                            :variant="$isDelete ? 'danger' : 'warning'"
                            :icon="$isDelete ? 'ph-trash' : 'ph-check-circle'"
                        >
                            {{ $isDelete ? 'Ya, Hapus' : 'Verifikasi & Edit' }}
                        </x-pos.utility.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

