<!-- Return Transaction Modal -->
@if ($showReturnModal && $returnSale)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="cancelReturn"></div>

            <!-- Centering helper -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100 dark:border-slate-800">
                
                <!-- Modal Header -->
                <div class="px-6 py-4.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-955/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                            <i class="ph-bold ph-arrow-counter-clockwise text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white" id="modal-title">
                                Retur Penjualan {{ $returnSale->invoice_number }}
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                {{ $returnSale->invoice_date->format('d F Y') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="cancelReturn" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto no-scrollbar">
                    <!-- Alert Box -->
                    <div class="p-3 bg-amber-50 dark:bg-amber-955/10 border border-amber-250 dark:border-amber-900/60 rounded-xl flex gap-3 text-amber-800 dark:text-amber-400">
                        <i class="ph-fill ph-info text-lg mt-0.5"></i>
                        <div class="text-xs">
                            <span class="font-extrabold block">Penting sebelum memproses retur:</span>
                            <ul class="list-disc pl-4 mt-1 space-y-0.5 font-medium text-slate-600 dark:text-slate-400">
                                <li>Kuantitas retur tidak boleh melebihi sisa barang yang dapat diretur.</li>
                                <li>Refund dihitung berdasarkan harga bersih setelah diskon dibagi kuantitas barang saat pembelian.</li>
                                <li>Proses ini akan mengembalikan stok fisik dan memotong kas pada sesi kasir aktif saat ini.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Return Items List -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Pilih Item untuk Diretur</h4>
                        <div class="border border-slate-200 dark:border-slate-850 rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-850 text-slate-400 font-bold uppercase tracking-wider">
                                        <th class="px-4 py-3">Barang</th>
                                        <th class="px-4 py-3 text-center w-28">Beli / Diretur</th>
                                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                                        <th class="px-4 py-3 text-center w-36">Qty Retur</th>
                                        <th class="px-4 py-3 text-right w-36">Refund Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-150 dark:divide-slate-850 text-slate-700 dark:text-slate-300 font-semibold">
                                    @foreach($returnItems as $itemId => $item)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20">
                                            <td class="px-4 py-3">
                                                <span class="font-black text-slate-850 dark:text-slate-200 block">
                                                    {{ $item['name'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center text-slate-500">
                                                {{ $item['original_qty'] }} / {{ $item['returned_qty'] }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-slate-500">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                <div class="flex items-center justify-center gap-1 bg-slate-100 dark:bg-slate-950 border border-slate-250 dark:border-slate-850 rounded-lg p-0.5 w-fit mx-auto">
                                                    <button 
                                                        type="button" 
                                                        wire:click="$set('returnItems.{{ $itemId }}.qty', {{ max(0, intval($item['qty'] ?? 0) - 1) }})" 
                                                        class="w-6 h-6 rounded bg-white dark:bg-slate-800 shadow border border-slate-200 dark:border-slate-750 flex items-center justify-center text-slate-755 dark:text-slate-300 hover:text-rose-600 transition-colors"
                                                    >
                                                        <i class="ph-bold ph-minus text-[10px]"></i>
                                                    </button>
                                                    <input 
                                                        type="number" 
                                                        wire:model.live="returnItems.{{ $itemId }}.qty" 
                                                        min="0" 
                                                        max="{{ $item['max'] }}" 
                                                        class="w-10 bg-transparent text-center border-none p-0 text-xs font-black text-slate-900 dark:text-white focus:ring-0 appearance-none h-5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                    >
                                                    <button 
                                                        type="button" 
                                                        wire:click="$set('returnItems.{{ $itemId }}.qty', {{ min(intval($item['max']), intval($item['qty'] ?? 0) + 1) }})" 
                                                        class="w-6 h-6 rounded bg-white dark:bg-slate-800 shadow border border-slate-200 dark:border-slate-750 flex items-center justify-center text-slate-755 dark:text-slate-300 hover:text-rose-600 transition-colors"
                                                    >
                                                        <i class="ph-bold ph-plus text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400">
                                                Rp {{ number_format(intval($item['qty'] ?? 0) * $item['refund_per_unit'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Return Reason & Refund Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Alasan Retur</label>
                            <textarea 
                                wire:model="returnReason" 
                                placeholder="Masukkan alasan pengembalian barang..." 
                                rows="3" 
                                class="w-full px-3 py-2 bg-slate-550 dark:bg-slate-950 border border-slate-250 dark:border-slate-850 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-550 focus:border-rose-500 focus:ring-rose-500 outline-none transition-colors"
                            ></textarea>
                        </div>
                        <div class="bg-slate-50/50 dark:bg-slate-950/40 p-4.5 rounded-xl border border-slate-100 dark:border-slate-800/40 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                                <span>Total Barang Diretur</span>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ collect($returnItems)->sum('qty') }} unit
                                </span>
                            </div>
                            <div class="w-full border-t border-dashed border-slate-200 dark:border-slate-800 my-3"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-700 dark:text-slate-350">Estimasi Total Refund</span>
                                <span class="text-xl font-black text-rose-600 dark:text-rose-450">
                                    Rp {{ number_format(collect($returnItems)->sum(fn($i) => intval($i['qty'] ?? 0) * intval($i['refund_per_unit'])), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-850 flex items-center justify-end gap-3 font-semibold">
                    <button
                        type="button"
                        wire:click="processReturn"
                        class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-black text-white hover:text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-md shadow-rose-600/15 hover:shadow-rose-600/25 transition-all cursor-pointer"
                    >
                        <i class="ph-bold ph-check text-sm"></i>
                        <span>Proses Retur</span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancelReturn"
                        class="px-4 py-2.5 text-xs font-black text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-250 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition-all"
                    >
                        Batal
                    </button>
                </div>

            </div>
        </div>
    </div>
@endif
