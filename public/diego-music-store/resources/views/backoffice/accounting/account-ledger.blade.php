<div class="space-y-4 p-4 dark:text-gray-200">
    <!-- Header info -->
    <div class="grid grid-cols-2 gap-4 border-b pb-4 border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kode & Nama Akun</p>
            <p class="font-bold text-lg text-gray-800 dark:text-white">{{ $account->code }} - {{ $account->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 dark:text-gray-400">Klasifikasi / Saldo Normal</p>
            <p class="font-semibold text-gray-800 dark:text-white capitalize">
                {{ $account->classificationRelation->name ?? $account->classification }} 
                <span class="text-xs font-normal text-gray-500">({{ ucfirst($account->getNormalBalance()) }})</span>
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50">
                    <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Tanggal</th>
                    <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">No. Jurnal</th>
                    <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-600 dark:text-gray-400">Keterangan</th>
                    <th scope="col" class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Debit (Dr.)</th>
                    <th scope="col" class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Kredit (Cr.)</th>
                    <th scope="col" class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @php
                    $runningBalance = 0;
                    $normal = $account->getNormalBalance();
                @endphp
                
                @forelse($items as $item)
                    @php
                        if ($normal === 'debit') {
                            $runningBalance += ($item->debit - $item->credit);
                        } else {
                            $runningBalance += ($item->credit - $item->debit);
                        }
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="px-4 py-2.5 whitespace-nowrap text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($item->journalEntry->date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                            {{ $item->journalEntry->entry_no }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $item->journalEntry->description }}</p>
                            @if($item->notes)
                                <span class="text-xs italic text-gray-400">{{ $item->notes }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-2.5 text-right font-bold whitespace-nowrap {{ $runningBalance >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($runningBalance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                            Belum ada transaksi jurnal dibukukan (posted) untuk akun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
