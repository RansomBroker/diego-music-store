<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <x-pos.toast />
        <x-pos.navbar pageTitle="Tugas Stok Opname" backLabel="Dashboard" />

        <div class="flex-1 overflow-y-auto no-scrollbar p-4 sm:p-6">
            <div class="mx-auto w-full max-w-7xl space-y-6">
                @if (!$activeAssignment)
                    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-primary dark:text-blue-400">Inventori / Opname</p>
                            <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">Tugas Stok Opname Saya</h1>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih lokasi yang ditugaskan, hitung stok fisik, lalu kirim untuk diperiksa.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Tugas ditampilkan:</span>
                            <strong class="ml-1 text-slate-900 dark:text-white">{{ $assignments->count() }}</strong>
                        </div>
                    </header>

                    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        @forelse ($assignments as $assignment)
                            @php
                                $isEditable = $assignment->period->status === AppModelsStockOpnamePeriod::STATUS_ACTIVE
                                    && in_array($assignment->status, [
                                        AppModelsStockOpnameAssignment::STATUS_ASSIGNED,
                                        AppModelsStockOpnameAssignment::STATUS_IN_PROGRESS,
                                        AppModelsStockOpnameAssignment::STATUS_NEEDS_REVISION,
                                    ], true);
                                $countedItems = $assignment->stockOpname?->items?->count() ?? 0;
                                $statusLabels = [
                                    'assigned' => 'Belum dimulai',
                                    'in_progress' => 'Sedang dikerjakan',
                                    'submitted' => 'Menunggu review',
                                    'needs_revision' => 'Perlu revisi',
                                    'completed' => 'Disetujui',
                                    'cancelled' => 'Dibatalkan',
                                ];
                                $statusColors = [
                                    'assigned' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                    'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'submitted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    'needs_revision' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                                    'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'cancelled' => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                                ];
                            @endphp
                            <article wire:key="opname-assignment-{{ $assignment->id }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $assignment->period->branch->name ?? 'Cabang' }}</p>
                                        <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $assignment->preset->name }}</h2>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $assignment->period->name }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $assignment->period->period?->translatedFormat('F Y') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $statusColors[$assignment->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$assignment->status] ?? ucfirst($assignment->status) }}
                                    </span>
                                </div>

                                @if ($assignment->review_notes && $assignment->status === AppModelsStockOpnameAssignment::STATUS_NEEDS_REVISION)
                                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">
                                        <p class="font-bold">Catatan revisi</p>
                                        <p class="mt-1">{{ $assignment->review_notes }}</p>
                                    </div>
                                @endif

                                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $countedItems }}</span> produk dihitung
                                    </div>
                                    @if ($isEditable)
                                        <x-pos.utility.button wire:click="startTask({{ $assignment->id }})" wire:loading.attr="disabled" class="!rounded-xl !px-4 !py-2">
                                            <i class="ph-bold ph-clipboard-text mr-1"></i>
                                            {{ $assignment->status === 'assigned' ? 'Mulai hitung' : ($assignment->status === 'needs_revision' ? 'Perbaiki hitungan' : 'Lanjutkan') }}
                                        </x-pos.utility.button>
                                    @elseif ($assignment->status === AppModelsStockOpnameAssignment::STATUS_SUBMITTED)
                                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">Menunggu pemeriksaan owner</span>
                                    @elseif ($assignment->status === AppModelsStockOpnameAssignment::STATUS_COMPLETED)
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Tugas disetujui</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-800">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                    <i class="ph ph-clipboard-text text-2xl"></i>
                                </div>
                                <h2 class="mt-4 font-bold text-slate-900 dark:text-white">Belum ada tugas aktif</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tugas akan muncul di sini setelah periode stok opname diaktifkan oleh owner.</p>
                            </div>
                        @endforelse
                    </section>
                @else
                    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <button wire:click="backToTasks" class="mb-3 inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline dark:text-blue-400">
                                <i class="ph-bold ph-arrow-left"></i> Kembali ke tugas
                            </button>
                            <p class="text-xs font-bold uppercase tracking-widest text-primary dark:text-blue-400">{{ $activeAssignment->period->name }}</p>
                            <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">Hitung: {{ $activeAssignment->preset->name }}</h1>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cari produk berdasarkan nama, SKU, barcode, atau kategori. Masukkan jumlah fisik yang benar-benar ditemukan di lokasi ini.</p>
                        </div>
                        <div class="flex gap-2">
                            <x-pos.utility.button wire:click="submitTask" wire:loading.attr="disabled" class="!rounded-xl !px-4 !py-2">
                                <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Kirim untuk review
                            </x-pos.utility.button>
                        </div>
                    </header>

                    @error('search')
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</div>
                    @enderror

                    @if ($activeAssignment->status === \App\Models\StockOpnameAssignment::STATUS_NEEDS_REVISION && $activeAssignment->review_notes)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">
                            <p class="font-black">Catatan revisi dari pemeriksa</p>
                            <p class="mt-1">{{ $activeAssignment->review_notes }}</p>
                        </div>
                    @endif

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                            <label for="opname-product-search" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Cari produk</label>
                            <div class="relative">
                                <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                                <input id="opname-product-search" type="search" wire:model.live.debounce.350ms="search" placeholder="Nama produk, SKU, barcode, kategori..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
                            </div>
                            <p class="mt-2 text-xs text-slate-400">Maksimal 20 hasil ditampilkan untuk menjaga pencarian tetap ringan. Jumlah stok sistem tidak ditampilkan agar hitungan fisik tetap objektif.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                            @forelse ($products as $variant)
                                @php
                                    $counted = $activeAssignment->stockOpname?->items?->firstWhere('product_variant_id', $variant->id);
                                @endphp
                                <article wire:key="variant-{{ $variant->id }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $variant->product->name }}</h3>
                                        @if ($variant->name)
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $variant->name }}</p>
                                        @endif
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            @if ($variant->sku)
                                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $variant->sku }}</span>
                                            @endif
                                            @if ($variant->product->category)
                                                <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ $variant->product->category }}</span>
                                            @endif
                                            @if ($counted)
                                                <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Terhitung: {{ $counted->physical_qty }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <x-pos.utility.button wire:click="chooseProduct({{ $variant->id }})" class="!shrink-0 !rounded-lg !px-3 !py-2">
                                        {{ $counted ? 'Ubah' : 'Hitung' }}
                                    </x-pos.utility.button>
                                </article>
                            @empty
                                <div class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    {{ trim($search) === '' ? 'Ketik nama produk, SKU, barcode, atau kategori untuk mulai mencari.' : 'Produk tidak ditemukan. Coba kata kunci lain.' }}
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                            <h2 class="font-black text-slate-900 dark:text-white">Produk yang sudah dihitung</h2>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($activeAssignment->stockOpname?->items ?? [] as $item)
                                <div wire:key="counted-item-{{ $item->id }}" class="flex items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $item->productVariant?->product?->name ?? 'Produk' }} {{ $item->productVariant?->name ? '— '.$item->productVariant->name : '' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">SKU: {{ $item->productVariant?->sku ?? '-' }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($item->physical_qty, 0, ',', '.') }} unit</p>
                                        <button wire:click="chooseProduct({{ $item->product_variant_id }})" class="text-xs font-bold text-primary hover:underline dark:text-blue-400">Ubah hitungan</button>
                                    </div>
                                </div>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada produk yang dihitung.</p>
                            @endforelse
                        </div>
                    </section>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                        <strong>Catatan:</strong> Mengirim tugas hanya mengunci hitungan untuk diperiksa. Stok cabang dan jurnal akuntansi belum diubah pada tahap ini.
                    </div>
                @endif
            </div>
        </div>
    </main>

    <x-pos.modal wire:model="showCountModal" title="Input Jumlah Fisik" subtitle="Hitung produk di lokasi yang ditugaskan" icon="ph-package" maxWidth="md">
        @if ($selectedVariant)
            <div class="mb-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-900">
                <p class="font-bold text-slate-900 dark:text-white">{{ $selectedVariant->product->name }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $selectedVariant->name }} · SKU {{ $selectedVariant->sku ?? '-' }}</p>
            </div>
        @endif
        <form wire:submit="saveCount" class="space-y-4">
            <div>
                <label for="physical-qty" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Jumlah fisik ditemukan</label>
                <input id="physical-qty" type="number" min="0" max="1000000" step="1" wire:model="selectedPhysicalQty" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-lg font-black outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white" />
                @error('selectedPhysicalQty') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2">
                <x-pos.utility.button type="button" wire:click="$set('showCountModal', false)" class="!rounded-xl !bg-slate-100 !text-slate-700 dark:!bg-slate-700 dark:!text-slate-100">Batal</x-pos.utility.button>
                <x-pos.utility.button type="submit" wire:loading.attr="disabled" class="!rounded-xl">Simpan hitungan</x-pos.utility.button>
            </div>
        </form>
    </x-pos.modal>
</div>
