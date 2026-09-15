<!-- Table Card (Dynamic per View Mode) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
        <span>
            @if ($viewMode === 'detail')
                Detail Transaksi Penjualan Harian (Per Barang)
            @elseif ($viewMode === 'per_day')
                Ringkasan Penjualan Per Hari
            @elseif ($viewMode === 'per_nota')
                Ringkasan Penjualan Per Nota / Invoice
            @elseif ($viewMode === 'top_selling')
                Laporan Produk Terlaris (Top Selling)
            @endif
        </span>
        <span class="text-sm text-slate-400 font-normal uppercase">Mode: {{ str_replace('_', ' ', $viewMode) }}</span>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            @if ($viewMode === 'detail')
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>Tanggal</x-pos.table.th>
                        <x-pos.table.th>Kategori Jual</x-pos.table.th>
                        <x-pos.table.th>No. Invoice</x-pos.table.th>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>Catatan / Note</x-pos.table.th>
                        <x-pos.table.th>Kasir</x-pos.table.th>
                        <x-pos.table.th>Sales</x-pos.table.th>
                        <x-pos.table.th>Jenis Bayar</x-pos.table.th>
                        <x-pos.table.th>Kode Barang</x-pos.table.th>
                        <x-pos.table.th>Nama Barang</x-pos.table.th>
                        <x-pos.table.th class="text-center">Jlh / Qty</x-pos.table.th>
                        <x-pos.table.th class="text-right">Harga Satuan</x-pos.table.th>
                        <x-pos.table.th class="text-right">Diskon Item</x-pos.table.th>
                        <x-pos.table.th class="text-right">PPN / Pajak</x-pos.table.th>
                        <x-pos.table.th class="text-right">Subtotal</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($paginatedReportData['paginated_items'] ?? [] as $item)
                        <x-pos.table.tr class="align-top">
                            @if (!empty($item['is_first_item']))
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['invoice_date'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200">{{ $item['sale_category'] }}</span>
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-mono font-bold text-primary dark:text-blue-400 whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['invoice_number'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['customer_name'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-800 dark:text-slate-200 italic whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['notes'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['cashier_name'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['sales_rep_name'] }}
                                </x-pos.table.td>
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                    {{ $item['payment_method'] }}
                                </x-pos.table.td>
                            @endif
                            <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $item['sku'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $item['product_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $item['quantity'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                            @if (!empty($item['is_first_item']))
                                <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="text-right font-mono font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-l border-slate-200 dark:border-slate-800 align-top">
                                    Rp {{ number_format($item['tax_amount'], 0, ',', '.') }}
                                </x-pos.table.td>
                            @endif
                            <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="15" icon="ph-receipt" message="Belum ada transaksi penjualan harian pada periode ini." />
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'per_day')
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>Tanggal</x-pos.table.th>
                        <x-pos.table.th class="text-center">Jumlah Nota / Transaksi</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Subtotal</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Diskon</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total PPN / Pajak</x-pos.table.th>
                        <x-pos.table.th class="text-right">Grand Total Penjualan</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($paginatedReportData['paginated_items'] ?? [] as $row)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $row['date'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white"><span class="px-2.5 py-1 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-600 font-bold">{{ $row['invoice_count'] }} Nota</span></x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($row['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($row['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="6" icon="ph-calendar" message="Belum ada data penjualan per hari pada periode ini." />
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'per_nota')
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>No. Invoice</x-pos.table.th>
                        <x-pos.table.th>Tanggal</x-pos.table.th>
                        <x-pos.table.th>Kategori Jual</x-pos.table.th>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>Kasir</x-pos.table.th>
                        <x-pos.table.th>Sales</x-pos.table.th>
                        <x-pos.table.th>Jenis Bayar</x-pos.table.th>
                        <x-pos.table.th class="text-center">Jlh Item</x-pos.table.th>
                        <x-pos.table.th class="text-right">Subtotal</x-pos.table.th>
                        <x-pos.table.th class="text-right">Diskon</x-pos.table.th>
                        <x-pos.table.th class="text-right">PPN / Pajak</x-pos.table.th>
                        <x-pos.table.th class="text-right">Grand Total</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($paginatedReportData['paginated_items'] ?? [] as $nota)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-mono font-bold text-primary dark:text-blue-400">{{ $nota['invoice_number'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $nota['date'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white"><span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200">{{ $nota['sale_category'] }}</span></x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['customer_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['cashier_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['sales_rep_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['payment_method'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white">{{ $nota['item_count'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white">Rp {{ number_format($nota['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($nota['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($nota['tax_amount'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($nota['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="12" icon="ph-receipt" message="Belum ada transaksi nota penjualan pada periode ini." />
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'top_selling')
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th class="text-center">Rangking</x-pos.table.th>
                        <x-pos.table.th>Kode Barang (SKU/Barcode)</x-pos.table.th>
                        <x-pos.table.th>Nama Barang</x-pos.table.th>
                        <x-pos.table.th>Kategori Produk</x-pos.table.th>
                        <x-pos.table.th class="text-center">Total Qty Terjual</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Omzet Penjualan</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($paginatedReportData['paginated_items'] ?? [] as $rank => $prod)
                        <x-pos.table.tr>
                            <x-pos.table.td class="text-center font-black">
                                <span class="w-6 h-6 inline-flex items-center justify-center rounded-full font-bold {{ $rank === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : ($rank === 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : ($rank === 2 ? 'bg-amber-50 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                                    #{{ $rank + 1 }}
                                </span>
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $prod['sku'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $prod['product_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $prod['category_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-center font-extrabold text-primary dark:text-blue-400">{{ number_format($prod['total_qty'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($prod['total_revenue'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="6" icon="ph-trophy" message="Belum ada data produk terlaris pada periode ini." />
                    @endforelse
                </tbody>
            @endif
        </x-pos.table>
    </x-pos.table.container>

    <!-- Table Footer Component -->
    <x-pos.table.footer :paginator="$paginatedReportData['paginated_items'] ?? null" />
</div>
