<!-- Modal: Kas Keluar -->
<x-pos.modal
    wire:model="showOutModal"
    title="Pengeluaran Kas (Kas Keluar)"
    subtitle="Catat pengeluaran operasional kecil menggunakan uang laci kasir"
    icon="ph-arrow-down-left"
    maxWidth="md"
>
    @php
        $outflowOptions = collect($outflowDestinations)->map(fn ($acc) => [
            'value' => (string) $acc->id,
            'label' => (string) $acc->name,
            'sublabel' => (string) $acc->code,
        ])->values()->all();
    @endphp
    <!-- Form Body -->
    <form wire:submit.prevent="saveOutflow" class="space-y-4">
        <!-- Nominal -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Uang (Rp) <span class="text-rose-500">*</span></label>
            <x-money-input
                wire:model.live="outAmount"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors font-bold"
                placeholder="0"
            />
            @error('outAmount') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Destination/Kontra Akun (Searchable Dropdown Teleported) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kategori Pengeluaran (Beban)</label>
            <x-pos.form.searchable-select
                model="outDestinationAccountId"
                :options="$outflowOptions"
                placeholder="-- Pilih Akun Beban --"
                searchPlaceholder="Cari nama atau kode beban..."
                accent="rose"
            />
            @error('outDestinationAccountId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan / Keterangan Belanja</label>
            <textarea
                wire:model="outNotes"
                rows="3"
                placeholder="Contoh: Beli air galon aqua toko, Uang parkir kurir, Beli stop kontak, dll."
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors resize-none"
            ></textarea>
            @error('outNotes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Form Footer Actions -->
        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                size="sm"
                variant="secondary"
                wire:click="$set('showOutModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                size="sm"
                variant="danger"
                icon="ph-check"
            >
                Simpan Transaksi
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
