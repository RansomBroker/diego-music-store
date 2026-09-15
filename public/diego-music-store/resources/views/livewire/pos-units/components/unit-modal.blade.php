{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Satuan Barang' : 'Tambah Satuan Barang'"
    :subtitle="$isEditing ? 'Perbarui detail data satuan barang terpilih' : 'Tambahkan data satuan barang baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-ruler'"
    maxWidth="lg"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <!-- Nama Satuan -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Satuan <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. Pieces, Box, Karton"
            >
            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Kode Satuan -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kode Satuan <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="code"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. pcs, box, krt"
            >
            @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Satuan Dasar Acuan -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Satuan Dasar Acuan (Base Unit)</label>
            <select
                wire:model.live="base_unit_id"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="">Pilih jika ini Satuan Konversi/Besar (misal: pcs)</option>
                @foreach ($baseUnitsList as $unitItem)
                    <option value="{{ $unitItem->id }}">{{ $unitItem->name }} ({{ $unitItem->code }})</option>
                @endforeach
            </select>
            @error('base_unit_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Faktor Konversi -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Faktor Konversi (Jumlah Satuan Dasar)</label>
            <input
                type="number"
                wire:model="conversion_factor"
                @disabled(blank($base_unit_id))
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors disabled:opacity-50 disabled:bg-slate-50 dark:disabled:bg-slate-900"
                placeholder="e.g. 12"
                min="1"
            >
            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                Misal: jika diisi 12 untuk Karton dengan acuan pcs, artinya 1 Karton = 12 pcs.
            </span>
            @error('conversion_factor') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Status Aktif Switch -->
        <div class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-950/40 mt-4">
            <div>
                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">Status Aktif</span>
                <span class="block text-[10px] text-slate-450 dark:text-slate-500">Aktifkan atau nonaktifkan satuan</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
            </label>
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                variant="primary"
                icon="ph-check"
            >
                {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Satuan' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
