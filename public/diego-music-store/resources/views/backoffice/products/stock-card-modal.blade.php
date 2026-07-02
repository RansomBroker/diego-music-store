<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="p-6 bg-gray-50/60 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full capitalize {{
                        match(strtolower($product->type ?? '')) {
                            'physical' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                            'bundle' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                            'service' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                        }
                    }}">
                        {{ $product->type === 'physical' ? 'Fisik' : ($product->type === 'bundle' ? 'Paket / Bundle' : 'Jasa') }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">ID: #{{ $product->id }}</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $product->description ?: 'Tidak ada deskripsi produk.' }}</p>
            </div>
            
            <div class="flex flex-col items-start md:items-end">
                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Total Stok (Semua Cabang)</span>
                <span class="text-2xl font-black text-gray-900 dark:text-white font-mono mt-1">
                    @if($product->isService())
                        &infin;
                    @else
                        {{ number_format($product->variants->first()?->totalStock() ?? 0, 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-1">{{ $product->unit->name ?? 'Unit' }}</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Product Type: SERVICE -->
    @if($product->isService())
        <div class="flex flex-col items-center justify-center py-12 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-full mb-3">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h4 class="text-base font-bold text-gray-900 dark:text-white">Produk Jasa (Service)</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mt-1">Produk ini bertipe Jasa. Tidak memiliki pencatatan fisik stok atau riwayat kartu stok karena ketersediaannya tidak dibatasi oleh inventori.</p>
        </div>

    <!-- Product Type: BUNDLE -->
    @elseif($product->isBundle())
        <div class="space-y-4">
            <div class="border-b border-gray-200 dark:border-gray-800 pb-2">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Komponen Penyusun Bundle</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Klik pada setiap produk komponen di bawah untuk melihat rincian riwayat kartu stoknya</p>
            </div>

            @forelse($bundleItems as $index => $item)
                @php
                    $childVariant = $item->childVariant;
                    $childProduct = $childVariant->product;
                    $movements = $childMovements[$childVariant->id] ?? collect();
                @endphp

                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-900 transition-all hover:border-gray-305 dark:hover:border-gray-700">
                    <!-- Accordion Trigger Header -->
                    <button @click="open = !open" type="button" class="w-full flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-800/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/40 border-b border-transparent transition-all" :class="open ? 'border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50' : ''">
                        <div class="flex items-center gap-3 text-left">
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 font-mono">Komponen #{{ $index + 1 }}</span>
                                <h5 class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $childProduct->name }}</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono">SKU: {{ $childVariant->sku }} | Barcode: {{ $childVariant->barcode ?: '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 mt-3 sm:mt-0 ml-10 sm:ml-0">
                            <div class="text-right">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold block">Kebutuhan / Bundle</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ $item->quantity }} {{ $childProduct->unit->name ?? 'Unit' }}</span>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold block">Stok Komponen</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white font-mono">{{ number_format($childVariant->totalStock(), 0, ',', '.') }} {{ $childProduct->unit->name ?? 'Unit' }}</span>
                            </div>

                            <!-- Chevron Icon -->
                            <div class="text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180 text-gray-600' : ''">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="open" x-collapse x-cloak class="p-4 bg-white dark:bg-gray-900 space-y-4">
                        <!-- Branch Stock Summary for Component -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($branches as $branch)
                                <div class="p-2.5 rounded-lg bg-gray-50/50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-center">
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-bold block">{{ $branch->name }}</span>
                                    <span class="text-sm font-extrabold text-gray-900 dark:text-gray-200 font-mono block mt-0.5">
                                        {{ number_format($childVariant->stockForBranch($branch->id), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Component Stock Movements Table -->
                        <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                            <div class="max-h-72 overflow-y-auto">
                                <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 sticky top-0 uppercase tracking-wider text-[10px] border-b border-gray-200 dark:border-gray-800">
                                        <tr>
                                            <th class="px-4 py-2.5 font-bold">Tanggal & Waktu</th>
                                            <th class="px-4 py-2.5 font-bold">Cabang</th>
                                            <th class="px-4 py-2.5 font-bold text-center">Tipe</th>
                                            <th class="px-4 py-2.5 font-bold text-right">Jumlah</th>
                                            <th class="px-4 py-2.5 font-bold text-right">Harga Satuan</th>
                                            <th class="px-4 py-2.5 font-bold text-right">HPP Berjalan</th>
                                            <th class="px-4 py-2.5 font-bold text-right">Total Nilai</th>
                                            <th class="px-4 py-2.5 font-bold">Keterangan / Referensi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                        @forelse($movements as $mv)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                                <td class="px-4 py-2.5 font-mono text-gray-700 dark:text-gray-400">
                                                    {{ $mv->created_at->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-gray-200">
                                                    {{ $mv->branch->name ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-center">
                                                    @if($mv->type === 'in')
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                            Masuk
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                                            Keluar
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono font-bold text-gray-900 dark:text-gray-200">
                                                    {{ number_format($mv->quantity, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono text-gray-900 dark:text-white">
                                                    Rp {{ number_format($mv->unit_cost, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono text-gray-900 dark:text-white">
                                                    Rp {{ number_format($mv->hpp, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono text-gray-900 dark:text-white">
                                                    Rp {{ number_format($mv->quantity * $mv->unit_cost, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2.5 text-gray-800 dark:text-gray-300 font-medium">
                                                    {{ $mv->reference_label }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-500">
                                                    Belum ada riwayat pergerakan stok untuk komponen ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                    Belum ada data komponen penyusun bundle yang dikonfigurasi untuk paket ini.
                </div>
            @endforelse
        </div>

    <!-- Product Type: PHYSICAL -->
    @else
        @foreach($product->variants as $vIndex => $variant)
            <div class="space-y-4">
                @if($product->variants->count() > 1)
                    <div class="border-b border-gray-200 dark:border-gray-800 pb-1.5">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            Varian: <span class="text-blue-600 dark:text-blue-400">{{ $variant->name ?: 'Varian ' . ($vIndex + 1) }}</span>
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">SKU: {{ $variant->sku }} | Barcode: {{ $variant->barcode ?: '-' }}</p>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-mono">
                        <span>SKU: <strong class="text-gray-700 dark:text-gray-300 font-semibold">{{ $variant->sku }}</strong></span>
                        <span>|</span>
                        <span>Barcode: <strong class="text-gray-700 dark:text-gray-300 font-semibold">{{ $variant->barcode ?: '-' }}</strong></span>
                    </div>
                @endif

                <!-- Branch Stock Summary for Variant -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($branches as $branch)
                        <div class="p-2.5 rounded-lg bg-gray-50/50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 text-center">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-bold block">{{ $branch->name }}</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-gray-200 font-mono block mt-0.5">
                                {{ number_format($variant->stockForBranch($branch->id), 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                @php
                    $movements = $physicalMovements[$variant->id] ?? collect();
                @endphp

                <!-- Stock Movements Table for Physical Product -->
                <div class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                            <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 sticky top-0 uppercase tracking-wider text-[10px] border-b border-gray-200 dark:border-gray-800">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Tanggal & Waktu</th>
                                    <th class="px-4 py-3 font-bold">Cabang</th>
                                    <th class="px-4 py-3 font-bold text-center">Tipe</th>
                                    <th class="px-4 py-3 font-bold text-right">Jumlah</th>
                                    <th class="px-4 py-3 font-bold text-right">Harga Satuan</th>
                                    <th class="px-4 py-3 font-bold text-right">HPP Berjalan</th>
                                    <th class="px-4 py-3 font-bold text-right">Total Nilai</th>
                                    <th class="px-4 py-3 font-bold">Keterangan / Referensi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                @forelse($movements as $mv)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-400">
                                            {{ $mv->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-200">
                                            {{ $mv->branch->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($mv->type === 'in')
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                    Masuk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                                    Keluar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-gray-200">
                                            {{ number_format($mv->quantity, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                            Rp {{ number_format($mv->unit_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                            Rp {{ number_format($mv->hpp, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900 text-gray-900 dark:text-white">
                                            Rp {{ number_format($mv->quantity * $mv->unit_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-800 dark:text-gray-300 font-medium">
                                            {{ $mv->reference_label }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-500">
                                            Belum ada data pergerakan stok yang tercatat untuk varian ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
