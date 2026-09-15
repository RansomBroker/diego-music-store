<!-- TAB 2: SKEMA & ATURAN KOMISI -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <!-- Card Toolbar Header -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="ph-bold ph-sliders text-primary text-base"></i>
                Daftar Skema & Aturan Komisi Aktif
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Konfigurasi persentase komisi per produk, kategori, atau transaksi umum</p>
        </div>
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-plus-circle"
            wire:click="openSchemeModal"
        >
            Tambah Skema Baru
        </x-pos.utility.button>
    </div>

    @if ($schemes->isNotEmpty())
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Nama Skema</x-pos.table.th>
                        <x-pos.table.th>Cabang</x-pos.table.th>
                        <x-pos.table.th>Target Karyawan / Sales</x-pos.table.th>
                        <x-pos.table.th>Tipe Kalkulasi</x-pos.table.th>
                        <x-pos.table.th class="text-right">Tarif / Rate</x-pos.table.th>
                        <x-pos.table.th>Target Penerapan</x-pos.table.th>
                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($schemes as $sch)
                        <x-pos.table.tr>
                            <x-pos.table.td>
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $sch->name }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-600 dark:text-slate-400 font-normal">
                                {{ $sch->branch?->name ?: 'Semua Cabang' }}
                            </x-pos.table.td>
                            <x-pos.table.td>
                                @if ($sch->employees->count() > 0)
                                    <div class="flex flex-wrap items-center gap-1">
                                        @foreach ($sch->employees as $schEmp)
                                            <x-pos.utility.pill variant="primary" size="xs">
                                                {{ $schEmp->name }}
                                            </x-pos.utility.pill>
                                        @endforeach
                                    </div>
                                @elseif ($sch->employee)
                                    <x-pos.utility.pill variant="primary" size="xs">
                                        {{ $sch->employee->name }}
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="default" size="xs">
                                        Semua Karyawan (Umum)
                                    </x-pos.utility.pill>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td>
                                <x-pos.utility.pill variant="{{ $sch->calculation_type === 'percentage' ? 'primary' : 'success' }}" size="xs">
                                    {{ $sch->calculation_type === 'percentage' ? 'Persentase (%)' : 'Flat Nominal (Rp)' }}
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right  font-medium text-slate-800 dark:text-slate-200">
                                @if ($sch->calculation_type === 'percentage')
                                    {{ $sch->rate }}%
                                @else
                                    Rp {{ number_format($sch->rate, 0, ',', '.') }}
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-600 dark:text-slate-400 capitalize font-normal">
                                @if ($sch->applies_to === 'product')
                                    <div>Produk: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sch->targetProduct?->name ?: '-' }}</span></div>
                                @elseif ($sch->applies_to === 'category')
                                    <div>Kategori: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sch->targetCategory?->name ?: '-' }}</span></div>
                                @else
                                    <div>Semua Transaksi Sales</div>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <button
                                    type="button"
                                    wire:click="toggleSchemeStatus({{ $sch->id }})"
                                    class="cursor-pointer transition hover:opacity-80"
                                    title="Klik untuk ubah status"
                                >
                                    <x-pos.utility.pill variant="{{ $sch->is_active ? 'success' : 'default' }}" size="xs">
                                        {{ $sch->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </x-pos.utility.pill>
                                </button>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-pos.utility.button
                                        type="button"
                                        variant="warning"
                                        size="sm"
                                        icon="ph-pencil-simple"
                                        wire:click="openSchemeModal({{ $sch->id }})"
                                        title="Edit Skema"
                                    />
                                    <x-pos.utility.button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        icon="ph-trash"
                                        wire:click="deleteScheme({{ $sch->id }})"
                                        wire:confirm="Yakin ingin menghapus skema komisi ini?"
                                        title="Hapus Skema"
                                    />
                                </div>
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :total="count($schemes)" />
        </x-pos.table.container>
    @else
        <div class="p-12 text-center text-slate-400 dark:text-slate-500">
            <i class="ph ph-sliders text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-medium mb-3">Belum ada skema komisi yang dikonfigurasi.</p>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-plus-circle"
                wire:click="openSchemeModal"
            >
                Buat Skema Pertama
            </x-pos.utility.button>
        </div>
    @endif
</div>
