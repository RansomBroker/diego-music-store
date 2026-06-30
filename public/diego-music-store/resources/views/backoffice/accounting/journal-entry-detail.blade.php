<div class="space-y-4 p-4 dark:text-gray-200">
    <!-- Header info -->
    <div class="grid grid-cols-2 gap-4 border-b pb-4 border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">No. Jurnal</p>
            <p class="font-bold text-base text-gray-800 dark:text-white">{{ $record->entry_no }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Cabang</p>
            <p class="text-gray-800 dark:text-white font-medium">{{ $record->branch->name ?? 'Multi-cabang / Pusat' }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $record->status === 'posted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                {{ ucfirst($record->status) }}
            </span>
        </div>
        @if($record->reference_type)
        <div class="col-span-2 border-t pt-2 border-gray-100 dark:border-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Referensi Sumber</p>
            <p class="text-gray-800 dark:text-white font-semibold text-sm">
                {{ $record->reference_type }} (ID: #{{ $record->reference_id }})
            </p>
        </div>
        @endif
        <div class="col-span-2 border-t pt-2 border-gray-100 dark:border-gray-800">
            <p class="text-xs text-gray-500 dark:text-gray-400">Keterangan Jurnal</p>
            <p class="text-gray-700 dark:text-gray-300 text-sm italic">{{ $record->description ?: '-' }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50">
                    <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Akun Rekening</th>
                    <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Keterangan Item</th>
                    <th scope="col" class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Debit (Dr.)</th>
                    <th scope="col" class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Kredit (Cr.)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($record->items as $item)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900 dark:text-white {{ $item->credit > 0 ? 'pl-8 text-gray-700 dark:text-gray-300' : '' }}">
                            {{ $item->account->code }} - {{ $item->account->name }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                        {{ $item->notes ?: '-' }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                        {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                        {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100/70 dark:bg-gray-800/70 font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="2" class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">Total Debit / Kredit:</td>
                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                        Rp {{ number_format($record->items->sum('debit'), 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                        Rp {{ number_format($record->items->sum('credit'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
