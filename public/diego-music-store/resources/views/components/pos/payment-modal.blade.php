@props([
    'showPaymentModal',
    'paymentMethod',
    'grandTotal',
    'amountPaid'
])

@if ($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl transition-all border border-slate-100 dark:border-slate-700 mx-4">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Detail Pembayaran</h3>
                <button wire:click="closePayment" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <!-- Grand Total Display -->
                <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl mb-6 text-center border border-slate-100 dark:border-slate-800">
                    <span class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider block mb-1">Total Tagihan</span>
                    <span class="text-3xl font-black text-primary dark:text-blue-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="setPaymentMethod('cash')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'cash' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750' }}">
                            <i class="ph-fill ph-money text-2xl mb-1.5"></i>
                            <span class="text-xs font-bold">Tunai</span>
                        </button>
                        <button wire:click="setPaymentMethod('debit')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'debit' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750' }}">
                            <i class="ph-fill ph-credit-card text-2xl mb-1.5"></i>
                            <span class="text-xs font-bold">Debit BCA</span>
                        </button>
                        <button wire:click="setPaymentMethod('credit')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'credit' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-750' }}">
                            <i class="ph-fill ph-hand-coins text-2xl mb-1.5"></i>
                            <span class="text-xs font-bold">Piutang</span>
                        </button>
                    </div>
                </div>

                <!-- Input Tunai -->
                @if ($paymentMethod === 'cash')
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Uang Diterima</label>
                            <div class="flex gap-1.5">
                                <button type="button" onclick="@this.set('amountPaid', {{ $grandTotal }})" class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold px-2 py-0.5 rounded hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Uang Pas</button>
                            </div>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-500">Rp</span>
                            <input type="number" wire:model.live="amountPaid" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none font-bold text-lg focus:ring-2 focus:ring-primaryLight dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="0">
                        </div>
                    </div>

                    @php
                        $change = intval($amountPaid) - intval($grandTotal);
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Kembalian</span>
                        <span class="text-xl font-bold {{ $change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                            {{ $change >= 0 ? 'Rp ' . number_format($change, 0, ',', '.') : 'Kurang Rp ' . number_format(abs($change), 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                <!-- Action Button -->
                <button wire:click="checkout" class="w-full bg-primary hover:bg-primaryHover text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 group">
                    <i class="ph-bold ph-printer-active text-xl group-hover:scale-110 transition-transform"></i>
                    Konfirmasi & Cetak Struk
                </button>
            </div>
        </div>
    </div>
@endif
