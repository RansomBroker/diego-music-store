<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data Karyawan"
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
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Karyawan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Data Karyawan</h1>
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
                                placeholder="Cari NIK, Nama, HP..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            >
                        </div>

                        <!-- Add Action -->
                        <button
                            wire:click="openCreate"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                        >
                            <i class="ph-bold ph-plus text-sm"></i>
                            <span>Tambah Karyawan</span>
                        </button>
                    </div>

                    <!-- Table Container -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th sortable field="nik" :sortField="$sortField" :sortDirection="$sortDirection">
                                        NIK
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Nama Karyawan
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Role / Akses User
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        Cabang
                                    </x-pos.table.th>
                                    <x-pos.table.th>
                                        No Telepon / WA
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="monthly_off_days_quota" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Quota Off Day
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="basic_salary" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Gaji Pokok
                                    </x-pos.table.th>
                                    <x-pos.table.th sortable field="is_active" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Status
                                    </x-pos.table.th>
                                    <x-pos.table.th align="right">
                                        Aksi
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($employees as $emp)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                        <!-- NIK -->
                                        <x-pos.table.td class="font-mono text-xs text-slate-500 dark:text-slate-400">
                                            {{ $emp->nik }}
                                        </x-pos.table.td>

                                        <!-- Nama Karyawan -->
                                        <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                                            <div>{{ $emp->name }}</div>
                                            @if($emp->email)
                                                <div class="text-xs text-slate-400 dark:text-slate-500 font-normal">{{ $emp->email }}</div>
                                            @endif
                                        </x-pos.table.td>

                                        <!-- Role / User -->
                                        <x-pos.table.td>
                                            @if ($emp->user)
                                                <div class="flex flex-wrap gap-1">
                                                    @forelse($emp->user->roles as $role)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                                            {{ ucfirst($role->name) }}
                                                        </span>
                                                    @empty
                                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">User (Tanpa Role)</span>
                                                    @endforelse
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">Tanpa Akun User</span>
                                            @endif
                                        </x-pos.table.td>

                                        <!-- Cabang -->
                                        <x-pos.table.td>
                                            @if($emp->branch)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                                                    <i class="ph ph-storefront text-slate-400"></i>
                                                    {{ $emp->branch->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">Semua Cabang</span>
                                            @endif
                                        </x-pos.table.td>

                                        <!-- Telepon -->
                                        <x-pos.table.td class="text-xs">
                                            {{ $emp->phone ?: '-' }}
                                        </x-pos.table.td>

                                        <!-- Quota Off Day -->
                                        <x-pos.table.td class="text-xs font-semibold">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                {{ $emp->monthly_off_days_quota }} hari/bln
                                            </span>
                                        </x-pos.table.td>

                                        <!-- Gaji Pokok -->
                                        <x-pos.table.td class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}
                                        </x-pos.table.td>

                                        <!-- Status -->
                                        <x-pos.table.td>
                                            @if ($emp->is_active)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </x-pos.table.td>

                                        <!-- Actions -->
                                        <x-pos.table.td align="right">
                                            <x-pos.table.actions
                                                :editAction="'openEdit(' . $emp->id . ')'"
                                                :deleteAction="'confirmDelete(' . $emp->id . ')'"
                                            />
                                        </x-pos.table.td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="ph ph-user-group text-4xl text-slate-300 dark:text-slate-600"></i>
                                                <span class="text-sm font-medium">Belum ada data karyawan</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Pagination -->
                    @if ($employees->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                            {{ $employees->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form (Tambah / Edit Karyawan) -->
    <x-pos.modal
        wire:model="showModal"
        :title="$isEditing ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru'"
        :subtitle="$isEditing ? 'Perbarui informasi data karyawan terpilih' : 'Isikan data personel karyawan baru ke sistem'"
        :icon="$isEditing ? 'ph-pencil-simple' : 'ph-user-plus'"
        maxWidth="2xl"
    >
        <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- NIK -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            NIK (Nomor Induk Karyawan)
                        </label>
                        <input
                            type="text"
                            wire:model="nik"
                            placeholder="Otomatis jika kosong..."
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('nik') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap Karyawan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- No Telepon -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            No Telepon / WhatsApp
                        </label>
                        <input
                            type="text"
                            wire:model="phone"
                            placeholder="08123456789"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="karyawan@diegomusic.com"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Cabang -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Cabang Karyawan
                        </label>
                        <select
                            wire:model="branch_id"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                            <option value="">-- Pilih Cabang --</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Akun User Login -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Hubungkan ke Akun Login User
                        </label>
                        <select
                            wire:model="user_id"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                            <option value="">-- Tanpa Akun Login --</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Quota Off Day -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kuota Off Day per Bulan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            wire:model="monthly_off_days_quota"
                            required
                            min="0"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('monthly_off_days_quota') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Gaji Pokok -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Gaji Pokok (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            wire:model="basic_salary"
                            required
                            min="0"
                            step="1000"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                        >
                        @error('basic_salary') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Alamat -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Alamat Lengkap
                    </label>
                    <textarea
                        wire:model="address"
                        rows="2"
                        placeholder="Jl. Merdeka No. 10..."
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary dark:focus:border-blue-500 focus:outline-none"
                    ></textarea>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="is_active_check"
                        wire:model="is_active"
                        class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary dark:border-slate-700 dark:bg-slate-950"
                    >
                    <label for="is_active_check" class="text-sm font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                        Karyawan Aktif
                    </label>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                    >
                        Simpan
                    </button>
                </div>
            </form>
    </x-pos.modal>

    <!-- Modal Delete Confirmation -->
    <x-pos.modal
        wire:model="showDeleteModal"
        title="Hapus Data Karyawan"
        subtitle="Konfirmasi tindakan penghapusan data"
        icon="ph-warning-octagon"
        maxWidth="sm"
    >
        <div class="py-2 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                <i class="ph-bold ph-warning text-2xl"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Apakah Anda yakin?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Data karyawan yang dihapus akan dipindahkan ke tempat sampah (Soft Delete) dan dapat dipulihkan jika diperlukan.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button
                    wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    wire:click="delete"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                >
                    Hapus
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
