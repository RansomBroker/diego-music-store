{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Kategori Penjualan' : 'Tambah Kategori Penjualan'"
    :subtitle="$isEditing ? 'Perbarui detail kategori penjualan terpilih' : 'Tambahkan kategori penjualan baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-storefront'"
    maxWidth="lg"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <!-- Nama Kategori -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Nama Kategori <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. Store, Online, WhatsApp"
            >
            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Prefix Invoice -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Prefix Invoice</label>
                <input
                    type="text"
                    wire:model="prefix"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors uppercase"
                    placeholder="e.g. STR, WA"
                >
                @error('prefix') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Panjang Digit -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Panjang Digit <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    wire:model="digit_length"
                    min="3" max="10"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                <p class="text-[10px] text-slate-400 mt-1.5 leading-tight">Mendukung format auto-alfanumerik dinamis jika melampaui limit 9999.</p>
                @error('digit_length') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Start Alphabet -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Abjad Awal (Mulai)</label>
                <input
                    type="text"
                    wire:model="start_alphabet"
                    maxlength="5"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors uppercase"
                    placeholder="e.g. A"
                >
                @error('start_alphabet') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- End Alphabet -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Abjad Akhir (Batas)</label>
                <input
                    type="text"
                    wire:model="end_alphabet"
                    maxlength="5"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors uppercase"
                    placeholder="e.g. Z"
                >
                <p class="text-[10px] text-slate-400 mt-1.5 leading-tight">Transaksi otomatis ditolak jika sudah melewati batas abjad ini.</p>
                @error('end_alphabet') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
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
                {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Kategori' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
