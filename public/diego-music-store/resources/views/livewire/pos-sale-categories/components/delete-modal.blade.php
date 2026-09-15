{{-- ===================== REUSABLE MODAL: KONFIRMASI HAPUS ===================== --}}
<x-pos.modal
    wire:model="showDeleteModal"
    title="Hapus Kategori Penjualan"
    subtitle="Konfirmasi penghapusan data secara permanen"
    icon="ph-trash"
    maxWidth="sm"
>
    <div class="text-center space-y-4">
        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
            Apakah Anda yakin ingin menghapus kategori penjualan ini? Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex gap-3 pt-2">
            <button
                wire:click="$set('showDeleteModal', false)"
                class="flex-1 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                wire:click="destroy"
                class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
            >
                Ya, Hapus
            </button>
        </div>
    </div>
</x-pos.modal>
