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
            <button
                type="button"
                wire:click="$set('showPostConfirmation', false)"
                class="px-4 py-2 border border-slate-350 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
                Batal
            </button>
            <button
                type="button"
                wire:click="postPayment"
                class="px-4 py-2 bg-emerald-650 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md transition duration-150"
            >
                Ya, Posting
            </button>
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
            <button
                type="button"
                wire:click="$set('showDeleteConfirmation', false)"
                class="px-4 py-2 border border-slate-350 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
                Batal
            </button>
            <button
                type="button"
                wire:click="deletePayment"
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-md transition duration-150"
            >
                Ya, Hapus
            </button>
        </div>
    </div>
</x-pos.modal>
