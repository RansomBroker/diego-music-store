<!-- Modal: Kas Keluar -->
<x-pos.modal
    wire:model="showOutModal"
    title="Pengeluaran Kas (Kas Keluar)"
    subtitle="Catat pengeluaran operasional kecil menggunakan uang laci kasir"
    icon="ph-arrow-down-left"
    maxWidth="md"
>
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

        <!-- Destination/Kontra Akun -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kategori Pengeluaran (Beban)</label>
            <select
                wire:model="outDestinationAccountId"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-colors"
            >
                @foreach($outflowDestinations as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                @endforeach
            </select>
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
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button
                type="button"
                wire:click="$set('showOutModal', false)"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
            >
                <i class="ph-bold ph-check text-xs"></i>
                <span>Simpan Transaksi</span>
            </button>
        </div>
    </form>
</x-pos.modal>
