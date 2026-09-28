<div class="space-y-4 text-xs">
    <div class="p-3.5 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-950/30 flex items-center justify-between">
        <div>
            <span class="text-[11px] font-semibold text-blue-800 dark:text-blue-300 uppercase tracking-wider block">Nominal Kasbon Karyawan</span>
            <span class="text-lg font-black text-blue-900 dark:text-blue-200">
                Rp {{ number_format($record->amount, 0, ',', '.') }}
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
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Karyawan:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->employee?->name ?? '-' }} ({{ $record->employee?->nik ?? '-' }})</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Sumber Rekening Pengeluaran:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->disbursementAccount?->name ?? 'KAS' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Tanggal Pencairan:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->disbursed_at ? \Carbon\Carbon::parse($record->disbursed_at)->translatedFormat('d F Y') : '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">No. Jurnal Akuntansi:</span>
            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $record->journal_no ?? ($record->journalEntry?->entry_no ?? '-') }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Sisa Saldo Kasbon:</span>
            <span class="font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($record->remaining_amount, 0, ',', '.') }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Tenor Cicilan:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->tenor_months }} Bulan (@ Rp {{ number_format($record->monthly_installment, 0, ',', '.') }}/bln)</span>
        </div>
        @if($record->notes)
            <div class="col-span-2 pt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Riwayat & Catatan:</span>
                <span class="italic text-gray-800 dark:text-gray-200">{{ $record->notes }}</span>
            </div>
        @endif
    </div>

    <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-[11px] space-y-1">
        <div class="font-bold text-gray-800 dark:text-gray-200">
            Jurnal Umum Pencairan Kasbon:
        </div>
        <div class="font-mono text-gray-700 dark:text-gray-300 pl-3 space-y-0.5">
            <div class="text-emerald-700 dark:text-emerald-400">[D] 111301002 - PIUTANG KARYAWAN: Rp {{ number_format($record->amount, 0, ',', '.') }}</div>
            <div class="text-rose-700 dark:text-rose-400 pl-4">[K] {{ $record->disbursementAccount?->code ?? '111101001' }} - {{ $record->disbursementAccount?->name ?? 'KAS' }}: Rp {{ number_format($record->amount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>
