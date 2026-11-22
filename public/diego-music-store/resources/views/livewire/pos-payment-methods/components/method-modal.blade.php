{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Metode Pembayaran' : 'Tambah Metode Pembayaran'"
    :subtitle="$isEditing ? 'Perbarui detail metode pembayaran terpilih' : 'Tambahkan metode pembayaran baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-credit-card'"
    maxWidth="lg"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <!-- Nama Metode Pembayaran -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Metode Pembayaran <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="name"
                wire:keyup="$set('code', require('slugify') ? slugify(name) : name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, ''))"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. QRIS, Transfer Mandiri"
            >
            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Kode Unik -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kode Unik (Slug) <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="code"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                placeholder="e.g. qris, transfer-mandiri"
            >
            @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Kategori / Parent Group (Opsional) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Kategori / Parent Group (Opsional)</label>
            <select
                wire:model.live="parent_id"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="">Tanpa Parent (Metode Utama / Standalone)</option>
                @foreach ($parentOptions as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                @endforeach
            </select>
            <span class="text-[11px] text-slate-400 mt-1 block">Pilih jika metode ini adalah sub-metode (misal: BCA under Debit Card). Kosongkan jika ini adalah Metode Utama atau Standalone (misal: Cash, QRIS).</span>
            @error('parent_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Akun Hubungan (COA) -->
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Akun Akuntansi (COA)</label>
            <select
                wire:model="account_id"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
            >
                <option value="">Pilih Akun Perkiraan...</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
            @error('account_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Status Aktif -->
        <div class="flex items-center gap-2 pt-2">
            <input
                type="checkbox"
                wire:model="is_active"
                id="is_active_checkbox"
                class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary focus:ring-opacity-50"
            >
            <label for="is_active_checkbox" class="text-xs font-semibold text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                Metode Pembayaran Aktif
            </label>
        </div>

        @if ($parent_id)
            <!-- Default Sub-Metode Switch -->
            <div class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-950/40">
                <div>
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">Jadikan Default Group</span>
                    <span class="block text-[10px] text-slate-400 dark:text-slate-500">Otomatis terpilih saat group metode pembayaran ini dipilih di POS</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" wire:model="is_default" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                </label>
            </div>
        @endif

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
                {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Metode' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
