<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm text-gray-500 dark:text-gray-400">
        <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-700 dark:text-gray-300">
            <tr>
                <th scope="col" class="px-6 py-3">Waktu</th>
                <th scope="col" class="px-6 py-3">Varian</th>
                <th scope="col" class="px-6 py-3">Cabang</th>
                <th scope="col" class="px-6 py-3">Tipe</th>
                <th scope="col" class="px-6 py-3 text-right">Qty</th>
                <th scope="col" class="px-6 py-3 text-right">Harga Satuan</th>
                <th scope="col" class="px-6 py-3 text-right">HPP Berjalan</th>
                <th scope="col" class="px-6 py-3 text-right">Total Nilai</th>
                <th scope="col" class="px-6 py-3">Dokumen Referensi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            @foreach($movements as $movement)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">
                        {{ $movement->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $movement->productVariant->name ?: 'Default (No Variant)' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $movement->branch->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($movement->type === 'in')
                            <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/20">
                                Masuk (IN)
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/30 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/20 dark:ring-red-500/20">
                                Keluar (OUT)
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $movement->type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 dark:text-white">
                        Rp {{ number_format($movement->unit_cost, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 dark:text-white">
                        Rp {{ number_format($movement->hpp, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 dark:text-white">
                        Rp {{ number_format($movement->quantity * $movement->unit_cost, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                        {{ $movement->reference_label }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
