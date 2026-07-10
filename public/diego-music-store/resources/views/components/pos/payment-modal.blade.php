@props([
    'showPaymentModal',
    'paymentMethod',
    'grandTotal',
    'amountPaid',
    'subtotal' => 0,
    'discountAmount' => 0,
    'taxAmount' => 0,
    'pointDiscountAmount' => 0,
    'usePoints' => false,
    'selectedPaymentMethods' => ['cash'],
    'amountCash' => 0,
    'amountDebit' => 0,
    'amountCredit' => 0,
    'debitRef' => '',
    'discountType' => 'fixed',
    'discountValue' => 0
])

<x-pos.modal 
    :show="$showPaymentModal" 
    title="Detail Pembayaran" 
    closeAction="closePayment"
    maxWidth="lg"
>
    <!-- Grand Total Display & Details -->
    <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl mb-6 border border-slate-100 dark:border-slate-800">
        <div class="text-center mb-4">
            <span class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider block mb-1">Total Tagihan</span>
            <span class="text-3xl font-black text-primary dark:text-blue-400">{{ \App\Helpers\FormatHelper::rupiah($grandTotal) }}</span>
        </div>
        
        <div class="border-t border-slate-200/50 dark:border-slate-700/60 pt-3 space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 font-medium">
                <span>Subtotal</span>
                <span>{{ \App\Helpers\FormatHelper::rupiah($subtotal) }}</span>
            </div>
            
            @if ($discountAmount > 0)
                <div class="flex items-center justify-between text-green-600 dark:text-green-400 font-semibold">
                    <span class="flex items-center gap-1">
                        <i class="ph-bold ph-tag"></i> 
                        Diskon Transaksi @if ($discountType === 'percent')({{ $discountValue }}%)@endif
                    </span>
                    <span>- {{ \App\Helpers\FormatHelper::rupiah($discountAmount) }}</span>
                </div>
            @endif

            @if ($usePoints && $pointDiscountAmount > 0)
                <div class="flex items-center justify-between text-green-600 dark:text-green-400 font-semibold">
                    <span class="flex items-center gap-1"><i class="ph-bold ph-coins"></i> Potongan Poin</span>
                    <span>- {{ \App\Helpers\FormatHelper::rupiah($pointDiscountAmount) }}</span>
                </div>
            @endif

            @if ($taxAmount > 0)
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 font-medium">
                    <span>PPN (11%)</span>
                    <span>{{ \App\Helpers\FormatHelper::rupiah($taxAmount) }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Method Selection (Multi-select Dropdown) -->
    <div class="mb-6" x-data="{ open: false }" @click.outside="open = false">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2.5">Pilih Metode Pembayaran (Bisa Multi-Pilih)</label>
        
        <div class="relative">
            <!-- Dropdown Trigger Button -->
            <div 
                @click="open = !open" 
                class="w-full flex items-center justify-between gap-2 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-800 dark:text-slate-200 focus-within:ring-2 focus-within:ring-primary-light dark:focus-within:ring-blue-950 transition-all text-left cursor-pointer select-none"
            >
                <div class="flex flex-wrap gap-1.5 items-center">
                    @if (empty($selectedPaymentMethods))
                        <span class="text-slate-400 font-medium text-xs">Pilih metode...</span>
                    @else
                        @foreach ($selectedPaymentMethods as $method)
                            <span @click.stop class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-primary/10 text-primary dark:bg-blue-950/40 dark:text-blue-400 rounded-full font-bold text-[10px] uppercase tracking-wider cursor-default">
                                <span>{{ $method === 'cash' ? 'Tunai' : ($method === 'debit' ? 'Debit BCA' : 'Piutang') }}</span>
                                <button type="button" wire:click="togglePaymentMethod('{{ $method }}')" class="hover:text-red-500 transition-colors cursor-pointer">
                                    <i class="ph-bold ph-x text-[8px]"></i>
                                </button>
                            </span>
                        @endforeach
                    @endif
                </div>
                <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </div>

            <!-- Dropdown Options List (Absolute floating list) -->
            <div 
                x-show="open" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg dark:shadow-slate-950/80 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800"
                style="display: none;"
            >
                <!-- Option: Tunai -->
                <button 
                    type="button" 
                    wire:click="togglePaymentMethod('cash')" 
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450 rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-money text-base"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-750 dark:text-slate-300">Tunai</span>
                    </div>
                    @if (in_array('cash', $selectedPaymentMethods))
                        <i class="ph-bold ph-check text-primary dark:text-blue-400 text-sm"></i>
                    @endif
                </button>

                <!-- Option: Debit BCA -->
                <button 
                    type="button" 
                    wire:click="togglePaymentMethod('debit')" 
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-credit-card text-base"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-750 dark:text-slate-300">Debit BCA</span>
                    </div>
                    @if (in_array('debit', $selectedPaymentMethods))
                        <i class="ph-bold ph-check text-primary dark:text-blue-400 text-sm"></i>
                    @endif
                </button>

                <!-- Option: Piutang -->
                <button 
                    type="button" 
                    wire:click="togglePaymentMethod('credit')" 
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-450 rounded-lg flex items-center justify-center">
                            <i class="ph-bold ph-hand-coins text-base"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-750 dark:text-slate-300">Piutang</span>
                    </div>
                    @if (in_array('credit', $selectedPaymentMethods))
                        <i class="ph-bold ph-check text-primary dark:text-blue-400 text-sm"></i>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Inputs -->
    <div class="space-y-4 mb-6">
        <!-- Tunai Input -->
        @if (in_array('cash', $selectedPaymentMethods))
            <div class="p-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-money text-base text-primary dark:text-blue-400"></i>
                        Nominal Tunai
                    </label>
                    @if (count($selectedPaymentMethods) === 1)
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Uang Pas</span>
                    @else
                        <button type="button" onclick="@this.set('amountCash', {{ max(0, (int)$grandTotal - (int)$amountDebit - (int)$amountCredit) }})" class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors uppercase tracking-wider">Sisa Tagihan</button>
                    @endif
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-500 text-sm">Rp</span>
                    <input type="number" wire:model.live="amountCash" wire:keyup="distributePaymentAmounts" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold text-base focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="0">
                </div>
            </div>
        @endif

        <!-- Debit BCA Input -->
        @if (in_array('debit', $selectedPaymentMethods))
            <div class="p-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-credit-card text-base text-primary dark:text-blue-400"></i>
                        Nominal Debit BCA
                    </label>
                    @if (count($selectedPaymentMethods) === 1)
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Bayar Penuh</span>
                    @else
                        <button type="button" onclick="@this.set('amountDebit', {{ max(0, (int)$grandTotal - (int)$amountCash - (int)$amountCredit) }})" class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors uppercase tracking-wider">Sisa Tagihan</button>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-500 text-sm">Rp</span>
                        <input type="number" wire:model.live="amountDebit" wire:keyup="distributePaymentAmounts" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold text-base focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="0">
                    </div>
                    <div class="relative">
                        <input type="text" wire:model="debitRef" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold text-sm focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="No. Bukti / Ref (Opsional)">
                    </div>
                </div>
            </div>
        @endif

        <!-- Piutang Input -->
        @if (in_array('credit', $selectedPaymentMethods))
            <div class="p-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-hand-coins text-base text-primary dark:text-blue-400"></i>
                        Nominal Piutang
                    </label>
                    @if (count($selectedPaymentMethods) === 1)
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Bayar Penuh</span>
                    @else
                        <button type="button" onclick="@this.set('amountCredit', {{ max(0, (int)$grandTotal - (int)$amountCash - (int)$amountDebit) }})" class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1 rounded hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors uppercase tracking-wider">Sisa Tagihan</button>
                    @endif
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 dark:text-slate-500 text-sm">Rp</span>
                    <input type="number" wire:model.live="amountCredit" wire:keyup="distributePaymentAmounts" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold text-base focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" placeholder="0">
                </div>
            </div>
        @endif
    </div>

    @php
        $totalPaid = 0;
        if (in_array('cash', $selectedPaymentMethods)) $totalPaid += intval($amountCash);
        if (in_array('debit', $selectedPaymentMethods)) $totalPaid += intval($amountDebit);
        if (in_array('credit', $selectedPaymentMethods)) $totalPaid += intval($amountCredit);
        
        $change = max(0, $totalPaid - $grandTotal);
    @endphp

    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 mb-6">
        <div>
            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400 block">Total Dibayar</span>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Dari seluruh metode</span>
        </div>
        <span class="text-lg font-bold text-slate-850 dark:text-slate-200">
            Rp {{ number_format($totalPaid, 0, ',', '.') }}
        </span>
    </div>

    @if (in_array('cash', $selectedPaymentMethods) && $change > 0)
        <div class="flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100/50 dark:border-emerald-955/50 mb-6">
            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-400">Kembalian</span>
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($change, 0, ',', '.') }}
            </span>
        </div>
    @elseif (!in_array('cash', $selectedPaymentMethods) && $totalPaid != $grandTotal)
        <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-950/20 rounded-2xl border border-red-100/50 dark:border-red-955/50 mb-6">
            <span class="text-sm font-semibold text-red-800 dark:text-red-400">Status Pembayaran</span>
            <span class="text-xs font-bold text-red-650 dark:text-red-400">
                Nominal harus pas Rp {{ number_format($grandTotal, 0, ',', '.') }}
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
