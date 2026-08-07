<x-filament-panels::page>
    @php
        $data = $this->report_data;
    @endphp

    {{-- Filter Form (Native Filament Section) --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Transaksi Retur</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_transactions'], 0, ',', '.') }} Transaksi
            </div>
            <span class="text-xs text-gray-400">Periode: {{ $data['from_date'] }} - {{ $data['to_date'] }}</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Qty Barang Diretur</span>
            <div class="text-xl font-extrabold font-mono text-amber-600 dark:text-amber-400 mt-1">
                {{ number_format($data['total_qty_returned'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Barang fisik yang dikembalikan</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Total Nilai Refund / Pengurangan Hutang</span>
            <div class="text-xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                Rp {{ number_format($data['total_return_amount'], 0, ',', '.') }}
            </div>
            <span class="text-xs text-gray-500 font-semibold">Cabang: {{ $data['branch_name'] }}</span>
        </div>
    </div>

    {{-- Data Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'detail' ? 'RINCIAN DETAIL BARANG PER RETUR PEMBELIAN' : 'RINGKASAN RETUR PEMBELIAN SUPPLIER' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Mode: <strong>{{ strtoupper($data['mode']) }}</strong> &bull; Status: <strong>{{ strtoupper($data['status']) }}</strong>
            </span>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                        <th class="p-3">No. Retur</th>
                        <th class="p-3">Ref Transaksi PT</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Cabang</th>
                        <th class="p-3">Supplier</th>
                        <th class="p-3">Status</th>
                        @if ($data['mode'] === 'detail')
                            <th class="p-3">SKU</th>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3 text-right">Qty</th>
                            <th class="p-3 text-right">Harga Satuan</th>
                        @else
                            <th class="p-3 text-right">Total Qty</th>
                        @endif
                        <th class="p-3 text-right">Total Nilai Retur</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($data['returns'] as $ret)
                        @if ($data['mode'] === 'detail')
                            @foreach ($ret['items'] as $index => $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    @if ($index === 0)
                                        <td class="p-3 font-bold font-mono text-gray-900 dark:text-white" rowspan="{{ count($ret['items']) }}">
                                            {{ $ret['return_no'] }}
                                        </td>
                                        <td class="p-3 font-mono text-gray-600 dark:text-gray-400" rowspan="{{ count($ret['items']) }}">
                                            {{ $ret['transaction_no'] }}
                                        </td>
                                        <td class="p-3 whitespace-nowrap" rowspan="{{ count($ret['items']) }}">
                                            {{ $ret['return_date'] }}
                                        </td>
                                        <td class="p-3" rowspan="{{ count($ret['items']) }}">
                                            {{ $ret['branch_name'] }}
                                        </td>
                                        <td class="p-3 font-semibold" rowspan="{{ count($ret['items']) }}">
                                            {{ $ret['supplier_name'] }}
                                        </td>
                                        <td class="p-3" rowspan="{{ count($ret['items']) }}">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $ret['status'] === 'posted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $ret['status_label'] }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="p-3 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                    <td class="p-3 font-medium">{{ $item['product_name'] }}</td>
                                    <td class="p-3 text-right font-bold text-amber-600">{{ $item['qty'] }}</td>
                                    <td class="p-3 text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                    @if ($index === 0)
                                        <td class="p-3 text-right font-extrabold font-mono text-emerald-600 dark:text-emerald-400" rowspan="{{ count($ret['items']) }}">
                                            Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-center" rowspan="{{ count($ret['items']) }}">
                                            <button 
                                                type="button"
                                                wire:click="openReturnDetailModal({{ $ret['id'] }})"
                                                class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100"
                                            >
                                                Detail
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="p-3 font-bold font-mono text-gray-900 dark:text-white">
                                    {{ $ret['return_no'] }}
                                </td>
                                <td class="p-3 font-mono text-gray-600 dark:text-gray-400">
                                    {{ $ret['transaction_no'] }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    {{ $ret['return_date'] }}
                                </td>
                                <td class="p-3">
                                    {{ $ret['branch_name'] }}
                                </td>
                                <td class="p-3 font-semibold">
                                    {{ $ret['supplier_name'] }}
                                </td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $ret['status'] === 'posted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $ret['status_label'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-bold text-amber-600">
                                    {{ $ret['total_qty'] }} unit
                                </td>
                                <td class="p-3 text-right font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    <button 
                                        type="button"
                                        wire:click="openReturnDetailModal({{ $ret['id'] }})"
                                        class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $data['mode'] === 'detail' ? 11 : 9 }}" class="p-8 text-center text-gray-400 italic">
                                Tidak ada data retur pembelian supplier untuk periode dan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Detail Return Modal Component --}}
    <x-filament::modal id="return-detail-modal" width="3xl">
        <x-slot name="heading">
            Rincian Dokumen Retur Pembelian Supplier
        </x-slot>

        @if ($selectedReturnDetail)
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">No. Retur</span>
                        <span class="font-bold font-mono text-gray-900 dark:text-white">{{ $selectedReturnDetail['return_no'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Ref Transaksi</span>
                        <span class="font-mono text-gray-800 dark:text-gray-200">{{ $selectedReturnDetail['transaction_no'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Tanggal Retur</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $selectedReturnDetail['date'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Status</span>
                        <span class="font-bold text-emerald-600">{{ $selectedReturnDetail['status'] }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Supplier</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $selectedReturnDetail['supplier_name'] }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Alasan Retur</span>
                        <span class="italic text-gray-600 dark:text-gray-300">{{ $selectedReturnDetail['reason'] }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-100 dark:bg-gray-800 font-bold uppercase text-gray-600 dark:text-gray-400">
                            <tr>
                                <th class="p-2.5">SKU</th>
                                <th class="p-2.5">Nama Produk</th>
                                <th class="p-2.5 text-right">Qty Retur</th>
                                <th class="p-2.5 text-right">Harga Satuan</th>
                                <th class="p-2.5 text-right">Total Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($selectedReturnDetail['items'] as $it)
                                <tr>
                                    <td class="p-2.5 font-mono text-gray-500">{{ $it['sku'] }}</td>
                                    <td class="p-2.5 font-medium text-gray-900 dark:text-white">{{ $it['product_name'] }}</td>
                                    <td class="p-2.5 text-right font-bold text-amber-600">{{ $it['qty'] }}</td>
                                    <td class="p-2.5 text-right">Rp {{ number_format($it['unit_price'], 0, ',', '.') }}</td>
                                    <td class="p-2.5 text-right font-bold font-mono text-emerald-600">Rp {{ number_format($it['total_price'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold border-t border-gray-200 dark:border-gray-700">
                            <tr>
                                <td colspan="4" class="p-2.5 text-right uppercase">Total Retur / Refund:</td>
                                <td class="p-2.5 text-right font-mono font-black text-emerald-600 text-sm">Rp {{ number_format($selectedReturnDetail['total_amount'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
