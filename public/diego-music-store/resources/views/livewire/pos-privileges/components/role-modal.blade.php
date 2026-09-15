<!-- Modal Form Tambah / Edit Role -->
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Atur Hak Akses Role: ' . $roleName : 'Tambah Role Baru'"
    subtitle="Kelola modul dan fitur yang dapat diakses oleh role ini"
    icon="ph-shield-check"
    maxWidth="2xl"
>
    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Role <span class="text-rose-500">*</span></label>
            <input
                type="text"
                wire:model="roleName"
                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                placeholder="e.g. Kasir Senior / Supervisor POS"
            >
            @error('roleName') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Permission Matrix Grouped -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Daftar Hak Akses Sistem</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[380px] overflow-y-auto pr-1 no-scrollbar">
                @foreach ($permissionGroups as $groupName => $permissions)
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i class="ph-bold ph-folder-open text-primary"></i>
                                {{ $groupName }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($permissions as $permName => $permLabel)
                                <label class="flex items-center gap-2.5 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/50 p-1.5 rounded-lg transition-colors">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedPermissions"
                                        value="{{ $permName }}"
                                        class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300 dark:border-slate-700 dark:bg-slate-900"
                                    >
                                    <div>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 block">{{ $permLabel }}</span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 ">{{ $permName }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Modal Footer -->
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
                Simpan Role & Hak Akses
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
