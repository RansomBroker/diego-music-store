{{-- ===================== REUSABLE MODAL: KONFIRMASI HAPUS ===================== --}}
<x-pos.modal
    wire:model="showDeleteModal"
    title="Hapus User"
    subtitle="Konfirmasi penghapusan data secara permanen"
    icon="ph-trash"
    maxWidth="sm"
>
    <div class="text-center space-y-4">
        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
            Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
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
                Ya, Hapus
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>
