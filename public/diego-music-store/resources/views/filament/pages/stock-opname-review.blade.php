<x-filament-panels::page>
    @php
        $periods = $this->periods;
        $selectedPeriod = $this->selectedPeriod;
        $selectedAssignment = $this->selectedAssignment;
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ====== KOLOM KIRI: DAFTAR PERIODE OPNAME ====== --}}
        <div class="space-y-4 lg:col-span-1">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Daftar Periode Opname</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $periods->count() }} Periode</span>
            </div>

            <div class="space-y-3">
                @forelse ($periods as $period)
                    @php
                        $isSelected = $selectedPeriodId === $period->id;
                        $total = $period->assignments->count();
                        $approved = $period->assignments->where('status', 'completed')->count();
                        $submitted = $period->assignments->where('status', 'submitted')->count();
                        $revision = $period->assignments->where('status', 'needs_revision')->count();
                        $statusBadge = match ($period->status) {
                            'active' => ['label' => 'Sedang Berjalan', 'bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800'],
                            'review' => ['label' => 'Menunggu Review', 'bg' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800'],
                            'completed' => ['label' => 'Selesai', 'bg' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'],
                            default => ['label' => ucfirst($period->status), 'bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700'],
                        };
                    @endphp

                    <div
                        wire:click="selectPeriod({{ $period->id }})"
                        wire:key="period-card-{{ $period->id }}"
                        class="p-4 rounded-xl border cursor-pointer transition-all {{ $isSelected ? 'border-primary-500 bg-primary-50/40 dark:bg-primary-950/20 ring-2 ring-primary-500/20 dark:border-primary-400' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-700 shadow-sm' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $period->branch->name ?? 'Cabang' }}</span>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white truncate mt-0.5">{{ $period->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $period->period?->translatedFormat('F Y') }}</p>
                            </div>
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full border {{ $statusBadge['bg'] }} shrink-0">
                                {{ $statusBadge['label'] }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 pt-2.5 border-t border-gray-100 dark:border-gray-800 text-center">
                            <div class="p-1.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <span class="text-[9px] uppercase font-semibold text-gray-400 block">Tugas</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white">{{ $total }}</span>
                            </div>
                            <div class="p-1.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg">
                                <span class="text-[9px] uppercase font-semibold text-emerald-600 dark:text-emerald-400 block">Disetujui</span>
                                <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">{{ $approved }}</span>
                            </div>
                            <div class="p-1.5 bg-amber-50 dark:bg-amber-950/30 rounded-lg">
                                <span class="text-[9px] uppercase font-semibold text-amber-600 dark:text-amber-400 block">Review</span>
                                <span class="text-xs font-black text-amber-700 dark:text-amber-300">{{ $submitted }}</span>
                            </div>
                        </div>

                        @if ($revision > 0)
                            <div class="mt-2 text-[11px] font-bold text-rose-600 dark:text-rose-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                {{ $revision }} tugas perlu revisi
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-8 h-8 mx-auto text-gray-400" />
                        <p class="mt-2 text-sm font-bold text-gray-800 dark:text-gray-200">Belum ada periode aktif</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Aktifkan periode opname terlebih dahulu melalui menu Periode Stok Opname.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ====== KOLOM KANAN: DETAIL TUGAS & REVIEW ====== --}}
        <div class="space-y-6 lg:col-span-2">
            @if ($selectedPeriod)
                @php
                    $canFinalize = in_array($selectedPeriod->status, [\App\Models\StockOpnamePeriod::STATUS_ACTIVE, \App\Models\StockOpnamePeriod::STATUS_REVIEW], true)
                        && $selectedPeriod->assignments->isNotEmpty()
                        && $selectedPeriod->assignments->every(fn ($a) => $a->status === \App\Models\StockOpnameAssignment::STATUS_COMPLETED);

                    $statusLabels = [
                        'assigned' => ['label' => 'Belum Dimulai', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
                        'in_progress' => ['label' => 'Sedang Dikerjakan', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300'],
                        'submitted' => ['label' => 'Menunggu Review', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'],
                        'needs_revision' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'],
                        'completed' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'],
                        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'],
                    ];
                @endphp

                {{-- Card Header Periode Terpilih --}}
                <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $selectedPeriod->branch->name ?? 'Cabang' }}</span>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white mt-0.5">{{ $selectedPeriod->name }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Periksa hitungan tiap lokasi penugasan. Finalisasi akan menyesuaikan stok fisik ke sistem dan mencatat jurnal akuntansi.</p>
                        </div>

                        <div>
                            @if ($selectedPeriod->status === \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                                <div class="text-right">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-extrabold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4" />
                                        Periode Selesai
                                    </span>
                                    @if ($selectedPeriod->finalStockOpname)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Dokumen: <strong>{{ $selectedPeriod->finalStockOpname->opname_number }}</strong>
                                        </p>
                                    @endif
                                </div>
                            @elseif ($canFinalize)
                                <x-filament::button
                                    color="success"
                                    icon="heroicon-o-lock-closed"
                                    x-on:click="$dispatch('open-modal', { id: 'finalize-period-modal' })"
                                    wire:loading.attr="disabled"
                                >
                                    Finalisasi Periode
                                </x-filament::button>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 text-gray-400" />
                                    Finalisasi Menunggu Persetujuan
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($canFinalize)
                        <div class="p-3.5 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 text-xs leading-relaxed flex items-start gap-2.5">
                            <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                            <div>
                                <strong>Semua lokasi telah disetujui!</strong> Klik tombol <strong>Finalisasi Periode</strong> untuk mengagregasi hitungan fisik seluruh lokasi, membandingkan selisih dengan stok sistem, dan menerbitkan dokumen penyesuaian stok beserta jurnal akuntansi secara otomatis.
                            </div>
                        </div>
                    @elseif ($selectedPeriod->status !== \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 text-xs flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-4 h-4 text-gray-400 shrink-0" />
                            Finalisasi terkunci hingga semua tugas lokasi dikirim karyawan dan disetujui oleh pemeriksa.
                        </div>
                    @endif
                </div>

                {{-- Section Panel Review Tugas (Jika Ada Tugas yang Dipilih) --}}
                @if ($selectedAssignment && $selectedAssignment->status === \App\Models\StockOpnameAssignment::STATUS_SUBMITTED)
                    <div class="p-5 rounded-xl border-2 border-primary-500 bg-white dark:bg-gray-900 shadow-md space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Verifikasi Hitungan Lokasi</span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-0.5">
                                    {{ $selectedAssignment->preset->name }} &bull; {{ $selectedAssignment->assignedUser->name }}
                                </h3>
                            </div>
                            <x-filament::button size="xs" color="gray" wire:click="$set('selectedAssignmentId', null)">
                                Batal
                            </x-filament::button>
                        </div>

                        <div>
                            <label for="review-notes" class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                Catatan Pemeriksa / Alasan Revisi
                            </label>
                            <textarea
                                id="review-notes"
                                wire:model="reviewNotes"
                                rows="3"
                                maxlength="2000"
                                placeholder="Tuliskan catatan evaluasi (opsional jika disetujui; wajib jika meminta revisi)..."
                                class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white p-3 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            ></textarea>
                            @error('reviewNotes')
                                <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-1">
                            <x-filament::button
                                color="danger"
                                icon="heroicon-o-arrow-path"
                                wire:click="requestRevision({{ $selectedAssignment->id }})"
                                wire:confirm="Kirim kembali tugas hitungan ke karyawan untuk direvisi sesuai catatan?"
                                wire:loading.attr="disabled"
                            >
                                Minta Revisi
                            </x-filament::button>

                            <x-filament::button
                                color="success"
                                icon="heroicon-o-check"
                                wire:click="approveAssignment({{ $selectedAssignment->id }})"
                                wire:loading.attr="disabled"
                            >
                                Setujui Tugas Lokasi
                            </x-filament::button>
                        </div>
                    </div>
                @endif

                {{-- Daftar Penugasan Lokasi --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
                    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="font-extrabold text-sm text-gray-900 dark:text-white">Daftar Penugasan Lokasi</h3>
                        <span class="text-xs text-gray-400">{{ $selectedPeriod->assignments->count() }} Lokasi</span>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($selectedPeriod->assignments as $assignment)
                            @php
                                $statusInfo = $statusLabels[$assignment->status] ?? ['label' => ucfirst($assignment->status), 'class' => 'bg-gray-100 text-gray-600'];
                                $itemsCount = $assignment->stockOpname?->items?->count() ?? 0;
                            @endphp

                            <div wire:key="assignment-row-{{ $assignment->id }}" class="p-4 sm:p-5 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ $assignment->preset->name ?? 'Lokasi' }}</h4>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $statusInfo['class'] }}">
                                                {{ $statusInfo['label'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Karyawan: <strong>{{ $assignment->assignedUser->name ?? '-' }}</strong> &bull;
                                            <span>{{ $itemsCount }} produk dihitung</span>
                                            @if ($assignment->submitted_at)
                                                &bull; Dikirim {{ $assignment->submitted_at->format('d M Y H:i') }}
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        @if ($assignment->status === \App\Models\StockOpnameAssignment::STATUS_SUBMITTED && $selectedPeriod->status !== \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                                            <x-filament::button
                                                size="sm"
                                                color="primary"
                                                icon="heroicon-o-magnifying-glass"
                                                wire:click="selectAssignment({{ $assignment->id }})"
                                            >
                                                Periksa
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>

                                @if ($assignment->review_notes)
                                    <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700/60 text-xs text-gray-700 dark:text-gray-300">
                                        <span class="font-bold">Catatan Pemeriksa:</span> {{ $assignment->review_notes }}
                                    </div>
                                @endif

                                @if ($assignment->stockOpname?->items?->isNotEmpty())
                                    <details class="group mt-2">
                                        <summary class="cursor-pointer text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-chevron-right" class="w-3.5 h-3.5 group-open:rotate-90 transition-transform" />
                                            Lihat rincian hitungan produk ({{ $itemsCount }})
                                        </summary>

                                        <div class="mt-2.5 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                            <table class="w-full min-w-[500px] text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                                                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                                                    <tr>
                                                        <th class="px-3 py-2 font-bold">Produk / Varian</th>
                                                        <th class="px-3 py-2 font-bold">SKU</th>
                                                        <th class="px-3 py-2 font-bold text-right">Sistem</th>
                                                        <th class="px-3 py-2 font-bold text-right">Fisik</th>
                                                        <th class="px-3 py-2 font-bold text-right">Selisih</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                                    @foreach ($assignment->stockOpname->items as $item)
                                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">
                                                                {{ $item->productVariant?->product?->name ?? 'Produk' }}
                                                                {{ $item->productVariant?->name ? '— ' . $item->productVariant->name : '' }}
                                                            </td>
                                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $item->productVariant?->sku ?? '-' }}</td>
                                                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($item->system_qty, 0, ',', '.') }}</td>
                                                            <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-white">{{ number_format($item->physical_qty, 0, ',', '.') }}</td>
                                                            <td class="px-3 py-2 text-right font-extrabold {{ $item->difference < 0 ? 'text-rose-600 dark:text-rose-400' : ($item->difference > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400') }}">
                                                                {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </details>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-gray-500">
                                Belum ada lokasi penugasan pada periode ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="p-12 text-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="w-12 h-12 mx-auto text-gray-400" />
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mt-3">Pilih Periode Opname</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                        Pilih salah satu periode di sebelah kiri untuk memeriksa rincian penugasan, hasil hitungan fisik, serta melakukan persetujuan atau permintaan revisi.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Finalisasi Periode Stok Opname (Filament Backoffice) --}}
    <x-filament::modal id="finalize-period-modal" width="lg" icon="heroicon-o-lock-closed" icon-color="success">
        <x-slot name="heading">
            Finalisasi Periode Stok Opname
        </x-slot>

        <x-slot name="description">
            Konfirmasi perubahan stok fisik dan pencatatan jurnal penyesuaian otomatis
        </x-slot>

        @if ($selectedPeriod)
            <div class="space-y-4 text-xs">
                {{-- Periode Info Box --}}
                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-white/5">
                    <div>
                        <span class="text-gray-400 block font-medium">Periode:</span>
                        <strong class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedPeriod->name }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Cabang:</span>
                        <strong class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedPeriod->branch->name ?? 'Cabang Utama' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Tanggal Periode:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedPeriod->period->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Status Penugasan:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4" />
                            {{ $selectedPeriod->assignments->count() }} Lokasi Disetujui
                        </span>
                    </div>
                </div>

                {{-- Alert Box --}}
                <div class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-4 h-4 shrink-0" />
                        <span>Perhatian Sebelum Melanjutkan:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-amber-800/90 dark:text-amber-300/90 pl-1 leading-relaxed">
                        <li>Hitungan fisik seluruh lokasi penugasan akan dijumlahkan per SKU/varian.</li>
                        <li>Sistem akan membandingkannya dengan saldo stok sistem cabang saat ini.</li>
                        <li>Mutasi penyesuaian stok dan jurnal penyesuaian akan dibuat <strong>satu kali</strong>.</li>
                        <li>Status periode akan dikunci menjadi <strong>Selesai</strong> dan tidak dapat diubah lagi.</li>
                    </ul>
                </div>
            </div>
        @endif

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-3 w-full">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'finalize-period-modal' })"
                >
                    Batal
                </x-filament::button>

                <x-filament::button
                    color="success"
                    icon="heroicon-o-lock-closed"
                    wire:click="finalizePeriod({{ $selectedPeriod?->id }})"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="finalizePeriod">
                        Ya, Finalisasi Sekarang
                    </span>
                    <span wire:loading wire:target="finalizePeriod">
                        Memproses...
                    </span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
