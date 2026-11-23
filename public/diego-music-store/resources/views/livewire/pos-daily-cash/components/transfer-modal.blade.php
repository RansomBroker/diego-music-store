<!-- Modal: Setor / Transfer Kas (Akun Terkunci: KAS Kecil -> KAS Besar) -->
<x-pos.modal
    wire:model="showTransferModal"
    title="Setor / Transfer Kas"
    subtitle="Pindahkan uang tunai dari laci kasir (Kas Kecil) ke Kas Besar"
    icon="ph-arrows-left-right"
    maxWidth="md"
>
    @php
        $sourceAcc = collect($transferAccounts)->firstWhere('id', $transferSourceAccountId);
        $destAcc = collect($transferAccounts)->firstWhere('id', $transferDestinationAccountId);
    @endphp
    <form wire:submit.prevent="saveTransfer" class="space-y-4">
        <!-- Info Saldo Laci Saat Ini -->
        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-900/50 flex items-center justify-between text-xs">
            <span class="font-semibold text-blue-700 dark:text-blue-300">Saldo Teoritis Laci Saat Ini</span>
            <span class="font-black text-blue-900 dark:text-blue-200">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
        </div>

        <!-- Nominal -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Setor (Rp) <span class="text-rose-500">*</span></label>
            <x-money-input
                wire:model.live="transferAmount"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors font-bold"
                placeholder="0"
            />
            @error('transferAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Dari Akun (Terkunci) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Dari Akun Kas</label>
            <div class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 flex items-center justify-between gap-2 select-none">
                <span class="font-semibold truncate">
                    {{ $sourceAcc ? "{$sourceAcc->name} ({$sourceAcc->code})" : 'KAS Kecil (111101002)' }}
                </span>
                <i class="ph-bold ph-lock text-slate-400 text-sm flex-shrink-0" title="Akun dikunci oleh sistem"></i>
            </div>
            @error('transferSourceAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Ke Akun (Terkunci) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tujuan Setor</label>
            <div class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 flex items-center justify-between gap-2 select-none">
                <span class="font-semibold truncate">
                    {{ $destAcc ? "{$destAcc->name} ({$destAcc->code})" : 'KAS (111101001)' }}
                </span>
                <i class="ph-bold ph-lock text-slate-400 text-sm flex-shrink-0" title="Akun dikunci oleh sistem"></i>
            </div>
            @error('transferDestinationAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Catatan -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Keterangan (Opsional)</label>
            <textarea
                wire:model="transferNotes"
                rows="2"
                placeholder="Contoh: Setor hasil penjualan tunai ke Kas Besar sebelum tutup kasir"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors resize-none"
            ></textarea>
            @error('transferNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Form Footer Actions -->
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
