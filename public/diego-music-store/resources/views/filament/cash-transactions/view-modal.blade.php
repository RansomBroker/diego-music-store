@php
    $typeLabel = match ($record->type) {
        'in' => 'Kas Masuk',
        'out' => 'Kas Keluar',
        'transfer' => 'Transfer / Setor Kas',
        default => ucfirst((string) $record->type),
    };
    $typeBadgeClass = match ($record->type) {
        'in' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
        'out' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400',
        'transfer' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
    $journal = $record->journalEntry?->loadMissing('items.account');
@endphp

<div class="space-y-4 text-sm">
    <div class="grid grid-cols-2 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">No. Transaksi</div>
            <div class="font-bold text-gray-900 dark:text-white mt-0.5">{{ $record->transaction_no }}</div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal</div>
            <div class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $record->transaction_date?->format('d M Y') ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cabang</div>
            <div class="font-semibold text-gray-900 dark:text-white mt-0.5">{{ $record->branch?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipe &amp; Status</div>
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $record->status === 'posted' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $record->status === 'posted' ? 'Posted' : 'Draft' }}
                </span>
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kredit (Sumber / Asal)</div>
            <div class="font-semibold text-gray-900 dark:text-white mt-0.5">
                {{ $record->sourceAccount ? "[{$record->sourceAccount->code}] {$record->sourceAccount->name}" : '-' }}
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Debit (Beban / Tujuan)</div>
            <div class="font-semibold text-gray-900 dark:text-white mt-0.5">
                {{ $record->destinationAccount ? "[{$record->destinationAccount->code}] {$record->destinationAccount->name}" : '-' }}
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nominal</div>
            <div class="text-base font-extrabold text-primary-600 dark:text-primary-400 mt-0.5">
                Rp {{ number_format((int) $record->amount, 0, ',', '.') }}
            </div>
        </div>
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sesi Kasir / Pembuat</div>
            <div class="font-semibold text-gray-900 dark:text-white mt-0.5">
                {{ $record->cash_session_id ? 'Sesi #' . str_pad($record->cash_session_id, 5, '0', STR_PAD_LEFT) : 'Backoffice (Non-Sesi)' }}
                @if ($record->creator)
                    <span class="text-xs text-gray-500 dark:text-gray-400">• {{ $record->creator->name }}</span>
                @endif
            </div>
        </div>
        <div class="col-span-2">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Catatan / Keterangan</div>
            <div class="text-gray-800 dark:text-gray-200 mt-0.5">{{ $record->notes ?: '-' }}</div>
        </div>
    </div>

    @if ($journal)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs">
                <span class="font-bold text-gray-700 dark:text-gray-200">Jurnal Akuntansi Terkait</span>
                <span class="font-mono font-semibold text-gray-500 dark:text-gray-400">{{ $journal->entry_no }}</span>
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/70 dark:bg-gray-800/30 text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-2">Akun</th>
                        <th class="px-4 py-2 text-right">Debit</th>
                        <th class="px-4 py-2 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($journal->items as $item)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                {{ $item->account ? "[{$item->account->code}] {$item->account->name}" : '-' }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono {{ $item->debit > 0 ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                {{ $item->debit > 0 ? 'Rp ' . number_format((int) $item->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-2 text-right font-mono {{ $item->credit > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                {{ $item->credit > 0 ? 'Rp ' . number_format((int) $item->credit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

