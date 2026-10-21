@php
    $canViewCost = auth()->check() && (auth()->user()->can('inventory.view_cost') || auth()->user()->hasRole(['owner', 'super_admin']));
@endphp

<!-- Table List -->
<x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>SKU / Barcode</x-pos.table.th>
                    <x-pos.table.th>Nama Produk & Variasi</x-pos.table.th>
                    <x-pos.table.th>Kategori & Merk</x-pos.table.th>
                    <x-pos.table.th class="text-center">Stok Fisik</x-pos.table.th>
                    <x-pos.table.th class="text-center">Batas Min</x-pos.table.th>
                    <x-pos.table.th class="text-center">Satuan</x-pos.table.th>
                    <x-pos.table.th class="text-center">Status Stok</x-pos.table.th>
                    <x-pos.table.th class="text-center">Diskon</x-pos.table.th>
                    <x-pos.table.th class="text-center">PPN</x-pos.table.th>
                    @if ($canViewCost)
                        <x-pos.table.th class="text-right">
                            <div class="pb-1 text-slate-700 dark:text-slate-200">Harga Beli</div>
                            <div class="pt-1 border-t border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">Harga Beli (HPP)</div>
                        </x-pos.table.th>
                    @endif
                    <x-pos.table.th class="text-right">Harga Jual</x-pos.table.th>
                    <x-pos.table.th class="text-right">Harga Jual (Netto)</x-pos.table.th>
                    @if ($canViewCost)
                        <x-pos.table.th class="text-right">Total Nilai HPP</x-pos.table.th>
                    @endif
                    <x-pos.table.th class="text-right">Total Nilai Jual</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @php
                    $displayRows = $reportData['paginated_rows'] ?? ($reportData['rows'] ?? []);
                @endphp
                @forelse ($displayRows as $row)
                    <x-pos.table.tr wire:key="stock-row-{{ $row['sku'] ?? $loop->index }}">
                        <x-pos.table.td class=" text-xs text-slate-900 dark:text-white">
                            <div class="font-bold text-primary dark:text-blue-400">{{ $row['sku'] }}</div>
                            <div class="text-[10px] text-slate-400">BC: {{ $row['barcode'] }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">
                            {{ $row['full_name'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-200 block w-fit">{{ $row['category'] }}</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $row['brand'] }}</span>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center  font-extrabold text-xs text-slate-900 dark:text-white">
                            {{ number_format($row['stock'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center  text-xs text-slate-500">
                            {{ number_format($row['min_stock'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center text-xs text-slate-500 font-semibold">
                            {{ $row['unit'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            @if ($row['badge_color'] === 'danger')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif ($row['badge_color'] === 'warning')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif ($row['badge_color'] === 'info')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @endif
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center  text-xs text-slate-600 dark:text-slate-300">
                            {{ $row['discount'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center  text-xs text-slate-600 dark:text-slate-300">
                            {{ $row['tax'] }}
                        </x-pos.table.td>

                        @if ($canViewCost)
                            <x-pos.table.td class="text-right text-xs whitespace-nowrap">
                                <div class="pb-1 font-semibold text-rose-600 dark:text-rose-400">
                                    Rp {{ number_format($row['cost_price'], 0, ',', '.') }}
                                </div>
                                <div class="pt-1 border-t border-slate-200 dark:border-slate-800 font-semibold text-rose-600 dark:text-rose-400">
                                    Rp {{ number_format($row['hpp'], 0, ',', '.') }}
                                </div>
                            </x-pos.table.td>
                        @endif

                        <x-pos.table.td class="text-right  text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                            Rp {{ number_format($row['retail_price'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right text-xs text-teal-600 dark:text-teal-400 font-bold">
                            Rp {{ number_format($row['net_retail_price'], 0, ',', '.') }}
                        </x-pos.table.td>

                        @if ($canViewCost)
                            <x-pos.table.td class="text-right  font-extrabold text-xs text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($row['valuation'], 0, ',', '.') }}
                            </x-pos.table.td>
                        @endif

                        <x-pos.table.td class="text-right  font-extrabold text-xs text-purple-600 dark:text-purple-400">
                            Rp {{ number_format($row['retail_value'], 0, ',', '.') }}
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="{{ $canViewCost ? 14 : 12 }}" icon="ph-package" message="Belum ada data stok produk ditemukan." />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :paginator="$reportData['paginated_rows'] ?? null" :total="count($reportData['rows'] ?? [])" perPageModel="perPage" />
    </x-pos.table.container>
