<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Manajemen Barang Service & Reparasi"
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
                                            <span class="text-slate-400">Layanan</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Barang Service</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Manajemen Barang Service & Reparasi {{ $currentBranch?->name ? '— ' . $currentBranch->name : '' }}</h1>
                        </div>
                    </div>

                    <!-- Common Filter Toolbar Card (4-Column Layout) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-sm space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <!-- Col 1: Filter Status -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Progress</label>
                                <x-pos.form.select model="selectedStatus" :live="true" size="sm" icon="ph-squares-four">
                                    <option value="">Semua Status Progress</option>
                                    <option value="received">Diterima</option>
                                    <option value="diagnosing">Proses Diagnosa</option>
                                    <option value="in_progress">Dikerjakan</option>
                                    <option value="waiting_parts">Menunggu Sparepart</option>
                                    <option value="completed">Selesai Service</option>
                                    <option value="picked_up">Siap / Sudah Diambil</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </x-pos.form.select>
                            </div>

                            <!-- Col 2: Filter Cabang -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Filter Cabang</label>
                                <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </x-pos.form.select>
                            </div>

                            <!-- Col 3: Filter Teknisi -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Teknisi Penanggung Jawab</label>
                                <x-pos.form.select model="selectedTechnicianId" :live="true" size="sm" icon="ph-user-gear">
                                    <option value="">Semua Teknisi</option>
                                    @foreach ($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                    @endforeach
                                </x-pos.form.select>
                            </div>

                            <!-- Col 4: Pencarian & Reset -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Tiket / Unit</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 min-w-0">
                                        <x-pos.form.input model="search" :live="true" placeholder="No. Tiket / Pelanggan / Unit..." icon="ph-magnifying-glass" size="sm" />
                                    </div>
                                    <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="shrink-0" wire:click="resetFilters" title="Reset Filter">
                                        Reset
                                    </x-pos.utility.button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Total Tiket Service</span>
                            <div class="text-xl font-black text-slate-900 dark:text-white mt-1 font-mono">
                                {{ number_format($statusCounts['all'] ?? 0, 0, ',', '.') }} Order
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Terdaftar di sistem</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Baru Diterima</span>
                            <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
                                {{ number_format($statusCounts['received'] ?? 0, 0, ',', '.') }} Unit
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Menunggu pemeriksaan</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Dalam Pengerjaan</span>
                            <div class="text-xl font-black text-amber-500 dark:text-amber-400 mt-1 font-mono">
                                {{ number_format($statusCounts['in_progress'] ?? 0, 0, ',', '.') }} Unit
                            </div>
                            <span class="text-[11px] text-amber-500 font-bold mt-1 block">Diagnosa / Service / Parts</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Selesai / Siap Diambil</span>
                            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                                {{ number_format($statusCounts['completed'] ?? 0, 0, ',', '.') }} Unit
                            </div>
                            <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Siap diambil pelanggan</span>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                            <span>DAFTAR TIKET SERVICE & REPARASI BARANG</span>
                            <span class="text-xs text-slate-400 font-normal">Cabang: {{ $currentBranch?->name ?: 'Semua Cabang' }}</span>
                        </div>

                        <x-pos.table.container>
                            <x-pos.table>
                                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                    <tr>
                                        <x-pos.table.th>No. Tiket & Tgl</x-pos.table.th>
                                        <x-pos.table.th>Unit / Instrument</x-pos.table.th>
                                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                                        <x-pos.table.th>Teknisi</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Status Progress</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Total Biaya</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @forelse ($orders as $so)
                                        <x-pos.table.tr>
                                            <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                                                <div class="font-bold text-primary dark:text-blue-400">{{ $so->ticket_code }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $so->created_at->format('d/m/Y H:i') }}</div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">
                                                <div>{{ $so->device_name }}</div>
                                                @if ($so->serial_number)
                                                    <div class="text-[10px] font-mono text-slate-400">S/N: {{ $so->serial_number }}</div>
                                                @endif
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                                <div>{{ $so->customer_name }}</div>
                                                <div class="text-[10px] font-mono text-slate-400">{{ $so->customer_phone ?: '-' }}</div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs text-slate-700 dark:text-slate-300">
                                                {{ $so->technician->name ?? 'Belum ditentukan' }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center">
                                                @if ($so->status === 'received')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                                        DITERIMA
                                                    </span>
                                                @elseif ($so->status === 'diagnosing')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                                        DIAGNOSA
                                                    </span>
                                                @elseif ($so->status === 'in_progress')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                        DIKERJAKAN
                                                    </span>
                                                @elseif ($so->status === 'waiting_parts')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-yellow-100 text-yellow-800 dark:bg-yellow-950/60 dark:text-yellow-300">
                                                        SPAREPART
                                                    </span>
                                                @elseif ($so->status === 'completed')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        SELESAI
                                                    </span>
                                                @elseif ($so->status === 'picked_up')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                                        DIAMBIL
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                                        DIBATALKAN
                                                    </span>
                                                @endif
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-right font-mono font-extrabold text-xs text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($so->total_cost ?: $so->estimated_cost, 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <x-pos.utility.button variant="secondary" size="xs" icon="ph-pencil-simple" wire:click="openEditModal({{ $so->id }})" title="Update Status & Catatan">
                                                        Update
                                                    </x-pos.utility.button>

                                                    <a href="{{ $so->tracking_url }}" target="_blank" class="p-1.5 text-slate-500 hover:text-primary dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Buka Link Tracking Publik">
                                                        <i class="ph ph-arrow-square-out text-base"></i>
                                                    </a>
                                                </div>
                                            </x-pos.table.td>
                                        </x-pos.table.tr>
                                    @empty
                                        <x-pos.table.empty colspan="7" icon="ph-wrench" message="Belum ada tiket service & reparasi barang yang sesuai dengan filter." />
                                    @endforelse
                                </tbody>
                            </x-pos.table>
                        </x-pos.table.container>

                        @if ($orders->hasPages())
                            <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">
                                {{ $orders->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- UPDATE SERVICE ORDER MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                            Update Tiket Service
                        </h3>
                        <p class="text-xs text-slate-500 font-mono">Kode Tiket: {{ \App\Models\ServiceOrder::find($editingOrderId)?->ticket_code }}</p>
                    </div>
                    <button wire:click="closeEditModal" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="saveServiceOrder" class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Status Progress Service</label>
                            <x-pos.form.select model="editStatus">
                                <option value="received">Diterima</option>
                                <option value="diagnosing">Proses Diagnosa</option>
                                <option value="in_progress">Dikerjakan</option>
                                <option value="waiting_parts">Menunggu Sparepart</option>
                                <option value="completed">Selesai Service</option>
                                <option value="picked_up">Siap / Sudah Diambil</option>
                                <option value="cancelled">Dibatalkan</option>
                            </x-pos.form.select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Teknisi Penanggung Jawab</label>
                            <x-pos.form.select model="editTechnicianId">
                                <option value="">Pilih Teknisi</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </x-pos.form.select>
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nomor Seri Unit (S/N)</label>
                        <x-pos.form.input model="editSerialNumber" placeholder="Contoh: S/N F310-998821" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Keluhan / Gejala Kerusakan</label>
                        <textarea wire:model="editComplaint" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Deskripsi keluhan dari pelanggan..."></textarea>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Catatan Pengerjaan Teknisi</label>
                        <textarea wire:model="editNotes" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Catatan hasil diagnosa, tindakan perbaikan, dll..."></textarea>
                    </div>

                    <!-- Additional Spareparts / Labor Section -->
                    <div class="border-t border-slate-200 dark:border-slate-800 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-700 dark:text-slate-300">Sparepart & Layanan Tambahan</label>
                            <x-pos.utility.button type="button" variant="secondary" size="xs" icon="ph-plus" wire:click="addAdditionalChargeRow">
                                Tambah Item
                            </x-pos.utility.button>
                        </div>

                        @foreach ($editAdditionalCharges as $idx => $chg)
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <x-pos.form.input model="editAdditionalCharges.{{ $idx }}.name" placeholder="Nama sparepart/jasa tambahan..." />
                                </div>
                                <div class="w-36">
                                    <x-pos.form.input type="number" model="editAdditionalCharges.{{ $idx }}.amount" placeholder="Nominal (Rp)" />
                                </div>
                                <button type="button" wire:click="removeAdditionalChargeRow({{ $idx }})" class="p-2 text-rose-500 hover:text-rose-700">
                                    <i class="ph ph-trash text-base"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <x-pos.utility.button type="button" variant="secondary" size="sm" wire:click="closeEditModal">
                            Batal
                        </x-pos.utility.button>
                        <x-pos.utility.button type="submit" variant="primary" size="sm" icon="ph-check">
                            Simpan Perubahan
                        </x-pos.utility.button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
