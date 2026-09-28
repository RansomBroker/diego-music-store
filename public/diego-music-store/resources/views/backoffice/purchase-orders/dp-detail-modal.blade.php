<div class="space-y-4 text-xs">
    <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30 flex items-center justify-between">
        <div>
            <span class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Uang Muka Terbayar</span>
            <span class="text-lg font-black text-emerald-900 dark:text-emerald-200">
                Rp {{ number_format($record->dp_amount, 0, ',', '.') }}
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
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Rekening Pembayaran:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->dpAccount?->name ?? 'Kas / Bank' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Tanggal Transfer:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->dp_paid_at ? \Carbon\Carbon::parse($record->dp_paid_at)->translatedFormat('d F Y') : '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">No. Jurnal Akuntansi:</span>
            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $record->dp_journal_no ?? '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">No. Bukti / Referensi:</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $record->dp_reference_no ?: '-' }}</span>
        </div>
        @if($record->dp_notes)
            <div class="col-span-2 pt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Catatan:</span>
                <span class="italic text-gray-800 dark:text-gray-200">{{ $record->dp_notes }}</span>
            </div>
        @endif
    </div>

    <div class="p-3 rounded-lg border border-blue-100 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-950/20 text-[11px] space-y-1">
        <div class="font-bold text-gray-800 dark:text-gray-200">
            Jurnal Akuntansi:
        </div>
        <div class="font-mono text-gray-700 dark:text-gray-300 pl-3 space-y-0.5">
            <div>[D] 111201006 - Uang Muka Pembelian: Rp {{ number_format($record->dp_amount, 0, ',', '.') }}</div>
            <div>[K] {{ $record->dpAccount?->code ?? '111101001' }} - {{ $record->dpAccount?->name ?? 'Kas / Bank' }}: Rp {{ number_format($record->dp_amount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>
