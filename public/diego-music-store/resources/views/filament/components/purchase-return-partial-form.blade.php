@php
    $purchaseId = $get('purchase_transaction_id');
    $pt = $purchaseId ? \App\Models\PurchaseTransaction::with(['details.productVariant.product', 'details.returnItems', 'supplier', 'branch'])->find($purchaseId) : null;
@endphp

@if ($pt)
    <div class="space-y-4 my-2 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-800/50">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Detail Transaksi Pembelian Supplier</span>
                <h4 class="text-sm font-black text-slate-900 dark:text-slate-100">No. Transaksi #{{ $pt->transaction_no }}</h4>
                <p class="text-xs text-slate-500">Supplier: {{ $pt->supplier?->name ?: '-' }} • Tgl: {{ $pt->transaction_date->format('d M Y') }} • Type: {{ $pt->purchase_type }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Grand Total Pembelian</span>
                <div class="text-sm font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($pt->grand_total, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="space-y-3">
            <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Pilih Barang & Jumlah Unit yang Dikembalikan ke Supplier (Sebagian/Seluruhnya):
            </label>

            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach ($pt->details as $detail)
                    @php
                        $availableQty = $detail->available_qty_for_return;
                    @endphp
                    <div class="flex items-center justify-between gap-4 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                        <div class="flex-1 min-w-0">
                            <h5 class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                                {{ $detail->productVariant?->product?->name }} ({{ $detail->productVariant?->name ?: $detail->productVariant?->sku }})
                            </h5>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Diterima: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $detail->qty_received }}</span> unit 
                                • Sudah Diretur: <span class="font-bold text-rose-600">{{ $detail->returned_qty }}</span> unit
                                • Maks Retur: <span class="font-bold text-emerald-600">{{ $availableQty }}</span> unit
                            </div>
                        </div>

                        <div class="w-32 shrink-0">
                            @if ($availableQty > 0)
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        wire:model="mountedActionsData.0.return_items.{{ $detail->id }}"
                                        min="0"
                                        max="{{ $availableQty }}"
                                        placeholder="0"
                                        class="w-full px-3 py-1.5 text-xs font-bold bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 text-center"
                                    />
                                    <span class="text-xs text-slate-500 font-semibold">unit</span>
                                </div>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-400 text-[10px] font-bold rounded-lg block text-center">
                                    Habis Diretur
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
