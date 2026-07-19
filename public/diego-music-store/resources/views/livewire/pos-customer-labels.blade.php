<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data Kategori Penjualan"
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
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Kategori Penjualan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Kategori Penjualan</h1>
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
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor duration-150 cursor-pointer active:scale-[0.98]"
                        >
                            <i class="ph-bold ph-plus text-sm"></i>
                            <span>Tambah Kategori</span>
                        </button>
                    </div>

                    <!-- Table Container -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <!-- Key -->
                                    <x-pos.table.th sortable field="key" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Key / Code
                                    </x-pos.table.th>
                                    <!-- Name -->
                                    <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Nama Label / Kategori
                                    </x-pos.table.th>
                                    <!-- Actions -->
                                    <x-pos.table.th class="text-right">
                                        Aksi
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($labels as $row)
                                    <x-pos.table.tr>
                                        <!-- Key -->
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-750 dark:text-slate-200 font-mono">
                                            {{ $row->key }}
                                        </x-pos.table.td>
                                        <!-- Name -->
                                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-semibold">
                                            {{ $row->name }}
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
                                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                                <button
                                                    wire:click="confirmDelete({{ $row->id }})"
                                                    class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-trash text-xs"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="3" icon="ph-tag" message="Tidak ada kategori penjualan ditemukan" />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Footer (Filament v3 Style Pagination) -->
                    @if ($labels->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                            <!-- Left: Statistics & Per Page -->
                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                                <div>
                                    Menampilkan
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $labels->firstItem() }}</span>
                                    sampai
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $labels->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $labels->total() }}</span>
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
                            @if ($labels->hasPages())
                                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                                    {{-- Previous Page Button --}}
                                    @if ($labels->onFirstPage())
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
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
                                    @foreach ($labels->getUrlRange(max(1, $labels->currentPage() - 2), min($labels->lastPage(), $labels->currentPage() + 2)) as $page => $url)
                                        @if ($page == $labels->currentPage())
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
                                    @if ($labels->hasMorePages())
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
        :title="$isEditing ? 'Ubah Kategori Penjualan' : 'Tambah Kategori Penjualan'"
        :subtitle="$isEditing ? 'Perbarui detail kategori penjualan terpilih' : 'Tambahkan kategori penjualan baru ke sistem'"
        :icon="$isEditing ? 'ph-pencil-simple' : 'ph-tag'"
        maxWidth="lg"
    >
        <form wire:submit.prevent="save" class="space-y-4">
            <!-- Key / Code -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Key / Code <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="key"
                    @disabled($isEditing)
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors disabled:opacity-50 disabled:bg-slate-50 dark:disabled:bg-slate-900"
                    placeholder="e.g. perorangan, instansi"
                >
                @error('key') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nama Label / Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 font-medium">Nama Kategori <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    placeholder="e.g. Perorangan, Instansi / Lembaga"
                >
                @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
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
                    <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
                </button>
            </div>
        </form>
    </x-pos.modal>

    {{-- ===================== REUSABLE MODAL: KONFIRMASI HAPUS ===================== --}}
    <x-pos.modal
        wire:model="showDeleteModal"
        title="Hapus Kategori Penjualan"
        subtitle="Konfirmasi penghapusan data secara permanen"
        icon="ph-trash"
        maxWidth="sm"
    >
        <div class="text-center space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Apakah Anda yakin ingin menghapus kategori penjualan ini? Tindakan ini tidak dapat dibatalkan.
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
