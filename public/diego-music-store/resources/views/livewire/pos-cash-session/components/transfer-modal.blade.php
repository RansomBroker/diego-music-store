<!-- Modal: Setor / Transfer Kas dari Halaman Tutup Sesi -->
<x-pos.modal
    wire:model="showTransferModal"
    title="Setor / Transfer Kas"
    subtitle="Pindahkan uang tunai dari laci kasir ke Kas Kecil / Kas Besar / Bank sebelum tutup shift"
    icon="ph-arrows-left-right"
    maxWidth="md"
>
    @php
        $transferOptions = collect($transferAccounts)->map(fn ($acc) => [
            'value' => (string) $acc->id,
            'label' => (string) $acc->name,
            'sublabel' => (string) $acc->code,
        ])->values()->all();
    @endphp
    <form wire:submit.prevent="saveTransfer" class="space-y-4">
        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-900/50 flex items-center justify-between text-xs">
            <span class="font-semibold text-blue-700 dark:text-blue-300">Saldo Teoritis Laci Saat Ini</span>
            <span class="font-black text-blue-900 dark:text-blue-200">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Nominal Setor (Rp) <span class="text-rose-500">*</span></label>
                @if ($cashSales > 0 && $cashSales <= $expectedCash)
                    <button
                        type="button"
                        wire:click="$set('transferAmount', {{ $cashSales }})"
                        class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                    >
                        Sesuai Penjualan Tunai (Rp {{ number_format($cashSales, 0, ',', '.') }})
                    </button>
                @endif
            </div>
            <x-money-input
                wire:model.live="transferAmount"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors font-bold"
                placeholder="0"
            />
            @error('transferAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Dari Akun Kas</label>
            <x-pos.form.searchable-select
                model="transferSourceAccountId"
                :options="$transferOptions"
                placeholder="-- Pilih Akun Kas Asal --"
                searchPlaceholder="Cari akun kas asal..."
                accent="blue"
            />
            @error('transferSourceAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tujuan Setor (Kas / Bank)</label>
            <x-pos.form.searchable-select
                model="transferDestinationAccountId"
                :options="$transferOptions"
                placeholder="-- Pilih Akun Tujuan --"
                searchPlaceholder="Cari akun kas / bank tujuan..."
                accent="blue"
            />
            @error('transferDestinationAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Keterangan (Opsional)</label>
            <textarea
                wire:model="transferNotes"
                rows="2"
                placeholder="Contoh: Setor hasil penjualan tunai ke Kas Kecil sebelum tutup shift"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors resize-none"
            ></textarea>
            @error('transferNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                size="sm"
                variant="secondary"
                wire:click="$set('showTransferModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                size="sm"
                variant="primary"
                icon="ph-check"
            >
                Simpan Setor Kas
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
