<!-- MODAL 2: MODAL PELUNASAN DEPOSIT -->
<x-pos.modal
    wire:model="showSettleModal"
    title="Pelunasan Pesanan & Titipan Dana"
    subtitle="Mengalihkan dana dari Penitipan Dana ke Pendapatan Penjualan"
    icon="ph-check-circle"
    maxWidth="lg"
>
    @if ($settlingDeposit)
        <form wire:submit="processSettlement" class="space-y-5">
            
            <!-- Detail Ringkasan Pesanan -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2 text-xs sm:text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Deposit:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $settlingDeposit->deposit_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pelanggan:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $settlingDeposit->customer?->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Barang Pesanan:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $settlingDeposit->product_name }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500">Total Nilai Pesanan:</span>
                    <span class="font-bold text-slate-900 dark:text-white ">Rp {{ number_format($settlingDeposit->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Deposit Awal (Penitipan Dana):</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 ">Rp {{ number_format($settlingDeposit->deposit_amount, 0, ',', '.') }}</span>
                </div>

                <!-- Sisa yang Harus Dibayar Saat Pelunasan -->
                <div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                        Sisa Pelunasan Dibayar:
                    </span>
                    <span class="text-lg font-black text-amber-700 dark:text-amber-400 ">
                        Rp {{ number_format($settlingDeposit->remaining_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Metode Pembayaran Pelunasan -->
            <div class="space-y-3 relative" x-data="{ open: false }">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Metode Pembayaran
                </label>
                @php
                    $paymentMethods = \App\Models\PaymentMethod::whereNull('parent_id')->with('children')->get();
                    $activeMethod = $paymentMethods->firstWhere('code', $settlement_payment_method);
                @endphp
                
                <div class="relative">
                    <!-- Dropdown Trigger -->
                    <div 
                        @click="open = !open" 
                        @click.away="open = false"
                        class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-primary hover:ring-1 hover:ring-primary/50 transition-all"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!$activeMethod)
                                <span class="text-sm font-medium text-slate-400">Pilih Metode Pembayaran...</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary dark:bg-blue-950/40 dark:text-blue-400 rounded-full font-bold text-xs uppercase tracking-wider">
                                    {{ $activeMethod->name }}
                                </span>
                            @endif
                        </div>
                        <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </div>

                    <!-- Dropdown Options List -->
                    <div 
                        x-show="open" 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                        class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg dark:shadow-slate-950/80 max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"
                        style="display: none;"
                    >
                        @foreach ($paymentMethods as $pm)
                            @php
                                $iconClass = 'ph-credit-card';
                                $bgClass = 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-455';
                                if ($pm->code === 'cash') {
                                    $iconClass = 'ph-money';
                                    $bgClass = 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450';
                                } elseif (in_array($pm->code, ['credit', 'complimentary'])) {
                                    $iconClass = 'ph-hand-coins';
                                    $bgClass = 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-455';
                                }
                            @endphp

                            <button 
                                type="button" 
                                wire:click="$set('settlement_payment_method', '{{ $pm->code }}')" 
                                @click="open = false"
                                class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 {{ $bgClass }} rounded-lg flex items-center justify-center">
                                        <i class="ph-bold {{ $iconClass }} text-base"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-750 dark:text-slate-300">{{ $pm->name }}</span>
                                        @if (isset($pm->children) && count($pm->children) > 0)
                                            <span class="text-[10px] text-slate-400 font-medium">Sub-metode: {{ $pm->children->pluck('name')->implode(', ') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if ($settlement_payment_method === $pm->code)
                                    <i class="ph-bold ph-check text-primary dark:text-blue-400 text-sm"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Sub Method Selection -->
                @if($activeMethod && count($activeMethod->children) > 0)
                    <div class="mt-3 p-4 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-800 rounded-xl">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">
                            Pilih Sub-metode ({{ $activeMethod->name }})
                        </label>
                        <select wire:model="settlement_account_id" required class="w-full text-sm rounded-lg border-slate-200 focus:border-primary focus:ring-primary dark:bg-slate-800 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                            <option value="">-- Pilih Rekening / Kartu --</option>
                            @foreach($activeMethod->children as $child)
                                <option value="{{ $child->account_id }}">{{ $child->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Catatan / Referensi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-pos.form.input
                    label="No. Referensi Transfer / Bukti"
                    model="settlement_reference"
                    placeholder="Misal: TRF-BCA-98762"
                />

                <x-pos.form.input
                    label="Catatan Tambahan (Opsional)"
                    model="settlement_notes"
                    placeholder="Estimasi pengiriman, dll."
                />
            </div>

            <!-- Footer Actions -->
            <div class="pt-5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="$set('showSettleModal', false)"
                >
                    Batal
                </x-pos.utility.button>
                <x-pos.utility.button
                    type="submit"
                    variant="success"
                    icon="ph-check"
                >
                    Konfirmasi Pelunasan
                </x-pos.utility.button>
            </div>

        </form>
    @endif
</x-pos.modal>
