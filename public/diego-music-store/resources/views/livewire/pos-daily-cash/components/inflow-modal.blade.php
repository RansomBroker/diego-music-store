<!-- Modal: Kas Masuk -->
<x-pos.modal
    wire:model="showInModal"
    title="Pemasukan Kas (Kas Masuk)"
    subtitle="Catat penambahan modal atau dana masuk lainnya ke laci kasir"
    icon="ph-arrow-up-right"
    maxWidth="md"
>
    @php
        $inflowOptions = collect($inflowSources)->map(fn ($acc) => [
            'value' => (string) $acc->id,
            'label' => (string) $acc->name,
            'sublabel' => (string) $acc->code,
        ])->values()->all();
    @endphp
    <!-- Form Body -->
    <form wire:submit.prevent="saveInflow" class="space-y-4">
        <!-- Nominal -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Uang (Rp) <span class="text-rose-500">*</span></label>
            <x-money-input
                wire:model.live="inAmount"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-colors font-bold"
                placeholder="0"
            />
            @error('inAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Source/Kontra Akun (Searchable Dropdown Teleported) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sumber Pemasukan Kas (Pendapatan / Modal)</label>
            <x-pos.form.searchable-select
                model="inSourceAccountId"
                :options="$inflowOptions"
                placeholder="-- Pilih Akun Pemasukan --"
                searchPlaceholder="Cari nama atau kode akun..."
                accent="emerald"
            />
            @error('inSourceAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Alasan</label>
            <textarea
                wire:model="inNotes"
                rows="3"
                placeholder="Contoh: Tambahan modal laci kasir, Pengembalian dana belanja ATK, dll."
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-colors resize-none"
            ></textarea>
            @error('inNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Form Footer Actions -->
        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                size="sm"
                variant="secondary"
                wire:click="$set('showInModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                size="sm"
                variant="success"
                icon="ph-check"
            >
                Simpan Transaksi
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
