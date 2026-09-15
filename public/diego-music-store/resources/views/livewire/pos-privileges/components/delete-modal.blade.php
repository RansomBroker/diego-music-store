<!-- Modal Delete Confirmation -->
<x-pos.modal
    wire:model="showDeleteModal"
    title="Hapus Role"
    subtitle="Konfirmasi hapus role hak akses"
    icon="ph-trash"
    maxWidth="sm"
>
    <div class="text-center space-y-4">
        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
            Apakah Anda yakin ingin menghapus role ini? User yang memiliki role ini tidak akan lagi memiliki hak akses terkait.
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
                class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
            >
                Hapus Role
            </button>
        </div>
    </div>
</x-pos.modal>
