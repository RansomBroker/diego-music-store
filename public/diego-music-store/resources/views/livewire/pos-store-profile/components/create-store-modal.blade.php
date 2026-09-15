<!-- Modal Create Store / Cabang -->
<x-pos.modal
    wire:model="showCreateModal"
    title="Register Toko / Cabang Baru"
    subtitle="Daftarkan cabang atau outlet toko baru ke sistem ERP"
    icon="ph-storefront"
    maxWidth="lg"
>
    <form wire:submit.prevent="createStore" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Brand Toko <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="store_name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                placeholder="e.g. Diego Music Store"
            >
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Cabang <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                placeholder="e.g. Cabang Bandung"
            >
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WA</label>
            <input
                type="text"
                wire:model="phone"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                placeholder="e.g. 0822-1111-2222"
            >
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
            <textarea
                wire:model="address"
                rows="2"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
            ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button
                type="button"
                wire:click="$set('showCreateModal', false)"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-lg"
            >
                Register Toko
            </button>
        </div>
    </form>
</x-pos.modal>
