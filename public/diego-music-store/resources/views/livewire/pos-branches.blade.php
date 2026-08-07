<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Manajemen Cabang Toko"
                backLabel="Dashboard"
            />

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto no-scrollbar p-6">
                <div class="w-full space-y-6">

                    <!-- Header & Breadcrumb -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                    <li class="inline-flex items-center">
                                        <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-400">Pengaturan Toko</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Manajemen Cabang</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Kelola Cabang & Lokasi Outlet Toko</h1>
                        </div>

                        <div>
                            <x-pos.utility.button variant="primary" size="md" icon="ph-plus" wire:click="openCreate">
                                Tambah Cabang Baru
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-sm space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <!-- Col 1: Pencarian -->
                            <div class="lg:col-span-2">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Cabang</label>
                                <x-pos.form.input model="search" :live="true" placeholder="Cari nama cabang, kota, alamat..." icon="ph-magnifying-glass" size="sm" />
                            </div>

                            <!-- Col 2: Status -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Cabang</label>
                                <x-pos.form.select model="selectedStatus" :live="true" size="sm">
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </x-pos.form.select>
                            </div>

                            <!-- Col 3: Reset -->
                            <div class="flex items-end">
                                <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="w-full" wire:click="resetFilters">
                                    Reset Filter
                                </x-pos.utility.button>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                            <span>DAFTAR OUTLET & LOKASI CABANG</span>
                            <span class="text-xs text-slate-400 font-normal">Total {{ $branches->total() }} Cabang</span>
                        </div>

                        <x-pos.table.container>
                            <x-pos.table>
                                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                    <tr>
                                        <x-pos.table.th>Nama Cabang & Toko</x-pos.table.th>
                                        <x-pos.table.th>Kota / Wilayah</x-pos.table.th>
                                        <x-pos.table.th>Alamat & Kontak</x-pos.table.th>
                                        <x-pos.table.th>Manager Cabang</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @forelse ($branches as $b)
                                        <x-pos.table.tr>
                                            <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">
                                                <div class="flex items-center gap-3">
                                                    @if ($b->logo_path)
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($b->logo_path) }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center text-xs shrink-0">
                                                            <i class="ph ph-storefront"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-bold text-slate-900 dark:text-white">{{ $b->name }}</div>
                                                        <div class="text-[10px] text-slate-400 font-normal">{{ $b->store_name }}</div>
                                                    </div>
                                                </div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                <div>{{ $b->city ?: '-' }}</div>
                                                <div class="text-[10px] text-slate-400 font-normal">{{ $b->province }}</div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-400">
                                                <div class="truncate max-w-xs" title="{{ $b->address }}">{{ $b->address ?: '-' }}</div>
                                                <div class="text-[10px] font-mono text-slate-400">Telp: {{ $b->phone ?: '-' }}</div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs text-slate-800 dark:text-slate-200">
                                                {{ $b->manager->name ?? 'Belum ditentukan' }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center">
                                                @if ($b->is_active)
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        AKTIF
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                        NON-AKTIF
                                                    </span>
                                                @endif
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <x-pos.utility.button variant="secondary" size="xs" icon="ph-pencil-simple" wire:click="openEdit({{ $b->id }})">
                                                        Edit Profil
                                                    </x-pos.utility.button>
                                                </div>
                                            </x-pos.table.td>
                                        </x-pos.table.tr>
                                    @empty
                                        <x-pos.table.empty colspan="6" icon="ph-storefront" message="Belum ada data cabang toko." />
                                    @endforelse
                                </tbody>
                            </x-pos.table>
                        </x-pos.table.container>

                        @if ($branches->hasPages())
                            <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">
                                {{ $branches->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- CREATE / EDIT BRANCH MODAL -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                            {{ $isEditing ? 'Edit Profil Cabang' : 'Tambah Cabang Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ $isEditing ? 'Perbarui informasi outlet cabang.' : 'Cabang baru akan otomatis dikonfigurasi dengan stok awal & receipt setting.' }}
                        </p>
                    </div>
                    <button wire:click="$set('showModal', false)" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nama Cabang (Internal) *</label>
                            <x-pos.form.input model="name" placeholder="Contoh: Cabang Siantan / Cabang Gajah Mada" />
                            @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nama Toko (Untuk Struk & Faktur) *</label>
                            <x-pos.form.input model="store_name" placeholder="Contoh: Diego Music Store Siantan" />
                            @error('store_name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Telepon</label>
                            <x-pos.form.input model="phone" placeholder="0561-xxxxxx" />
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Email Cabang</label>
                            <x-pos.form.input model="email" placeholder="cabang@diegomusic.com" />
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Manager Cabang</label>
                            <x-pos.form.select model="manager_id">
                                <option value="">Pilih Manager</option>
                                @foreach ($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </x-pos.form.select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kota / Kabupaten</label>
                            <x-pos.form.input model="city" placeholder="Pontianak" />
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Provinsi</label>
                            <x-pos.form.input model="province" placeholder="Kalimantan Barat" />
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kode Pos</label>
                            <x-pos.form.input model="postal_code" placeholder="78123" />
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Jl. Gajah Mada No. 21-22..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">NPWP Toko</label>
                            <x-pos.form.input model="npwp" placeholder="00.000.000.0-000.000" />
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Informasi Bank / Rekening</label>
                            <x-pos.form.input model="bank_info" placeholder="BCA 123456789 a.n Diego Music" />
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Upload Logo Cabang (Opsional)</label>
                        <input type="file" wire:model="logo" class="w-full text-xs border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-white dark:bg-slate-900">
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="is_active" id="is_active_cb" class="w-4 h-4 rounded text-blue-600">
                        <label for="is_active_cb" class="font-bold text-slate-700 dark:text-slate-300">Status Cabang Aktif</label>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <x-pos.utility.button type="button" variant="secondary" size="sm" wire:click="$set('showModal', false)">
                            Batal
                        </x-pos.utility.button>
                        <x-pos.utility.button type="submit" variant="primary" size="sm" icon="ph-check">
                            {{ $isEditing ? 'Simpan Perubahan' : 'Buat Cabang Baru' }}
                        </x-pos.utility.button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
