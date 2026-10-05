<div class="space-y-6 max-w-3xl">
    <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-4 space-y-8 py-2">
        {{-- Event 1: Dibuat / Difokuskan --}}
        <div class="relative pl-6">
            <span class="absolute -left-2.5 top-1 w-5 h-5 rounded-full bg-emerald-500 border-4 border-white dark:border-gray-900"></span>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Produk Ditetapkan Sebagai Fokus
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($record->focused_at ?: $record->created_at)->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                    <div>Sumber: <span class="font-semibold">{{ $record->source === 'RULE' ? 'Rekomendasi Sistem (' . ($record->rule?->name ?? 'Rule') . ')' : 'Penetapan Manual' }}</span></div>
                    <div>Petugas: <span class="font-semibold">{{ $record->focusedBy?->name ?? 'Sistem' }}</span></div>
                    <div>Alasan: <em>"{{ $record->reason }}"</em></div>
                    @if($record->note)
                        <div>Catatan: {{ $record->note }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Event 2: Batas Waktu Aktif --}}
        @if($record->active_until)
            <div class="relative pl-6">
                <span class="absolute -left-2.5 top-1 w-5 h-5 rounded-full {{ $record->active_until < now() ? 'bg-amber-500' : 'bg-blue-500' }} border-4 border-white dark:border-gray-900"></span>
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Batas Waktu Periode Fokus
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($record->active_until)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        @if($record->active_until < now())
                            <span class="text-amber-600 dark:text-amber-400 font-medium">Periode fokus telah berakhir pada tanggal tersebut.</span>
                        @else
                            <span class="text-blue-600 dark:text-blue-400 font-medium">Fokus aktif sampai dengan tanggal tersebut.</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Event 3: Ditandai Selesai (RESOLVED) --}}
        @if($record->status === 'RESOLVED')
            <div class="relative pl-6">
                <span class="absolute -left-2.5 top-1 w-5 h-5 rounded-full bg-blue-600 border-4 border-white dark:border-gray-900"></span>
                <div class="bg-blue-50 dark:bg-blue-950/40 p-4 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-blue-900 dark:text-blue-200">
                            Fokus Selesai (RESOLVED)
                        </span>
                        <span class="text-xs text-blue-600 dark:text-blue-400">
                            {{ $record->resolved_at ? \Carbon\Carbon::parse($record->resolved_at)->format('d/m/Y H:i') : '-' }}
                        </span>
                    </div>
                    <div class="text-xs text-blue-800 dark:text-blue-300 mt-2 space-y-1">
                        <div>Diselesaikan oleh: <span class="font-semibold">{{ $record->resolvedBy?->name ?? '-' }}</span></div>
                        <div>Catatan Penyelesaian: <em>"{{ $record->resolution_note ?? 'Tidak ada catatan' }}"</em></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Event 4: Diabaikan (DISMISSED) --}}
        @if($record->status === 'DISMISSED')
            <div class="relative pl-6">
                <span class="absolute -left-2.5 top-1 w-5 h-5 rounded-full bg-red-500 border-4 border-white dark:border-gray-900"></span>
                <div class="bg-red-50 dark:bg-red-950/40 p-4 rounded-xl border border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-red-900 dark:text-red-200">
                            Fokus Dibatalkan / Diabaikan (DISMISSED)
                        </span>
                        <span class="text-xs text-red-600 dark:text-red-400">
                            {{ $record->dismissed_at ? \Carbon\Carbon::parse($record->dismissed_at)->format('d/m/Y H:i') : '-' }}
                        </span>
                    </div>
                    <div class="text-xs text-red-800 dark:text-red-300 mt-2 space-y-1">
                        <div>Diabaikan oleh: <span class="font-semibold">{{ $record->dismissedBy?->name ?? '-' }}</span></div>
                        <div>Alasan: <em>"{{ $record->dismissal_note ?? 'Tidak ada alasan' }}"</em></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
