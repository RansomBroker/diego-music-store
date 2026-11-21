<!-- Role Create & Edit Privilege Modal -->
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Atur Hak Akses Role: ' . $roleName : 'Tambah Role Baru'"
    subtitle="Aktifkan akses panel FO / BO dan centang fitur yang boleh diakses oleh role ini"
    icon="ph-shield-check"
    maxWidth="7xl"
>
    <form wire:submit="save" class="space-y-5">
        <!-- Role Name & Live Panel Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
            <div class="lg:col-span-2">
                <x-pos.form.input
                    label="Nama Role"
                    model="roleName"
                    placeholder="Contoh: Kepala Toko, Kasir Shift 1, Admin Keuangan..."
                    required
                />
            </div>

            <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Ringkasan Akses</div>
                <div class="text-sm font-black text-slate-900 dark:text-white mt-0.5">{{ $panelSummary['label'] }}</div>
                <div class="flex items-center gap-2 mt-1.5 text-xs">
                    <span class="px-2 py-0.5 rounded-md font-semibold {{ $accessFo ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                        FO: {{ $panelSummary['fo_count'] }}/{{ $panelSummary['fo_total'] }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md font-semibold {{ $accessBo ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                        BO: {{ $panelSummary['bo_count'] }}/{{ $panelSummary['bo_total'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2 Panel Gate Toggles -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="flex items-start justify-between gap-3 p-4 rounded-2xl border cursor-pointer transition {{ $accessFo ? 'border-blue-400 bg-blue-50/60 dark:border-blue-700 dark:bg-blue-950/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                <div>
                    <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="ph-bold ph-storefront text-blue-600 dark:text-blue-400"></i>
                        <span>Akses Panel Front Office (FO)</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Izinkan login dan mengakses halaman kasir &amp; operasional Front Office (/pos).
                    </p>
                </div>
                <input type="checkbox" wire:model.live="accessFo" class="mt-1 h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
            </label>

            <label class="flex items-start justify-between gap-3 p-4 rounded-2xl border cursor-pointer transition {{ $accessBo ? 'border-amber-400 bg-amber-50/60 dark:border-amber-700 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                <div>
                    <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="ph-bold ph-buildings text-amber-600 dark:text-amber-400"></i>
                        <span>Akses Panel Backoffice (BO)</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Izinkan mengakses admin Backoffice, inventory gudang, pembelian, dan akuntansi (/backoffice).
                    </p>
                </div>
                <input type="checkbox" wire:model.live="accessBo" class="mt-1 h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
            </label>
        </div>

        <!-- Synchronized 2-Section Tabs + Feature Matrix -->
        @include('livewire.pos-privileges.components.permission-matrix')

        <!-- Footer -->
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
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
                icon="ph-floppy-disk"
            >
                {{ $isEditing ? 'Simpan Hak Akses' : 'Buat Role' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
