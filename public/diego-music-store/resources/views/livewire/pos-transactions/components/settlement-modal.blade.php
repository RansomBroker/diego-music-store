{{-- ===================== MODAL PELUNASAN PIUTANG ===================== --}}
<x-pos.modal
    wire:model="showSettlementModal"
    title="Input Pelunasan Piutang"
    subtitle="Proses pelunasan piutang transaksi {{ $settlementSale->invoice_number ?? '' }}"
    icon="ph-hand-coins"
    maxWidth="4xl"
>
    @if ($settlementSale)
        <div class="space-y-6">
            <!-- Informasi Utama -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tanggal Pelunasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Pelunasan <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        wire:model="settlementDate"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                    >
                </div>

                <!-- Pelanggan (LOCKED / DISABLED) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pelanggan (Sesuai Transaksi)</label>
                    <div class="relative">
                        <input
                            type="text"
                            class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-700 dark:text-slate-300 cursor-not-allowed"
                            value="{{ $settlementSale->customer->name ?? 'Pelanggan Umum' }}"
                            disabled
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">Terkunci</span>
                    </div>
                </div>

                <!-- Akun Kas / Bank -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Akun Kas / Bank <span class="text-rose-500">*</span></label>
                    <select
                        wire:model="settlementAccountId"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                    >
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Metode Pelunasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Metode Pelunasan <span class="text-rose-500">*</span></label>
                    <select
                        wire:model="settlementPaymentMethod"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                    >
                        <option value="Tunai">Tunai / Cash</option>
                        <option value="Transfer BCA">Transfer BCA</option>
                        <option value="Transfer Mandiri">Transfer Mandiri</option>
                        <option value="Transfer BNI">Transfer BNI</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>

                <!-- Referensi Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Referensi Pembayaran</label>
                    <input
                        type="text"
                        wire:model="settlementReference"
                        placeholder="e.g. Bukti Transfer #882190"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                    >
                </div>

                <!-- Nominal Pelunasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nominal Pelunasan (Rp) <span class="text-rose-500">*</span></label>
                    <x-money-input
                        wire:model.live="settlementAmount"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm  font-bold text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors"
                        placeholder="0"
                    />
                </div>
            </div>

            <!-- Detail Transaksi Row -->
            <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>No. Invoice:</span>
                    <span class=" font-bold text-slate-800 dark:text-slate-200">{{ $settlementSale->invoice_number }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Tanggal Transaksi:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $settlementSale->invoice_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Total Transaksi:</span>
                    <span class=" font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($settlementSale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Sisa Piutang / Tagihan:</span>
                    <span class="text-lg  font-black text-amber-600 dark:text-amber-400">Rp {{ number_format($settlementSale->getPiutangAmount(), 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="closeSettlementModal"
                >
                    Batal
                </x-pos.utility.button>
                <x-pos.utility.button
                    type="button"
                    variant="success"
                    wire:click="processSettlement"
                    wire:loading.attr="disabled"
                    icon="ph-check"
                >
                    <span wire:loading.remove wire:target="processSettlement">Simpan & Posting Pelunasan</span>
                    <span wire:loading wire:target="processSettlement">Memproses...</span>
                </x-pos.utility.button>
            </div>
        </div>
    @endif
</x-pos.modal>
