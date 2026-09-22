{{-- ===================== MODAL: TAGIHAN WHATSAPP ===================== --}}
<x-pos.modal
    wire:model="showBillingModal"
    title="Kirim Tagihan WhatsApp"
    subtitle="Kirim pengingat tagihan dan rincian piutang ke WhatsApp pelanggan secara langsung"
    icon="ph-receipt"
    maxWidth="lg"
>
    @if ($billingCustomer)
        <div class="space-y-4">
            {{-- Info Ringkasan Pelanggan & Piutang --}}
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-400">Nama Pelanggan</div>
                        <div class="font-bold text-slate-900 dark:text-white text-base">{{ $billingCustomer->name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-400">No. WhatsApp</div>
                        <div class="font-semibold text-slate-700 dark:text-slate-200 text-sm">{{ $billingCustomer->phone ?? 'Belum ada nomor' }}</div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 dark:border-slate-750 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Piutang Belum Lunas:</span>
                    <span class="text-base font-extrabold text-rose-600 dark:text-rose-400">
                        Rp {{ number_format($billingCustomer->total_piutang, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Preview Pesan --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Pratinjau Pesan Tagihan
                </label>
                <div class="p-3.5 rounded-lg bg-emerald-50/40 dark:bg-slate-950 border border-emerald-100 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 font-mono whitespace-pre-line leading-relaxed max-h-56 overflow-y-auto">
                    {{ $billingMessagePreview }}
                </div>
            </div>

            @if (empty($billingCustomer->phone))
                <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-lg flex items-center gap-2 text-xs text-amber-800 dark:text-amber-300">
                    <i class="ph-bold ph-warning text-base flex-shrink-0"></i>
                    <span>Pelanggan ini belum memiliki nomor telepon/WhatsApp. Mohon perbarui profil pelanggan terlebih dahulu.</span>
                </div>
            @endif

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="$set('showBillingModal', false)"
                >
                    Batal
                </x-pos.utility.button>

                <button
                    type="button"
                    wire:click="sendBillingReminder"
                    wire:loading.attr="disabled"
                    @disabled(empty($billingCustomer->phone))
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                >
                    <span wire:loading.remove wire:target="sendBillingReminder" class="inline-flex items-center gap-1.5">
                        <i class="ph-bold ph-paper-plane-tilt text-base"></i>
                        Kirim Tagihan WA
                    </span>
                    <span wire:loading wire:target="sendBillingReminder" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mengirim...
                    </span>
                </button>
            </div>
        </div>
    @endif
</x-pos.modal>
