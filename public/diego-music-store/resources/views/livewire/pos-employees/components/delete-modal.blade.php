<!-- Modal Delete Confirmation -->
<x-pos.modal
    wire:model="showDeleteModal"
    title="Hapus Data Karyawan"
    subtitle="Konfirmasi tindakan penghapusan data"
    icon="ph-warning-octagon"
    maxWidth="sm"
>
    <div class="py-2 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
            <i class="ph-bold ph-warning text-2xl"></i>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Apakah Anda yakin?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Data karyawan yang dihapus akan dipindahkan ke tempat sampah (Soft Delete) dan dapat dipulihkan jika diperlukan.
            </p>
        </div>
        <div class="flex items-center justify-center gap-3 pt-2">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                size="sm"
                wire:click="$set('showDeleteModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="button"
                variant="danger"
                size="sm"
                icon="ph-trash"
                wire:click="delete"
            >
                Hapus
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>
