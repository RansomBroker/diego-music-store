{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah User' : 'Tambah User'"
    :subtitle="$isEditing ? 'Perbarui detail data user terpilih' : 'Tambahkan data user baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-user-plus'"
    maxWidth="2xl"
>
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Kolom Kiri: Detail User --}}
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Detail Akun</h4>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. Administrator"
                    >
                    @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="username"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. admin_diego"
                    >
                    @error('username') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                    <input
                        type="email"
                        wire:model="email"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. admin@diegomusic.com"
                    >
                    @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                        Password
                        @if (!$isEditing)
                            <span class="text-rose-500">*</span>
                        @else
                            <span class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span>
                        @endif
                    </label>
                    <input
                        type="password"
                        wire:model="password"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="••••••••"
                    >
                    @error('password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Status Switch -->
                <div class="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-950/40 mt-4">
                    <div>
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">Status Aktif</span>
                        <span class="block text-[10px] text-slate-450 dark:text-slate-500">Aktifkan atau nonaktifkan user</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>

            {{-- Kolom Kanan: Cabang & Role (Multiple Checklist) --}}
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Akses & Hak Akses</h4>

                <!-- Assigned Branches -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Assigned Branches</label>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-950 p-3 max-h-36 overflow-y-auto space-y-2">
                        @foreach ($branchesList as $branch)
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="selectedBranches"
                                    value="{{ $branch->id }}"
                                    class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300 dark:border-slate-700 dark:bg-slate-900"
                                >
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $branch->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Roles</label>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-950 p-3 max-h-36 overflow-y-auto space-y-2">
                        @foreach ($rolesList as $role)
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                    class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300 dark:border-slate-700 dark:bg-slate-900"
                                >
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

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
                {{ $isEditing ? 'Simpan Perubahan' : 'Tambah User' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
