<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data User"
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
                                    <span class="text-slate-400 dark:text-slate-500">Input Data</span>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">User</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">User</h1>
                </div>

                <!-- Table Card Wrapper (Filament Style) -->
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
                                placeholder="Cari..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            >
                        </div>

                        <!-- Add Action -->
                        <button
                            wire:click="openCreate"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                        >
                            <i class="ph-bold ph-plus text-sm"></i>
                            <span>Tambah User</span>
                        </button>
                    </div>

                    <!-- Table Container -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <!-- Full Name -->
                                    <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Nama Lengkap
                                    </x-pos.table.th>
                                    <!-- Username -->
                                    <x-pos.table.th sortable field="username" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Username
                                    </x-pos.table.th>
                                    <!-- Email -->
                                    <x-pos.table.th sortable field="email" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Email Address
                                    </x-pos.table.th>
                                    <!-- Status -->
                                    <x-pos.table.th>
                                        Status
                                    </x-pos.table.th>
                                    <!-- Branches -->
                                    <x-pos.table.th>
                                        Cabang
                                    </x-pos.table.th>
                                    <!-- Roles -->
                                    <x-pos.table.th>
                                        Role
                                    </x-pos.table.th>
                                    <!-- Actions -->
                                    <x-pos.table.th class="text-right">
                                        Aksi
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($users as $row)
                                    <x-pos.table.tr>
                                        <!-- Full Name + Avatar -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    {{ strtoupper(substr($row->name, 0, 2)) }}
                                                </div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                                    {{ $row->name }}
                                                </div>
                                            </div>
                                        </x-pos.table.td>
                                        <!-- Username -->
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 font-medium">
                                            {{ $row->username ?? '—' }}
                                        </x-pos.table.td>
                                        <!-- Email Address -->
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                            {{ $row->email }}
                                        </x-pos.table.td>
                                        <!-- Status -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            @if ($row->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/45 dark:text-emerald-450 dark:border-emerald-900/40">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700/60">
                                                    Non-aktif
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                        <!-- Branches (badges) -->
                                        <x-pos.table.td class="max-w-[200px]">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse ($row->branches as $branch)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60">
                                                        {{ $branch->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-slate-350 dark:text-slate-650 text-xs">—</span>
                                                @endforelse
                                            </div>
                                        </x-pos.table.td>
                                        <!-- Roles (badges with custom colors matching UsersTable.php config) -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse ($row->roles as $role)
                                                    @php
                                                        $badgeColor = match ($role->name) {
                                                            'owner'      => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/40 dark:text-rose-455 dark:border-rose-900/40',
                                                            'admin'      => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/40 dark:text-amber-455 dark:border-amber-900/40',
                                                            'cashier'    => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-455 dark:border-emerald-900/40',
                                                            'sales'      => 'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-950/40 dark:text-sky-400 dark:border-sky-900/40',
                                                            'technician' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-850 dark:text-slate-350 dark:border-slate-750',
                                                            default      => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/40',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeColor }}">
                                                        {{ $role->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-slate-350 dark:text-slate-650 text-xs">—</span>
                                                @endforelse
                                            </div>
                                        </x-pos.table.td>
                                        <!-- Actions -->
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button
                                                    wire:click="openEdit({{ $row->id }})"
                                                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                    <span>Ubah</span>
                                                </button>
                                                @if (Auth::id() !== $row->id)
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
                                    <x-pos.table.empty colspan="7" icon="ph-users" message="Tidak ada user ditemukan" />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Footer (Filament v3 Style Pagination) -->
                    @if ($users->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                            <!-- Left: Statistics & Per Page -->
                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                                <div>
                                    Menampilkan
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->firstItem() }}</span>
                                    sampai
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->total() }}</span>
                                    hasil
                                </div>
                                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-xs">Per halaman:</label>
                                    <select
                                        wire:model.live="perPage"
                                        class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-750 dark:text-slate-250 py-1 px-2 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition duration-150"
                                    >
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right: Navigation Pages -->
                            @if ($users->hasPages())
                                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                                    {{-- Previous Page Button --}}
                                    @if ($users->onFirstPage())
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </span>
                                    @else
                                        <button
                                            wire:click="previousPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </button>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                                        @if ($page == $users->currentPage())
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <button
                                                wire:click="gotoPage({{ $page }})"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                                            >
                                                {{ $page }}
                                            </button>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Button --}}
                                    @if ($users->hasMorePages())
                                        <button
                                            wire:click="nextPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-right text-sm"></i>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
                                            <i class="ph-bold ph-caret-right text-sm"></i>
                                        </span>
                                    @endif
                                </nav>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

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
                    <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah User' }}</span>
                </button>
            </div>
        </form>
    </x-pos.modal>

    {{-- ===================== REUSABLE MODAL: KONFIRMASI HAPUS ===================== --}}
    <x-pos.modal
        wire:model="showDeleteModal"
        title="Hapus User"
        subtitle="Konfirmasi penghapusan data secara permanen"
        icon="ph-trash"
        maxWidth="sm"
    >
        <div class="text-center space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
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
                    class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
