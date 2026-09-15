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
            <x-pos.utility.button
                type="button"
                variant="secondary"
                class="flex-1 justify-center"
                wire:click="$set('showDeleteModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="button"
                variant="danger"
                class="flex-1 justify-center"
                icon="ph-trash"
                wire:click="destroy"
            >
                Hapus Role
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>
