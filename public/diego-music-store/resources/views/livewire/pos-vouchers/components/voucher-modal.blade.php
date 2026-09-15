<!-- Modal Form Create / Edit -->
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Voucher' : 'Tambah Voucher Baru'"
    :subtitle="$isEditing ? 'Perbarui detail dan syarat voucher' : 'Buat voucher promo diskon baru'"
    icon="ph-ticket"
    maxWidth="lg"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Kode Voucher -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="code"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm uppercase font-bold text-slate-900 dark:text-white"
                    placeholder="e.g. PROMO50K"
                >
                @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tipe Diskon -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Tipe Diskon <span class="text-rose-500">*</span></label>
                <select wire:model="type" class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white">
                    <option value="fixed">Nominal Tetap (Rp)</option>
                    <option value="percent">Persentase (%)</option>
                </select>
            </div>
        </div>

        <!-- Nama Voucher -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Nama / Deskripsi Voucher <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                placeholder="e.g. Voucher Diskon Rp 50.000 Promo Grand Opening"
            >
            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nilai Diskon -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                    Nilai Diskon {{ $type === 'fixed' ? '(Rp)' : '(%)' }} <span class="text-rose-500">*</span>
                </label>
                @if($type === 'fixed')
                    <x-money-input
                        wire:model.live="value"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-900 dark:text-white"
                        placeholder="0"
                    />
                @else
                    <input
                        type="number"
                        wire:model.live="value"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-900 dark:text-white"
                        placeholder="0"
                    >
                @endif
                @error('value') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Min Spend -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Min. Belanja (Rp)</label>
                <x-money-input
                    wire:model.live="min_spend"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-900 dark:text-white"
                    placeholder="0"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Valid Until -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Masa Berlaku (Kadaluarsa)</label>
                <input
                    type="datetime-local"
                    wire:model="valid_until"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white"
                >
            </div>

            <!-- Max Uses -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kuota Maksimal Penggunaan</label>
                <input
                    type="number"
                    wire:model="max_uses"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                    placeholder="Kosongkan jika tak terbatas"
                >
            </div>
        </div>

        <!-- Status Aktif -->
        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" wire:model="is_active" id="vch_active" class="rounded border-slate-300 text-primary">
            <label for="vch_active" class="text-xs font-semibold text-slate-600 dark:text-slate-400 cursor-pointer">Voucher Aktif</label>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-primary hover:bg-primary-dark rounded-lg shadow-sm transition duration-150 cursor-pointer">
                Simpan Voucher
            </button>
        </div>
    </form>
</x-pos.modal>
