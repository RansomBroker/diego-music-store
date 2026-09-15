<!-- Modal Detail Transaksi -->
@if ($showDetailsModal && $selectedSale)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeDetails" aria-hidden="true"></div>

            <!-- Spacer to center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="relative inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-800">
                
                <!-- Modal Header -->
                <div class="px-6 py-4.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-light dark:bg-blue-950/50 text-primary dark:text-blue-400 flex items-center justify-center">
                            <i class="ph-bold ph-receipt text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white" id="modal-title">
                                Detail Transaksi {{ $selectedSale->invoice_number }}
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                {{ $selectedSale->invoice_date->format('d F Y') }} • {{ $selectedSale->created_at->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetails" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto no-scrollbar">
                    <!-- Transaction Metadata -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-950/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800/40 text-xs">
                        <div>
                            <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Kasir / Sales Rep</span>
                            <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->salesRep->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</span>
                            <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->customer->name ?? 'Pelanggan Umum' }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Metode Pembayaran</span>
                            <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->payment_method }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="block font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Kategori Penjualan</span>
                            <span class="font-black text-slate-850 dark:text-slate-200 text-sm">{{ $selectedSale->sale_category }}</span>
                        </div>
                    </div>

                    <!-- Product Items Table -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Item Produk</h4>
                        <div class="border border-slate-200 dark:border-slate-850 rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-850 text-slate-400 font-bold uppercase tracking-wider">
                                        <th class="px-4 py-3">Barang</th>
                                        <th class="px-4 py-3 text-center">Qty</th>
                                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                                        <th class="px-4 py-3 text-right">Diskon</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-150 dark:divide-slate-850 text-slate-700 dark:text-slate-300 font-semibold">
                                    @foreach($selectedSale->items as $item)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="font-black text-slate-850 dark:text-slate-200 block">
                                                    {{ $item->variant->product->name ?? 'Produk Tidak Dikenal' }}
                                                </span>
                                                @if($item->variant && $item->variant->name && $item->variant->name !== 'Default')
                                                    <span class="block text-[10px] text-slate-400 dark:text-slate-550 font-medium mt-0.5">Varian: {{ $item->variant->name }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-red-500">
                                                @if($item->discount_amount > 0)
                                                    -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white">
                                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <x-pos.table.footer :total="$selectedSale->items->count()" />
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="space-y-2 border-t border-slate-150 dark:border-slate-800 pt-4 text-xs font-semibold">
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($selectedSale->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($selectedSale->discount_amount > 0)
                            <div class="flex items-center justify-between text-rose-500">
                                <span>Diskon</span>
                                <span>-Rp {{ number_format($selectedSale->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($selectedSale->tax_amount > 0)
                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                <span>PPN ({{ $selectedSale->tax_rate }}%)</span>
                                <span>Rp {{ number_format($selectedSale->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-base font-black text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                            <span>Grand Total</span>
                            <span class="text-primary dark:text-primaryLight">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="bg-slate-50 dark:bg-slate-950/40 p-4 rounded-xl space-y-2 text-xs border border-slate-200/60 dark:border-slate-850">
                        <div class="flex items-center justify-between text-slate-500 font-bold">
                            <span>Metode Pembayaran</span>
                            <span class="text-slate-800 dark:text-slate-200 uppercase">{{ $selectedSale->payment_method ?? 'TUNAI' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 font-bold">
                            <span>Nominal Dibayar</span>
                            <span class="text-slate-800 dark:text-slate-200">Rp {{ number_format($selectedSale->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 font-bold">
                            <span>Kembalian</span>
                            <span class="text-emerald-600 font-black">Rp {{ number_format($selectedSale->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-850 flex items-center justify-end gap-3">
                    @if ($selectedSale->status !== 'cancelled')
                        <x-pos.utility.button
                            variant="warning"
                            href="/pos?edit={{ $selectedSale->id }}"
                            icon="ph-pencil"
                        >
                            Edit Transaksi
                        </x-pos.utility.button>
                    @endif
                    @if ($selectedSale->status !== 'cancelled' && $selectedSale->items->sum(fn($item) => $item->quantity - $item->returnItems()->sum('quantity')) > 0)
                        <x-pos.utility.button
                            type="button"
                            variant="danger"
                            wire:click="startReturn({{ $selectedSale->id }})"
                            icon="ph-arrow-counter-clockwise"
                        >
                            Retur Barang
                        </x-pos.utility.button>
                    @endif
                    <x-pos.utility.button
                        type="button"
                        variant="success"
                        wire:click="printReceipt({{ $selectedSale->id }})"
                        icon="ph-printer"
                    >
                        Cetak Struk
                    </x-pos.utility.button>
                    <x-pos.utility.button
                        type="button"
                        variant="secondary"
                        wire:click="closeDetails"
                    >
                        Tutup
                    </x-pos.utility.button>
                </div>

            </div>
        </div>
    </div>
@endif
