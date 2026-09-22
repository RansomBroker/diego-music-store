{{-- ===================== MODAL: BROADCAST WHATSAPP ===================== --}}
<x-pos.modal
    wire:model="showBroadcastModal"
    title="Broadcast WhatsApp"
    subtitle="Kirim info promo, diskon, atau program sale ke pelanggan & member secara langsung"
    icon="ph-megaphone"
    maxWidth="2xl"
>
    <form wire:submit.prevent="sendBroadcast" class="space-y-5">
        {{-- Pilihan Target Penerima --}}
        <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Target Penerima Broadcast
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-all {{ $broadcastTarget === 'all' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300' }}">
                    <input type="radio" wire:model.live="broadcastTarget" value="all" class="text-emerald-600 focus:ring-emerald-500 mr-2.5">
                    <span class="text-xs font-semibold">Semua Pelanggan</span>
                </label>

                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-all {{ $broadcastTarget === 'loyalty_members' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300' }}">
                    <input type="radio" wire:model.live="broadcastTarget" value="loyalty_members" class="text-emerald-600 focus:ring-emerald-500 mr-2.5">
                    <span class="text-xs font-semibold">Khusus Member Loyalitas</span>
                </label>

                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-all {{ $broadcastTarget === 'label' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300' }}">
                    <input type="radio" wire:model.live="broadcastTarget" value="label" class="text-emerald-600 focus:ring-emerald-500 mr-2.5">
                    <span class="text-xs font-semibold">Filter Berdasarkan Label</span>
                </label>

                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-all {{ $broadcastTarget === 'tier' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-200' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300' }}">
                    <input type="radio" wire:model.live="broadcastTarget" value="tier" class="text-emerald-600 focus:ring-emerald-500 mr-2.5">
                    <span class="text-xs font-semibold">Filter Pricing Tier</span>
                </label>
            </div>

            {{-- Conditional Filters --}}
            @if ($broadcastTarget === 'label')
                <div class="pt-1">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pilih Label Pelanggan</label>
                    <select
                        wire:model.live="broadcastLabelId"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                    >
                        <option value="">-- Pilih Label --</option>
                        @foreach ($labels as $lbl)
                            <option value="{{ $lbl->id }}">{{ $lbl->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif ($broadcastTarget === 'tier')
                <div class="pt-1">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pilih Pricing Tier</label>
                    <select
                        wire:model.live="broadcastTierId"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                    >
                        <option value="">-- Pilih Pricing Tier --</option>
                        @foreach ($pricingTiers as $tier)
                            <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Estimasi Jumlah Penerima --}}
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800/60 text-slate-650 dark:text-slate-400 text-xs">
                <i class="ph-bold ph-users text-emerald-600 dark:text-emerald-400 text-sm"></i>
                <span>Estimasi Penerima Siap Kirim (memiliki No. WhatsApp): <strong>{{ $this->estimatedRecipientCount }} pelanggan</strong></span>
            </div>
        </div>

        {{-- Template Cepat --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Pesan Broadcast
                </label>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-slate-400">Template Cepat:</span>
                    <button
                        type="button"
                        wire:click="applyTemplate('promo')"
                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800/40 transition"
                    >
                        Promo Diskon
                    </button>
                    <button
                        type="button"
                        wire:click="applyTemplate('sale')"
                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 hover:bg-blue-100 border border-blue-200 dark:border-blue-800/40 transition"
                    >
                        Weekend Sale
                    </button>
                    <button
                        type="button"
                        wire:click="applyTemplate('member')"
                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 hover:bg-amber-100 border border-amber-200 dark:border-amber-800/40 transition"
                    >
                        Poin Reward
                    </button>
                </div>
            </div>

            {{-- Textarea Pesan --}}
            <textarea
                wire:model="broadcastMessage"
                rows="6"
                placeholder="Tulis pesan promosi atau diskon Anda di sini..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-sans"
            ></textarea>
            @error('broadcastMessage') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

            {{-- Dynamic Tag Chips --}}
            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                <span class="text-[11px] text-slate-400 mr-1">Sisipkan tag:</span>
                <button
                    type="button"
                    wire:click="insertTag('{nama}')"
                    class="px-2 py-0.5 text-xs rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    title="Sisipkan Nama Pelanggan"
                >
                    + {nama}
                </button>
                <button
                    type="button"
                    wire:click="insertTag('{toko}')"
                    class="px-2 py-0.5 text-xs rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    title="Sisipkan Nama Cabang Toko"
                >
                    + {toko}
                </button>
                <button
                    type="button"
                    wire:click="insertTag('{poin}')"
                    class="px-2 py-0.5 text-xs rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                    title="Sisipkan Poin Loyalitas Pelanggan"
                >
                    + {poin}
                </button>
            </div>
        </div>

        {{-- Info Banner Synchronous Direct Delivery --}}
        <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-lg flex items-start gap-2.5">
            <i class="ph-bold ph-paper-plane-tilt text-emerald-600 dark:text-emerald-400 text-base mt-0.5 flex-shrink-0"></i>
            <p class="text-[11px] text-emerald-800 dark:text-emerald-300 leading-relaxed">
                Pesan akan dikirimkan <strong>secara langsung (synchronous)</strong> melalui layanan WhatsApp Fonnte pada cabang aktif. Pastikan perangkat WhatsApp cabang berstatus terhubung.
            </p>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showBroadcastModal', false)"
            >
                Batal
            </x-pos.utility.button>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm"
            >
                <span wire:loading.remove wire:target="sendBroadcast" class="inline-flex items-center gap-1.5">
                    <i class="ph-bold ph-paper-plane-tilt text-base"></i>
                    Kirim Broadcast Sekarang
                </span>
                <span wire:loading wire:target="sendBroadcast" class="inline-flex items-center gap-1.5">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mengirim WhatsApp Langsung...
                </span>
            </button>
        </div>
    </form>
</x-pos.modal>
