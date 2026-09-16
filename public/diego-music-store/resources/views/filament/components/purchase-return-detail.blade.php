@php
    $journal = \App\Models\JournalEntry::with('items.account')
        ->where('reference_type', 'PurchaseReturn')
        ->where('reference_id', $record->id)
        ->first();
@endphp

<div class="space-y-6 p-2 sm:p-4 text-slate-800 dark:text-slate-100">
    <!-- Header Card -->
    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">No. Retur</span>
                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $record->return_no }}</span>
                <span class="text-xs text-slate-500 block mt-0.5">{{ $record->return_date ? $record->return_date->format('d M Y') : '-' }}</span>
            </div>

            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Retur</span>
                @if ($record->status === 'posted')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Posted (Selesai)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Draft (Belum Diposting)
                    </span>
                @endif
            </div>

            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Metode Penyelesaian</span>
                @php
                    $typeLabels = [
                        'invoice_deduction' => ['Potong Faktur Tempo', 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border-amber-300 dark:border-amber-800'],
                        'refund'            => ['Refund Kas / Rekening Bank', 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border-blue-300 dark:border-blue-800'],
                        'replacement'       => ['Tukar Guling Barang', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-300 dark:border-emerald-800'],
                        'supplier_credit'   => ['Saldo Deposit Supplier', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-400 border-indigo-300 dark:border-indigo-800'],
                    ];
                    $currentType = $typeLabels[$record->return_type] ?? [ucfirst($record->return_type ?? '-'), 'bg-slate-100 text-slate-700 border-slate-300'];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold border {{ $currentType[1] }}">
                    {{ $currentType[0] }}
                </span>
                @if ($record->return_type === 'refund' && $record->refundAccount)
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Ke: <span class="font-bold">{{ $record->refundAccount->name }}</span></p>
                @elseif ($record->return_type === 'replacement')
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Status: <span class="font-bold">{{ $record->replacement_status === 'received' ? 'Langsung Diterima' : 'Menunggu Pengiriman' }}</span></p>
                @endif
            </div>

            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Nilai Retur</span>
                <span class="text-base font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Secondary Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 text-xs">
            <div>
                <span class="text-slate-400 font-semibold block">Faktur Pembelian:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $record->purchaseTransaction?->transaction_no ?: '-' }}</span>
                <span class="text-slate-500 block text-[11px]">Tipe: {{ $record->purchaseTransaction?->purchase_type ?: 'Tunai' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block">Supplier & Cabang:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $record->supplier?->name ?: '-' }}</span>
                <span class="text-slate-500 block text-[11px]">Cabang: {{ $record->branch?->name ?: '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block">Diproses Oleh:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $record->creator?->name ?: 'Sistem' }}</span>
                <span class="text-slate-500 block text-[11px]">Dibuat: {{ $record->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if ($record->reason)
                <div class="sm:col-span-3 mt-1 bg-white dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider block">Alasan Retur ke Supplier:</span>
                    <p class="text-xs text-slate-700 dark:text-slate-300 mt-0.5">{{ $record->reason }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
        <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Rincian Barang yang Diretur ke Supplier
            </h4>
            <span class="text-xs text-slate-500 font-bold">{{ count($record->items) }} Item Barang</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Nama Barang & Varian</th>
                        <th class="py-3 px-4 text-center">Jumlah Unit</th>
                        <th class="py-3 px-4 text-right">Harga Beli Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($record->items as $idx => $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-black text-slate-900 dark:text-white block">
                                    {{ $item->productVariant?->product?->name }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    Varian: {{ $item->productVariant?->name ?: '-' }} • SKU: {{ $item->productVariant?->sku ?: '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                    {{ $item->quantity }} unit
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-600 dark:text-slate-300">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-white">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">Tidak ada item dalam retur ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 font-black text-slate-900 dark:text-white border-t-2 border-slate-200 dark:border-slate-700">
                        <td colspan="4" class="py-3 px-4 text-right uppercase tracking-wider text-xs">Total Retur / Pengembalian:</td>
                        <td class="py-3 px-4 text-right text-sm text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($record->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Journal Entry Details (if posted and financial journal exists) -->
    @if ($journal)
        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
            <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        Jurnal Akuntansi Otomatis (No: {{ $journal->entry_no }})
                    </h4>
                </div>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                    Status: Posted
                </span>
            </div>

            <div class="p-3 text-xs text-slate-500 dark:text-slate-400 bg-slate-50/40 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-800">
                <span>{{ $journal->description }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5 px-4">Kode & Nama Akun</th>
                            <th class="py-2.5 px-4">Keterangan Item</th>
                            <th class="py-2.5 px-4 text-right">Debit</th>
                            <th class="py-2.5 px-4 text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($journal->items as $jItem)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-2.5 px-4 font-bold {{ $jItem->credit > 0 ? 'pl-8 text-slate-700 dark:text-slate-300' : 'text-slate-900 dark:text-white' }}">
                                    {{ $jItem->account?->code }} - {{ $jItem->account?->name }}
                                </td>
                                <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400">
                                    {{ $jItem->notes ?: '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-black {{ $jItem->debit > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600' }}">
                                    {{ $jItem->debit > 0 ? 'Rp ' . number_format($jItem->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-black {{ $jItem->credit > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600' }}">
                                    {{ $jItem->credit > 0 ? 'Rp ' . number_format($jItem->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
