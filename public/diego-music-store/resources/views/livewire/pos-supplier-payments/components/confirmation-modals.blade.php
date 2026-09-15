{{-- ===================== CONFIRMATION: POST DRAFT PAYMENT ===================== --}}
<x-pos.modal
    wire:model="showPostConfirmation"
    title="Posting Pelunasan Hutang"
    subtitle="Konfirmasi posting transaksi pelunasan hutang"
    icon="ph-warning"
    maxWidth="md"
>
    <div class="space-y-6">
        <p class="text-sm text-slate-600 dark:text-slate-300">
            Apakah Anda yakin ingin memposting pelunasan hutang ini? Transaksi ini akan memperbarui hutang supplier secara permanen dan mencatat jurnal akuntansi secara otomatis.
        </p>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showPostConfirmation', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="button"
                variant="success"
                wire:click="postPayment"
                icon="ph-check"
            >
                Ya, Posting
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>

{{-- ===================== CONFIRMATION: DELETE DRAFT PAYMENT ===================== --}}
<x-pos.modal
    wire:model="showDeleteConfirmation"
    title="Hapus Draft Pelunasan"
    subtitle="Hapus transaksi draft secara permanen"
    icon="ph-trash"
    maxWidth="md"
>
    <div class="space-y-6">
        <p class="text-sm text-slate-600 dark:text-slate-300">
            Apakah Anda yakin ingin menghapus draft pelunasan hutang ini secara permanen? Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showDeleteConfirmation', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="button"
                variant="danger"
                wire:click="deletePayment"
                icon="ph-trash"
            >
                Ya, Hapus
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>
