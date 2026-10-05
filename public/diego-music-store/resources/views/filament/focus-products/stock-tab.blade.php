<div class="space-y-6">
    {{-- Metrik Stok & Aging --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Stok Tersedia Cabang</div>
            <div class="text-2xl font-bold {{ $currentStock > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500' }} mt-1">
                {{ number_format($currentStock) }} <span class="text-sm font-normal text-gray-500">unit</span>
            </div>
        </div>
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Harga Pokok (HPP)</div>
            <div class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1 font-mono">
                Rp {{ number_format($hpp, 0, ',', '.') }}
            </div>
        </div>
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Nilai Modal Mengendap</div>
            <div class="text-xl font-bold text-red-600 dark:text-red-400 mt-1 font-mono">
                Rp {{ number_format($currentStock * $hpp, 0, ',', '.') }}
            </div>
        </div>
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
            <div class="text-xs text-amber-700 dark:text-amber-300 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Estimasi Aging Stok (FIFO)
            </div>
            <div class="text-2xl font-extrabold text-amber-800 dark:text-amber-200 mt-1">
                {{ $agingData['aging_days'] }} <span class="text-sm font-normal">hari</span>
            </div>
            <div class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                Batch tertua: {{ $agingData['oldest_batch_date'] ? \Carbon\Carbon::parse($agingData['oldest_batch_date'])->format('d/m/Y') : '-' }}
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Mutasi Stok --}}
    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-gray-100 dark:bg-gray-800/80 px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Riwayat Mutasi Stok di {{ $branchName }}</h4>
            <span class="text-xs text-gray-500">Menampilkan mutasi terbaru</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-center">Tipe Mutasi</th>
                        <th class="px-4 py-3 text-center">Jumlah (Qty)</th>
                        <th class="px-4 py-3 text-right">Nilai HPP</th>
                        <th class="px-4 py-3">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($movement->type === 'in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                                        Masuk (IN)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">
                                        Keluar (OUT)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                Rp {{ number_format($movement->hpp ?: $movement->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">
                                {{ $movement->reference_type ?? '-' }} #{{ $movement->reference_id ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                Belum ada riwayat mutasi stok tercatat untuk produk ini di cabang terkait.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
