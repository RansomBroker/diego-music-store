<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <x-heroicon-o-sparkles class="w-5 h-5" />
                    </span>
                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
                        Rekomendasi Produk Fokus
                    </span>
                </div>

                @if($totalPending > 0)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300">
                        {{ $totalPending }}
                    </span>
                @endif
            </div>
        </x-slot>

        @if($recommendations->isEmpty())
            <div class="py-6 text-center text-gray-400 dark:text-gray-500">
                <x-heroicon-o-check-badge class="w-8 h-8 mx-auto text-emerald-500/70 mb-1.5" />
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Semua Stok Berjalan Optimal</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Tidak ada produk yang perlu difokuskan saat ini.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($recommendations as $rec)
                    @php
                        $scoreColor = $rec->score >= 85 ? 'text-red-600 bg-red-50 dark:bg-red-950/60 dark:text-red-300 border-red-200 dark:border-red-800' : ($rec->score >= 70 ? 'text-amber-600 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'text-blue-600 bg-blue-50 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800');
                        $ruleBadge = match($rec->rule?->code) {
                            'dead_stock' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200',
                            'slow_moving' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200',
                            'aging_stock' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-200',
                            'overstock' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200',
                            'stock_value_at_risk' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                        };
                    @endphp

                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition flex flex-col gap-2">
                        {{-- Baris 1: Rule, Cabang, dan Skor --}}
                        <div class="flex items-center justify-between gap-1 text-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $ruleBadge }}">
                                    {{ $rec->rule?->name }}
                                </span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    {{ $rec->branch?->name }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-extrabold border {{ $scoreColor }}">
                                Skor {{ $rec->score }}
                            </span>
                        </div>

                        {{-- Baris 2: Nama Produk & SKU --}}
                        <div>
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 line-clamp-1">
                                {{ $rec->productVariant?->product?->name }}
                                @if($rec->productVariant?->name && $rec->productVariant?->name !== 'Standard')
                                    <span class="text-gray-500 font-normal">({{ $rec->productVariant->name }})</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-400 font-mono mt-0.5">
                                SKU: {{ $rec->productVariant?->sku }}
                            </div>
                        </div>

                        {{-- Baris 3: Metrik Ringkas --}}
                        <div class="flex items-center gap-2 text-[11px] text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900/60 px-2 py-1 rounded border border-gray-100 dark:border-gray-800/60">
                            <span>Stok: <strong class="text-amber-600 dark:text-amber-400">{{ $rec->current_stock }} unit</strong></span>
                            <span>•</span>
                            <span>Terjual: <strong class="text-gray-700 dark:text-gray-300">{{ $rec->recent_sales_qty }}</strong></span>
                            @if($rec->aging_days)
                                <span>•</span>
                                <span>Umur: <strong class="text-gray-700 dark:text-gray-300">{{ $rec->aging_days }}h</strong></span>
                            @endif
                        </div>

                        {{-- Baris 4: Alasan Singkat --}}
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic line-clamp-2">
                            "{{ $rec->reason }}"
                        </p>
                    </div>
                @endforeach

                {{-- Footer Action Link --}}
                <div class="pt-2 text-center">
                    <a href="{{ $recommendationsUrl }}" class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-lg text-xs font-semibold bg-primary-600 hover:bg-primary-700 text-white shadow-sm transition">
                        <x-heroicon-m-sparkles class="w-4 h-4" />
                        Buka Semua Rekomendasi
                    </a>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
