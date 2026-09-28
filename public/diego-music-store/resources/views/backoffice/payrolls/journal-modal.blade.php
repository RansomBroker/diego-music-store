<div class="space-y-4 text-xs">
    <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30 flex items-center justify-between">
        <div>
            <span class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Total Gaji Bersih Dibayarkan (THP)</span>
            <span class="text-lg font-black text-emerald-900 dark:text-emerald-200">
                Rp {{ number_format($record->total_net_salary, 0, ',', '.') }}
            </span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-gray-500 dark:text-gray-400 block">Status Pembukuan</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                POSTED
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/40 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Kode Payroll:</span>
            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $record->payroll_code }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Periode Bulan:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->period }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Rekening Pembayaran:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->paymentAccount?->name ?? 'Kas / Bank' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Tanggal Pembayaran:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->paid_at ? \Carbon\Carbon::parse($record->paid_at)->translatedFormat('d F Y') : '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">No. Jurnal Akuntansi:</span>
            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $record->journal_no ?? ($record->journalEntry?->entry_no ?? '-') }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Total Karyawan:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->total_employees }} Orang</span>
        </div>
    </div>

    @php
        $kasbonOffset = 0;
        foreach ($record->items as $item) {
            if (!empty($item->deduction_details) && is_array($item->deduction_details)) {
                foreach ($item->deduction_details as $ded) {
                    if (isset($ded['advance_id']) && isset($ded['installment_amount'])) {
                        $kasbonOffset += (float) $ded['installment_amount'];
                    }
                }
            }
        }
        $grossCost = $record->total_net_salary + $kasbonOffset;
    @endphp

    <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-[11px] space-y-1">
        <div class="font-bold text-gray-800 dark:text-gray-200">
            Jurnal Umum Pembayaran Payroll:
        </div>
        <div class="font-mono text-gray-700 dark:text-gray-300 pl-3 space-y-0.5">
            <div class="text-emerald-700 dark:text-emerald-400">[D] 611101001 - BEBAN GAJI KARYAWAN: Rp {{ number_format($grossCost, 0, ',', '.') }}</div>
            @if($kasbonOffset > 0)
                <div class="text-amber-700 dark:text-amber-400 pl-4">[K] 111301002 - PIUTANG KARYAWAN (Offset Kasbon): Rp {{ number_format($kasbonOffset, 0, ',', '.') }}</div>
            @endif
            <div class="text-rose-700 dark:text-rose-400 pl-4">[K] {{ $record->paymentAccount?->code ?? '111201001' }} - {{ $record->paymentAccount?->name ?? 'Kas / Bank' }}: Rp {{ number_format($record->total_net_salary, 0, ',', '.') }}</div>
        </div>
    </div>
</div>
