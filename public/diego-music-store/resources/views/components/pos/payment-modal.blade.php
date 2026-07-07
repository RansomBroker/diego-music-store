@props([
    'showPaymentModal',
    'paymentMethod',
    'grandTotal',
    'amountPaid'
])

<x-pos.modal 
    :show="$showPaymentModal" 
    title="Detail Pembayaran" 
    closeAction="closePayment"
    maxWidth="lg"
>
    <!-- Grand Total Display -->
    <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl mb-6 text-center border border-slate-100 dark:border-slate-800">
        <span class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider block mb-1">Total Tagihan</span>
        <span class="text-3xl font-black text-primary dark:text-blue-400">{{ \App\Helpers\FormatHelper::rupiah($grandTotal) }}</span>
    </div>

    <!-- Payment Method Selection -->
    <div class="mb-6">
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5">Metode Pembayaran</label>
        <div class="grid grid-cols-3 gap-3">
            <button wire:click="setPaymentMethod('cash')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'cash' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                <i class="ph-fill ph-money text-2xl mb-1.5"></i>
                <span class="text-xs font-bold">Tunai</span>
            </button>
            <button wire:click="setPaymentMethod('debit')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'debit' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                <i class="ph-fill ph-credit-card text-2xl mb-1.5"></i>
                <span class="text-xs font-bold">Debit BCA</span>
            </button>
            <button wire:click="setPaymentMethod('credit')" class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition-all {{ $paymentMethod === 'credit' ? 'border-primary bg-primaryLight/35 text-primary dark:border-blue-500 dark:bg-blue-950/20 dark:text-blue-400' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
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
                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-555">Rp</span>
                <input type="number" wire:model.live="amountPaid" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl outline-none font-bold text-lg focus:ring-2 focus:ring-primaryLight dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="0">
            </div>
        </div>

        @php
            $change = intval($amountPaid) - intval($grandTotal);
        @endphp
        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Kembalian</span>
            <span class="text-xl font-bold {{ $change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                {{ \App\Helpers\FormatHelper::formatChange(intval($amountPaid), intval($grandTotal)) }}
            </span>
        </div>
    @endif

    <!-- Action Button -->
    <x-pos.button 
        wire:click="checkout" 
        variant="primary" 
        size="lg" 
        icon="ph-printer-active" 
        loading="checkout"
    >
        Konfirmasi & Cetak Struk
    </x-pos.button>
</x-pos.modal>
