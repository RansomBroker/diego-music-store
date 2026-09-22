{{-- ===================== MODAL: KIRIM PESAN WHATSAPP KHUSUS PELANGGAN INI ===================== --}}
<x-pos.modal
    wire:model="showCustomerMessageModal"
    title="Kirim Pesan WhatsApp"
    :subtitle="'Kirim pesan promo, diskon, atau informasi ke ' . ($selectedCustomer?->name ?? 'Pelanggan') . ' secara langsung'"
    icon="ph-whatsapp-logo"
    maxWidth="lg"
>
    @if ($selectedCustomer)
        <form wire:submit.prevent="sendCustomerMessage" class="space-y-4">
            {{-- Info Ringkas Pelanggan --}}
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Penerima:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $selectedCustomer->name }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">No. WhatsApp:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $selectedCustomer->phone ?: 'Belum ada nomor' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Poin Loyalitas:</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($selectedCustomer->loyalty_points ?? 0) }} Poin</span>
                </div>
                @if ($selectedCustomer->total_piutang > 0)
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-750">
                        <span class="text-slate-500 dark:text-slate-400">Sisa Piutang:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($selectedCustomer->total_piutang, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            @if (empty($selectedCustomer->phone))
                <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 rounded-lg flex items-center gap-2 text-xs text-amber-800 dark:text-amber-300">
                    <i class="ph-bold ph-warning text-base flex-shrink-0"></i>
                    <span>Pelanggan ini belum memiliki nomor telepon/WhatsApp. Mohon perbarui data pelanggan terlebih dahulu.</span>
                </div>
            @endif

            {{-- Template Cepat --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Pesan WhatsApp
                    </label>
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="text-[10px] text-slate-400 mr-0.5">Template:</span>
                        <button
                            type="button"
                            wire:click="applyCustomerTemplate('promo')"
                            class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800/40 transition"
                        >
                            Promo
                        </button>
                        <button
                            type="button"
                            wire:click="applyCustomerTemplate('sale')"
                            class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 hover:bg-blue-100 border border-blue-200 dark:border-blue-800/40 transition"
                        >
                            Sale
                        </button>
                        <button
                            type="button"
                            wire:click="applyCustomerTemplate('member')"
                            class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 hover:bg-amber-100 border border-amber-200 dark:border-amber-800/40 transition"
                        >
                            Poin
                        </button>
                    </div>
                </div>

                {{-- Textarea Pesan --}}
                <textarea
                    wire:model="customerMessage"
                    rows="6"
                    placeholder="Tulis pesan untuk pelanggan ini..."
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-sans"
                ></textarea>
                @error('customerMessage') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Info Pengiriman Langsung --}}
            <div class="p-2.5 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-lg flex items-start gap-2">
                <i class="ph-bold ph-paper-plane-tilt text-emerald-600 dark:text-emerald-400 text-sm mt-0.5 flex-shrink-0"></i>
                <p class="text-[11px] text-emerald-800 dark:text-emerald-300 leading-snug">
                    Pesan akan dikirim <strong>secara langsung (synchronous)</strong> ke nomor pelanggan tersebut menggunakan akun WhatsApp cabang aktif.
                </p>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="$set('showCustomerMessageModal', false)"
                >
                    Batal
                </x-pos.utility.button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    @disabled(empty($selectedCustomer->phone))
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
                >
                    <span wire:loading.remove wire:target="sendCustomerMessage" class="inline-flex items-center gap-1.5">
                        <i class="ph-bold ph-paper-plane-tilt text-base"></i>
                        Kirim WA
                    </span>
                    <span wire:loading wire:target="sendCustomerMessage" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mengirim...
                    </span>
                </button>
            </div>
        </form>
    @endif
</x-pos.modal>
