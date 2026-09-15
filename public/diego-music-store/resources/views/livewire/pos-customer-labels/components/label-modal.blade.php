{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Kategori Penjualan' : 'Tambah Kategori Penjualan'"
    :subtitle="$isEditing ? 'Perbarui detail kategori penjualan terpilih' : 'Tambahkan kategori penjualan baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-tag'"
    maxWidth="lg"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <!-- Key / Code -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Key / Code <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="key"
                @disabled($isEditing)
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors disabled:opacity-50 disabled:bg-slate-50 dark:disabled:bg-slate-900"
                placeholder="e.g. perorangan, instansi"
            >
            @error('key') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Nama Label / Kategori -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Nama Kategori <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. Perorangan, Instansi / Lembaga"
            >
            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-slate-800">
            <button
                type="button"
                wire:click="$set('showModal', false)"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
            >
                <i class="ph-bold ph-check text-xs"></i>
                <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
            </button>
        </div>
    </form>
</x-pos.modal>
