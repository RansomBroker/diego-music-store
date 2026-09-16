<x-filament-panels::page>
    @php
        $data = $this->report_data;
    @endphp

    {{-- Filter Form (Native Filament Section) --}}
    <div>
        {{ $this->form }}
    </div>



    {{-- MAIN CONTENT: TAMPILAN BUKU BESAR TIAP SUPPLIER --}}
    @if ($data['mode'] === 'ledger' || $data['mode'] === 'by_supplier')
        <div class="space-y-6">
            @forelse ($data['supplier_ledgers'] as $supplier)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 text-xs font-mono font-extrabold bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded border border-gray-300 dark:border-gray-600">
                                SUPPLIER
                            </span>
                            <span class="font-extrabold tracking-wide text-base text-gray-900 dark:text-white">
                                {{ $supplier['supplier_name'] }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot name="headerEnd">
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($supplier['phone'] && $supplier['phone'] !== '-')
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Telp: <strong>{{ $supplier['phone'] }}</strong>
                                </span>
                            @endif
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                {{ $supplier['total_transactions'] }} Transaksi
                            </span>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">
                                {{ $supplier['total_qty_returned'] }} Unit Diretur
                            </span>
                            <span class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                Rp {{ number_format($supplier['total_amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </x-slot>

                    {{-- Tabel Buku Besar Retur untuk Supplier ini --}}
                    <div class="overflow-x-auto -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-100/80 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                                    <th class="py-2.5 px-4 w-24">Tanggal</th>
                                    <th class="py-2.5 px-4 w-32">No. Retur</th>
                                    <th class="py-2.5 px-4 w-32">Ref PT</th>
                                    <th class="py-2.5 px-4 w-36">Metode Penyelesaian</th>
                                    <th class="py-2.5 px-4 w-28">SKU</th>
                                    <th class="py-2.5 px-4">Nama Produk / Barang</th>
                                    <th class="py-2.5 px-4 text-right w-20">Qty</th>
                                    <th class="py-2.5 px-4 text-right w-28">Harga Satuan</th>
                                    <th class="py-2.5 px-4 text-right w-32">Subtotal</th>
                                    <th class="py-2.5 px-4 w-20 text-center">Status</th>
                                    <th class="py-2.5 px-4 w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($supplier['entries'] as $entry)
                                    @php
                                        $badgeClasses = match($entry['return_type']) {
                                            'replacement'       => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            'invoice_deduction' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                            'refund'            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'supplier_credit'   => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                                            default             => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                                        };
                                        $itemCount = count($entry['items']);
                                    @endphp

                                    @foreach ($entry['items'] as $idx => $item)
                                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                            @if ($idx === 0)
                                                <td class="py-2 px-4 whitespace-nowrap text-gray-500 font-medium align-top" rowspan="{{ $itemCount }}">
                                                    {{ $entry['return_date'] }}
                                                </td>
                                                <td class="py-2 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap align-top" rowspan="{{ $itemCount }}">
                                                    {{ $entry['return_no'] }}
                                                </td>
                                                <td class="py-2 px-4 text-gray-600 dark:text-gray-400 whitespace-nowrap align-top" rowspan="{{ $itemCount }}">
                                                    {{ $entry['transaction_no'] }}
                                                </td>
                                                <td class="py-2 px-4 align-top" rowspan="{{ $itemCount }}">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $badgeClasses }}">
                                                        {{ $entry['return_type_label'] }}
                                                    </span>
                                                    @if ($entry['refund_account_name'])
                                                        <span class="block text-[10px] text-gray-500 mt-0.5">{{ $entry['refund_account_name'] }}</span>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="py-2 px-4 font-mono text-gray-500">
                                                {{ $item['sku'] }}
                                            </td>
                                            <td class="py-2 px-4 font-semibold text-gray-900 dark:text-white">
                                                {{ $item['product_name'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right font-bold text-amber-600 dark:text-amber-400">
                                                {{ $item['qty'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-600 dark:text-gray-400">
                                                Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-2 px-4 text-right font-extrabold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($item['total_price'], 0, ',', '.') }}
                                            </td>

                                            @if ($idx === 0)
                                                <td class="py-2 px-4 text-center align-top" rowspan="{{ $itemCount }}">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $entry['status'] === 'posted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                        {{ $entry['status_label'] }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-4 text-center align-top" rowspan="{{ $itemCount }}">
                                                    <button 
                                                        type="button"
                                                        wire:click="openReturnDetailModal({{ $entry['id'] }})"
                                                        class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50"
                                                    >
                                                        Detail
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="11" class="py-4 px-4 text-center text-gray-400 italic">
                                            Tidak ada riwayat retur untuk supplier ini pada periode yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50/90 dark:bg-white/5 font-extrabold border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white">
                                    <td colspan="6" class="py-3 px-4 uppercase text-xs">
                                        TOTAL RETUR SUPPLIER: {{ $supplier['supplier_name'] }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-amber-600 dark:text-amber-400 text-xs">
                                        {{ number_format($supplier['total_qty_returned'], 0, ',', '.') }} Unit
                                    </td>
                                    <td></td>
                                    <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 text-xs">
                                        Rp {{ number_format($supplier['total_amount'], 0, ',', '.') }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Mini Sub-summary Methods per Supplier --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3 text-xs">
                        <span class="text-gray-500 dark:text-gray-400 font-semibold">Rincian Metode:</span>
                        @if ($supplier['by_type']['replacement']['count'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                Tukar Guling: {{ $supplier['by_type']['replacement']['qty'] }} Unit ({{ $supplier['by_type']['replacement']['count'] }} Dok)
                            </span>
                        @endif
                        @if ($supplier['by_type']['invoice_deduction']['count'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                Potong Faktur: Rp {{ number_format($supplier['by_type']['invoice_deduction']['amount'], 0, ',', '.') }}
                            </span>
                        @endif
                        @if ($supplier['by_type']['refund']['count'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                Refund: Rp {{ number_format($supplier['by_type']['refund']['amount'], 0, ',', '.') }}
                            </span>
                        @endif
                        @if ($supplier['by_type']['supplier_credit']['count'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300">
                                Saldo Deposit: Rp {{ number_format($supplier['by_type']['supplier_credit']['amount'], 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </x-filament::section>
            @empty
                <div class="p-12 text-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <p class="text-gray-400 italic">Tidak ada buku besar retur supplier untuk periode dan filter ini.</p>
                </div>
            @endforelse
        </div>

    @else
        {{-- MODE SUMMARY ATAU DETAIL GABUNGAN --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                        {{ $data['mode'] === 'detail' ? 'RINCIAN DETAIL BARANG PER DOKUMEN RETUR' : 'RINGKASAN TRANSAKSI DOKUMEN RETUR' }}
                    </span>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Metode: <strong>{{ $data['return_type_label'] }}</strong> &bull; Status: <strong>{{ strtoupper($data['status']) }}</strong>
                </span>
            </x-slot>

            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <th class="p-3">No. Retur</th>
                            <th class="p-3">Ref Transaksi PT</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Supplier</th>
                            <th class="p-3">Metode Penyelesaian</th>
                            <th class="p-3">Status</th>
                            @if ($data['mode'] === 'detail')
                                <th class="p-3">SKU</th>
                                <th class="p-3">Nama Produk</th>
                                <th class="p-3 text-right">Qty</th>
                                <th class="p-3 text-right">Harga Satuan</th>
                            @else
                                <th class="p-3 text-right">Total Qty</th>
                            @endif
                            <th class="p-3 text-right">Total Nilai</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($data['returns'] as $ret)
                            @php
                                $badgeClasses = match($ret['return_type']) {
                                    'replacement'       => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                    'invoice_deduction' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                    'refund'            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                    'supplier_credit'   => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                                    default             => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                                };
                            @endphp

                            @if ($data['mode'] === 'detail')
                                @foreach ($ret['items'] as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                        @if ($index === 0)
                                            <td class="p-3 font-bold text-gray-900 dark:text-white" rowspan="{{ count($ret['items']) }}">
                                                {{ $ret['return_no'] }}
                                            </td>
                                            <td class="p-3 text-gray-600 dark:text-gray-400" rowspan="{{ count($ret['items']) }}">
                                                {{ $ret['transaction_no'] }}
                                            </td>
                                            <td class="p-3 whitespace-nowrap" rowspan="{{ count($ret['items']) }}">
                                                {{ $ret['return_date'] }}
                                            </td>
                                            <td class="p-3 font-semibold" rowspan="{{ count($ret['items']) }}">
                                                {{ $ret['supplier_name'] }}
                                            </td>
                                            <td class="p-3" rowspan="{{ count($ret['items']) }}">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $badgeClasses }}">
                                                    {{ $ret['return_type_label'] }}
                                                </span>
                                                @if ($ret['refund_account_name'])
                                                    <span class="block text-[10px] text-gray-500 mt-0.5">{{ $ret['refund_account_name'] }}</span>
                                                @endif
                                            </td>
                                            <td class="p-3" rowspan="{{ count($ret['items']) }}">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $ret['status'] === 'posted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                    {{ $ret['status_label'] }}
                                                </span>
                                            </td>
                                        @endif
                                        <td class="p-3 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                        <td class="p-3 font-medium text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                                        <td class="p-3 text-right font-bold text-amber-600">{{ $item['qty'] }}</td>
                                        <td class="p-3 text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                        @if ($index === 0)
                                            <td class="p-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400" rowspan="{{ count($ret['items']) }}">
                                                Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-3 text-center" rowspan="{{ count($ret['items']) }}">
                                                <button 
                                                    type="button"
                                                    wire:click="openReturnDetailModal({{ $ret['id'] }})"
                                                    class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50"
                                                >
                                                    Detail
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">
                                        {{ $ret['return_no'] }}
                                    </td>
                                    <td class="p-3 text-gray-600 dark:text-gray-400">
                                        {{ $ret['transaction_no'] }}
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        {{ $ret['return_date'] }}
                                    </td>
                                    <td class="p-3 font-semibold">
                                        {{ $ret['supplier_name'] }}
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $badgeClasses }}">
                                            {{ $ret['return_type_label'] }}
                                        </span>
                                        @if ($ret['refund_account_name'])
                                            <span class="block text-[10px] text-gray-500 mt-0.5">{{ $ret['refund_account_name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $ret['status'] === 'posted' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $ret['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-bold text-amber-600">
                                        {{ $ret['total_qty'] }} unit
                                    </td>
                                    <td class="p-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <button 
                                            type="button"
                                            wire:click="openReturnDetailModal({{ $ret['id'] }})"
                                            class="px-2.5 py-1 text-[11px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50"
                                        >
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ $data['mode'] === 'detail' ? 12 : 10 }}" class="p-8 text-center text-gray-400 italic">
                                    Tidak ada data retur pembelian supplier untuk periode dan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    {{-- Detail Return Modal Component --}}
    <x-filament::modal id="return-detail-modal" width="3xl">
        <x-slot name="heading">
            Rincian Dokumen Retur Pembelian Supplier
        </x-slot>

        @if ($selectedReturnDetail)
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">No. Retur</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $selectedReturnDetail['return_no'] }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Ref Transaksi PT</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $selectedReturnDetail['transaction_no'] }}</span>
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
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Metode Penyelesaian</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $selectedReturnDetail['return_type_label'] }}</span>
                        @if ($selectedReturnDetail['refund_account_name'] && $selectedReturnDetail['refund_account_name'] !== '-')
                            <span class="text-[11px] text-gray-500 block">Akun: {{ $selectedReturnDetail['refund_account_name'] }}</span>
                        @endif
                    </div>
                    <div class="col-span-4">
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
                                    <td class="p-2.5 text-right font-bold text-emerald-600">Rp {{ number_format($it['total_price'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold border-t border-gray-200 dark:border-gray-700">
                            <tr>
                                <td colspan="4" class="p-2.5 text-right uppercase">Total Retur:</td>
                                <td class="p-2.5 text-right font-black text-emerald-600 text-sm">
                                    Rp {{ number_format($selectedReturnDetail['total_amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
