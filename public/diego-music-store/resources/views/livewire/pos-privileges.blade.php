<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Setting Privilege User"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                <div>
                    <!-- Breadcrumbs -->
                    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-400 dark:text-slate-500">Utility</span>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Privilege User</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Setting Privilege User</h1>
                </div>

                <!-- Table Card Wrapper -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

                    <!-- Toolbar (Search & Actions) -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
                        <!-- Search Input -->
                        <div class="relative w-full sm:max-w-xs">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Cari role..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            >
                        </div>

                        <!-- Add Action -->
                        <button
                            wire:click="openCreate"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                        >
                            <i class="ph-bold ph-plus text-sm"></i>
                            <span>Tambah Role Baru</span>
                        </button>
                    </div>

                    <!-- Table Container -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th>Nama Role</x-pos.table.th>
                                    <x-pos.table.th>Jumlah User</x-pos.table.th>
                                    <x-pos.table.th>Jumlah Permissions</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($roles as $row)
                                    <x-pos.table.tr>
                                        <x-pos.table.td class="whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    <i class="ph-bold ph-shield-check text-base"></i>
                                                </div>
                                                <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">
                                                    {{ $row->name }}
                                                </div>
                                            </div>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ $row->users_count }} User
                                            </span>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                                {{ $row->permissions_count }} Akses Dipilih
                                            </span>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button
                                                    wire:click="openEdit({{ $row->id }})"
                                                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                    <span>Atur Hak Akses</span>
                                                </button>
                                                @if ($row->name !== 'Super Admin' && $row->name !== 'admin')
                                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                                    <button
                                                        wire:click="confirmDelete({{ $row->id }})"
                                                        class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                                                    >
                                                        <i class="ph-bold ph-trash text-xs"></i>
                                                        <span>Hapus</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="4" icon="ph-shield-warning" message="Tidak ada Role ditemukan" />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    @if ($roles->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                            {{ $roles->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

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
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $permName }}</span>
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
                <button
                    type="button"
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                >
                    <i class="ph-bold ph-check text-xs"></i>
                    <span>Simpan Role & Hak Akses</span>
                </button>
            </div>
        </form>
    </x-pos.modal>

    <!-- Modal Delete Confirmation -->
    <x-pos.modal
        wire:model="showDeleteModal"
        title="Hapus Role"
        subtitle="Konfirmasi hapus role hak akses"
        icon="ph-trash"
        maxWidth="sm"
    >
        <div class="text-center space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Apakah Anda yakin ingin menghapus role ini? User yang memiliki role ini tidak akan lagi memiliki hak akses terkait.
            </p>
            <div class="flex gap-3 pt-2">
                <button
                    wire:click="$set('showDeleteModal', false)"
                    class="flex-1 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    wire:click="destroy"
                    class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                >
                    Hapus Role
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
