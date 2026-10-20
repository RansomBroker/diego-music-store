<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl ?? null" />

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <x-pos.toast />
        <x-pos.navbar pageTitle="Review Stok Opname" backLabel="Dashboard" />

        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">
                <div>
                    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Review Stok Opname</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Review Stok Opname</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Periksa hitungan per lokasi, minta revisi jika perlu, lalu finalisasi periode setelah seluruh lokasi disetujui. Stok dan jurnal hanya diproses sekali pada finalisasi periode.</p>
                </div>

                @error('reviewNotes')
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">{{ $message }}</div>
                @enderror

                <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <div class="space-y-3 xl:col-span-1">
                        <h2 class="font-black text-slate-900 dark:text-white">Periode opname</h2>
                        @forelse ($periods as $period)
                            @php
                                $total = $period->assignments->count();
                                $approved = $period->assignments->where('status', 'completed')->count();
                                $submitted = $period->assignments->where('status', 'submitted')->count();
                                $revision = $period->assignments->where('status', 'needs_revision')->count();
                                $periodStatus = [
                                    'active' => 'Sedang berjalan',
                                    'review' => 'Menunggu final review',
                                    'completed' => 'Selesai',
                                ];
                            @endphp
                            <button wire:click="selectPeriod({{ $period->id }})" wire:key="review-period-{{ $period->id }}" class="w-full rounded-2xl border p-4 text-left transition {{ $selectedPeriodId === $period->id ? 'border-primary bg-primary/5 ring-2 ring-primary/20 dark:border-blue-400 dark:bg-blue-950/20' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $period->branch->name ?? 'Cabang' }}</p>
                                        <h3 class="mt-1 font-black text-slate-900 dark:text-white">{{ $period->name }}</h3>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $period->period?->translatedFormat('F Y') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $periodStatus[$period->status] ?? $period->status }}</span>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-3 dark:border-slate-700">
                                    <div><p class="text-[10px] uppercase text-slate-400">Tugas</p><p class="font-black text-slate-900 dark:text-white">{{ $total }}</p></div>
                                    <div><p class="text-[10px] uppercase text-slate-400">Disetujui</p><p class="font-black text-emerald-600">{{ $approved }}</p></div>
                                    <div><p class="text-[10px] uppercase text-slate-400">Review</p><p class="font-black text-amber-600">{{ $submitted }}</p></div>
                                </div>
                                @if ($revision > 0)
                                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $revision }} tugas perlu revisi</p>
                                @endif
                            </button>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-800">
                                <i class="ph ph-clipboard-text text-3xl text-slate-400"></i>
                                <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Belum ada periode untuk direview</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Periode akan muncul setelah dibuat dan diaktifkan.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="space-y-4 xl:col-span-2">
                        @if ($selectedPeriod)
                            @php
                                $canFinalize = in_array($selectedPeriod->status, [\App\Models\StockOpnamePeriod::STATUS_ACTIVE, \App\Models\StockOpnamePeriod::STATUS_REVIEW], true)
                                    && $selectedPeriod->assignments->isNotEmpty()
                                    && $selectedPeriod->assignments->every(fn ($assignment) => $assignment->status === \App\Models\StockOpnameAssignment::STATUS_COMPLETED);
                                $statusLabels = [
                                    'assigned' => 'Belum dimulai',
                                    'in_progress' => 'Sedang dikerjakan',
                                    'submitted' => 'Menunggu review',
                                    'needs_revision' => 'Perlu revisi',
                                    'completed' => 'Disetujui',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $selectedPeriod->branch->name ?? 'Cabang' }}</p>
                                        <h2 class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ $selectedPeriod->name }}</h2>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Periksa setiap lokasi sebelum finalisasi.</p>
                                    </div>
                                    @if ($selectedPeriod->status === \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                                        <div class="text-right"><span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"><i class="ph-bold ph-check-circle"></i> Sudah difinalisasi</span>@if ($selectedPeriod->finalStockOpname)<p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Dokumen: <strong>{{ $selectedPeriod->finalStockOpname->opname_number }}</strong></p>@endif</div>
                                    @elseif ($canFinalize)
                                        <x-pos.utility.button wire:click="openFinalizeModal({{ $selectedPeriod->id }})" wire:loading.attr="disabled" class="!rounded-xl !px-4 !py-2">
                                            <i class="ph-bold ph-lock-key mr-1"></i> Finalisasi periode
                                        </x-pos.utility.button>
                                    @endif
                                </div>

                                @if ($canFinalize)
                                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                                        Semua tugas lokasi sudah disetujui. Setiap lokasi wajib memiliki hitungan untuk seluruh varian yang terdaftar pada stok cabang; masukkan 0 jika produk tidak ada di lokasi tersebut. Finalisasi menjumlahkan hitungan antar lokasi per SKU/varian, membandingkannya dengan stok sistem cabang saat finalisasi, lalu menjalankan penyesuaian stok dan jurnal satu kali.
                                    </div>
                                @elseif ($selectedPeriod->status !== \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                                    <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                                        Finalisasi terkunci sampai semua tugas lokasi disetujui dan tidak ada tugas yang masih perlu dikerjakan atau direvisi.
                                    </div>
                                @endif
                            </section>

                            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                                    <h3 class="font-black text-slate-900 dark:text-white">Penugasan lokasi</h3>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @forelse ($selectedPeriod->assignments as $assignment)
                                        <article wire:key="review-assignment-{{ $assignment->id }}" class="p-4 sm:p-5">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="min-w-0">
                                                    <h4 class="font-black text-slate-900 dark:text-white">{{ $assignment->preset->name ?? 'Lokasi' }}</h4>
                                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $assignment->assignedUser->name ?? 'Karyawan' }}</p>
                                                    <p class="mt-1 text-xs text-slate-400">{{ $assignment->stockOpname?->items?->count() ?? 0 }} produk dihitung
                                                        @if ($assignment->submitted_at) · dikirim {{ $assignment->submitted_at->format('d M Y H:i') }} @endif
                                                    </p>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $statusLabels[$assignment->status] ?? $assignment->status }}</span>
                                                    @if ($assignment->status === \App\Models\StockOpnameAssignment::STATUS_SUBMITTED && $selectedPeriod->status !== \App\Models\StockOpnamePeriod::STATUS_COMPLETED)
                                                        <x-pos.utility.button wire:click="selectAssignment({{ $assignment->id }})" class="!rounded-lg !px-3 !py-1.5">Periksa</x-pos.utility.button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($assignment->review_notes)
                                                <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                                                    <span class="font-bold">Catatan pemeriksa:</span> {{ $assignment->review_notes }}
                                                </div>
                                            @endif

                                            @if ($assignment->stockOpname?->items?->isNotEmpty())
                                                <details class="mt-4">
                                                    <summary class="cursor-pointer text-sm font-bold text-primary hover:underline dark:text-blue-400">Lihat rincian hitungan ({{ $assignment->stockOpname->items->count() }})</summary>
                                                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                                        <table class="w-full min-w-[520px] text-left text-xs">
                                                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                                                <tr><th class="px-3 py-2">Produk</th><th class="px-3 py-2">SKU</th><th class="px-3 py-2 text-right">Sistem saat input</th><th class="px-3 py-2 text-right">Fisik</th><th class="px-3 py-2 text-right">Selisih</th></tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                                                @foreach ($assignment->stockOpname->items as $item)
                                                                    <tr>
                                                                        <td class="px-3 py-2 font-semibold text-slate-800 dark:text-slate-100">{{ $item->productVariant?->product?->name ?? 'Produk' }} {{ $item->productVariant?->name ? '— '.$item->productVariant->name : '' }}</td>
                                                                        <td class="px-3 py-2 text-slate-500">{{ $item->productVariant?->sku ?? '-' }}</td>
                                                                        <td class="px-3 py-2 text-right">{{ number_format($item->system_qty, 0, ',', '.') }}</td>
                                                                        <td class="px-3 py-2 text-right font-bold">{{ number_format($item->physical_qty, 0, ',', '.') }}</td>
                                                                        <td class="px-3 py-2 text-right {{ $item->difference < 0 ? 'text-rose-600' : ($item->difference > 0 ? 'text-emerald-600' : 'text-slate-500') }}">{{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            @endif
                                        </article>
                                    @empty
                                        <p class="p-6 text-sm text-slate-500">Belum ada penugasan pada periode ini.</p>
                                    @endforelse
                                </div>
                            </section>

                            @if ($selectedAssignment && $selectedAssignment->status === \App\Models\StockOpnameAssignment::STATUS_SUBMITTED)
                                <section class="rounded-2xl border border-primary/30 bg-white p-5 shadow-sm dark:border-blue-900 dark:bg-slate-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-primary dark:text-blue-400">Review lokasi</p>
                                            <h3 class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $selectedAssignment->preset->name }} — {{ $selectedAssignment->assignedUser->name }}</h3>
                                        </div>
                                        <button wire:click="$set('selectedAssignmentId', null)" class="rounded-lg px-3 py-1 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">Tutup</button>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="review-notes" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Catatan review / alasan revisi</label>
                                            <textarea id="review-notes" wire:model="reviewNotes" rows="3" maxlength="2000" placeholder="Opsional jika disetujui; wajib jika meminta revisi..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                                            @error('reviewNotes') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <x-pos.utility.button wire:click="requestRevision({{ $selectedAssignment->id }})" wire:confirm="Kirim tugas ini kembali ke karyawan untuk direvisi?" wire:loading.attr="disabled" class="!rounded-xl !bg-rose-600 hover:!bg-rose-700">
                                                <i class="ph-bold ph-arrow-counter-clockwise mr-1"></i> Minta revisi
                                            </x-pos.utility.button>
                                            <x-pos.utility.button wire:click="approveAssignment({{ $selectedAssignment->id }})" wire:loading.attr="disabled" class="!rounded-xl">
                                                <i class="ph-bold ph-check mr-1"></i> Setujui tugas
                                            </x-pos.utility.button>
                                        </div>
                                    </div>
                                </section>
                            @endif
                        @else
                            <div class="flex min-h-64 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-800">
                                <div>
                                    <i class="ph ph-clipboard-text text-4xl text-slate-400"></i>
                                    <h2 class="mt-3 font-black text-slate-900 dark:text-white">Pilih periode opname</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar tugas dan hasil hitungan lokasi akan ditampilkan di sini.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>

    {{-- Modal Finalisasi Periode Stok Opname (POS) --}}
    <x-pos.modal
        wire:model="showFinalizeModal"
        title="Finalisasi Periode Stok Opname"
        subtitle="Konfirmasi perubahan stok fisik dan pencatatan jurnal akuntansi"
        icon="ph-lock-key"
        maxWidth="lg"
    >
        @if ($selectedPeriod)
            <div class="space-y-5 text-slate-700 dark:text-slate-200">
                {{-- Summary Card --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 p-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Periode:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $selectedPeriod->name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Cabang:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $selectedPeriod->branch->name ?? 'Semua Cabang' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Total Lokasi Penugasan:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                            <i class="ph-bold ph-check-circle"></i>
                            {{ $selectedPeriod->assignments->count() }} Lokasi (Semua Disetujui)
                        </span>
                    </div>
                </div>

                {{-- Warning / Notice Card --}}
                <div class="rounded-2xl border border-amber-200/80 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-950/30 p-4 text-xs text-amber-900 dark:text-amber-200 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                        <i class="ph-bold ph-warning-circle text-base"></i>
                        <span>Perhatian Sebelum Finalisasi:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-amber-800/90 dark:text-amber-300/90 pl-1 leading-relaxed">
                        <li>Hitungan fisik seluruh lokasi penugasan akan dijumlahkan per SKU/varian.</li>
                        <li>Sistem akan membandingkannya dengan saldo stok sistem cabang saat ini.</li>
                        <li>Mutasi penyesuaian stok dan jurnal akuntansi akan otomatis dicatat <strong>satu kali</strong>.</li>
                        <li>Status periode akan berubah menjadi <strong>Selesai</strong> dan tidak dapat diubah kembali.</li>
                    </ul>
                </div>

                {{-- Actions Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeFinalizeModal"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                    >
                        Batal
                    </button>

                    <x-pos.utility.button
                        wire:click="finalizePeriod"
                        wire:loading.attr="disabled"
                        class="!rounded-xl !px-5 !py-2.5 !bg-primary hover:!bg-primary/90 text-xs font-bold"
                    >
                        <span wire:loading.remove wire:target="finalizePeriod">
                            <i class="ph-bold ph-lock-key mr-1.5"></i> Ya, Finalisasi Sekarang
                        </span>
                        <span wire:loading wire:target="finalizePeriod" class="inline-flex items-center gap-2">
                            <i class="ph ph-spinner animate-spin"></i> Memproses Finalisasi...
                        </span>
                    </x-pos.utility.button>
                </div>
            </div>
        @endif
    </x-pos.modal>
</div>
