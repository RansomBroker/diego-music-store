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
                    <span class="font-bold text-slate-900 dark:text-white font-mono">Rp {{ number_format($settlingDeposit->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Deposit Awal (Penitipan Dana):</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($settlingDeposit->deposit_amount, 0, ',', '.') }}</span>
                </div>

                <!-- Sisa yang Harus Dibayar Saat Pelunasan -->
                <div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                        Sisa Pelunasan Dibayar:
                    </span>
                    <span class="text-lg font-black text-amber-700 dark:text-amber-400 font-mono">
                        Rp {{ number_format($settlingDeposit->remaining_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <button
                    type="button"
                    wire:click="$set('showSettleModal', false)"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-black shadow-md transition active:scale-95 cursor-pointer"
                >
                    <i class="ph-bold ph-check text-base"></i>
                    <span>Konfirmasi Pelunasan</span>
                </button>
            </div>

        </form>
    @endif
</x-pos.modal>
