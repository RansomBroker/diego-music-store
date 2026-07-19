<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data Pelanggan"
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
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Pelanggan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pelanggan</h1>
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
                            <span>Tambah Pelanggan</span>
                        </button>
                    </div>

                    <!-- Table Container -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <!-- Nama Pelanggan -->
                                    <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Nama Pelanggan
                                    </x-pos.table.th>
                                    <!-- Telepon -->
                                    <x-pos.table.th>
                                        Telepon
                                    </x-pos.table.th>
                                    <!-- Email -->
                                    <x-pos.table.th>
                                        Email
                                    </x-pos.table.th>
                                    <!-- Label -->
                                    <x-pos.table.th>
                                        Label
                                    </x-pos.table.th>
                                    <!-- Poin -->
                                    <x-pos.table.th sortable field="loyalty_points" :sortField="$sortField" :sortDirection="$sortDirection">
                                        Poin
                                    </x-pos.table.th>
                                    <!-- Member -->
                                    <x-pos.table.th>
                                        Member
                                    </x-pos.table.th>
                                    <!-- Actions -->
                                    <x-pos.table.th class="text-right">
                                        Aksi
                                    </x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($customers as $customer)
                                    <x-pos.table.tr>
                                        <!-- Nama & Alamat -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $customer->name }}</div>
                                                    @if ($customer->address)
                                                        <div class="text-xs text-slate-400 dark:text-slate-555 mt-0.5 max-w-[200px] truncate">{{ $customer->address }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </x-pos.table.td>
                                        <!-- Telepon -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            {{ $customer->phone ?? '—' }}
                                        </x-pos.table.td>
                                        <!-- Email -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            {{ $customer->email ?? '—' }}
                                        </x-pos.table.td>
                                        <!-- Label badge -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            @if ($customer->label)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-750 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                                    {{ $customer->label->name }}
                                                </span>
                                            @else
                                                <span class="text-slate-350 dark:text-slate-600 text-xs">—</span>
                                            @endif
                                        </x-pos.table.td>
                                        <!-- Poin -->
                                        <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-200">
                                            {{ number_format($customer->loyalty_points) }}
                                        </x-pos.table.td>
                                        <!-- Member Badge -->
                                        <x-pos.table.td class="whitespace-nowrap">
                                            @if ($customer->is_loyalty_member)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-755 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                                    <i class="ph-fill ph-star text-[10px]"></i> Member
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60">
                                                    Umum
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                        <!-- Aksi Buttons (Filament style links or actions) -->
                                        <x-pos.table.td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button
                                                    wire:click="openEdit({{ $customer->id }})"
                                                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                    <span>Ubah</span>
                                                </button>
                                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                                <button
                                                    wire:click="confirmDelete({{ $customer->id }})"
                                                    class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-trash text-xs"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="7" icon="ph-users" message="Tidak ada data pelanggan ditemukan" />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Footer (Filament v3 Style Pagination) -->
                    @if ($customers->total() > 0)
                        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
                            <!-- Left: Statistics & Per Page -->
                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                                <div>
                                    Menampilkan
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->firstItem() }}</span>
                                    sampai
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->total() }}</span>
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
                            @if ($customers->hasPages())
                                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                                    {{-- Previous Page Button --}}
                                    @if ($customers->onFirstPage())
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </span>
                                    @else
                                        <button
                                            wire:click="previousPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-left text-sm"></i>
                                        </button>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) as $page => $url)
                                        @if ($page == $customers->currentPage())
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <button
                                                wire:click="gotoPage({{ $page }})"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                                            >
                                                {{ $page }}
                                            </button>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Button --}}
                                    @if ($customers->hasMorePages())
                                        <button
                                            wire:click="nextPage"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                                        >
                                            <i class="ph-bold ph-caret-right text-sm"></i>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
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
        :title="$isEditing ? 'Ubah Pelanggan' : 'Tambah Pelanggan'"
        :subtitle="$isEditing ? 'Perbarui detail data pelanggan terpilih' : 'Tambahkan data pelanggan baru ke sistem'"
        :icon="$isEditing ? 'ph-pencil-simple' : 'ph-user-plus'"
        maxWidth="2xl"
    >
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Kolom Kiri: Informasi Kontak --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Informasi Kontak</h4>

                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Pelanggan <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            placeholder="e.g. John Doe"
                        >
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nomor Telepon</label>
                        <input
                            type="tel"
                            wire:model="phone"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            placeholder="e.g. 08123456789"
                        >
                        @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
                        <input
                            type="email"
                            wire:model="email"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            placeholder="e.g. john@example.com"
                        >
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Lahir</label>
                        <input
                            type="date"
                            wire:model="date_of_birth"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        >
                        @error('date_of_birth') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Label -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Label Pelanggan</label>
                        <select
                            wire:model="customer_label_id"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        >
                            <option value="">— Tanpa Label —</option>
                            @foreach ($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Alamat</label>
                        <textarea
                            wire:model="address"
                            rows="2"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors resize-none"
                            placeholder="Alamat pelanggan..."
                        ></textarea>
                    </div>
                </div>

                {{-- Kolom Kanan: Program Loyalty --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Program Loyalty</h4>

                    <!-- Toggle Loyalty Member -->
                    <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl transition-colors">
                        <div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Loyalty Member</div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-550 mt-0.5">Berikan keuntungan akumulasi poin</div>
                        </div>
                        <button
                            type="button"
                            wire:click="$toggle('is_loyalty_member')"
                            class="relative w-10 h-5.5 rounded-full transition-colors duration-200 focus:outline-none cursor-pointer {{ $is_loyalty_member ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700' }}"
                        >
                            <span class="absolute top-0.5 left-0.5 w-4.5 h-4.5 rounded-full bg-white shadow-sm transition-transform duration-200 {{ $is_loyalty_member ? 'translate-x-4.5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    <!-- Poin Belanja -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Poin Belanja</label>
                        <div class="relative">
                            <i class="ph ph-star absolute left-3 top-1/2 -translate-y-1/2 text-amber-400 text-base"></i>
                            <input
                                type="number"
                                wire:model="loyalty_points"
                                min="0"
                                class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                                placeholder="0"
                            >
                        </div>
                        @error('loyalty_points') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tingkat Harga Default -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tingkat Harga Default</label>
                        <select
                            wire:model="pricing_tier_id"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        >
                            <option value="">— Harga Standar —</option>
                            @foreach ($pricingTiers as $tier)
                                <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Info Box -->
                    <div class="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-xl transition-colors">
                        <div class="flex items-start gap-2.5">
                            <i class="ph-fill ph-info text-blue-500 dark:text-blue-400 text-base mt-0.5 flex-shrink-0"></i>
                            <p class="text-[11px] text-blue-700 dark:text-blue-400 leading-normal">
                                Pelanggan yang didaftarkan sebagai member loyalitas akan secara otomatis mengumpulkan poin dari setiap transaksi retail.
                            </p>
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
                    <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}</span>
                </button>
            </div>
        </form>
    </x-pos.modal>

    {{-- ===================== REUSABLE MODAL: KONFIRMASI HAPUS ===================== --}}
    <x-pos.modal
        wire:model="showDeleteModal"
        title="Hapus Pelanggan"
        subtitle="Konfirmasi penghapusan data secara permanen"
        icon="ph-trash"
        maxWidth="sm"
    >
        <div class="text-center space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Apakah Anda yakin ingin menghapus data pelanggan ini? Tindakan ini tidak dapat dibatalkan.
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
