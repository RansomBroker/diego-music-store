@php
    use App\Helpers\PoSmartAssistHelper;

    $evaluation = $evaluation ?? [];
    $isEnabled = $evaluation['is_enabled'] ?? true;
    $status = $evaluation['status'] ?? 'AMAN';
    $badgeLabel = $evaluation['badge_label'] ?? 'AMAN UNTUK DITERBITKAN';
    $badgeConfig = PoSmartAssistHelper::getStatusBadgeConfig($status);

    $totalLiquid = $evaluation['total_liquid_cash'] ?? 0;
    $commitments = $evaluation['pending_commitments'] ?? 0;
    $poTotal = $evaluation['po_grand_total'] ?? 0;
    $postPoCash = $evaluation['post_po_cash'] ?? 0;
    $bufferPct = $evaluation['buffer_percentage'] ?? 0;
    $inflow30d = $evaluation['sales_30d_inflow'] ?? 0;
    $recommendation = $evaluation['recommendation'] ?? '';
    $tips = $evaluation['tips'] ?? [];
@endphp

@if(!$isEnabled)
    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center space-x-3 text-gray-500 dark:text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-medium">Smart Assist PO sedang dinonaktifkan di Pengaturan.</span>
        </div>
    </div>
@else
    <div class="rounded-xl border shadow-sm transition-all duration-200 overflow-hidden {{ $badgeConfig['bg_class'] }}">
        <!-- Header Banner Status -->
        <div class="p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 border-b {{ $badgeConfig['border_color'] }}">
            <div class="flex items-center space-x-3">
                <div class="p-2.5 rounded-lg {{ $badgeConfig['badge_bg'] }} shadow-sm">
                    @if($status === 'AMAN')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @elseif($status === 'WASPADA')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs uppercase tracking-wider font-semibold opacity-75">Smart Assist PO Keuangan Toko</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeConfig['badge_bg'] }}">
                            {{ $badgeLabel }}
                        </span>
                    </div>
                    <h3 class="text-base font-bold mt-0.5">Analisis Kelayakan Finansial Pembelian</h3>
                </div>
            </div>

            @if($poTotal > 0)
                <div class="text-right">
                    <span class="text-xs opacity-75 block">Indikator Buffer Likuiditas Pasca PO</span>
                    <div class="text-lg font-black">
                        {{ $bufferPct }}% Sisa Kas
                    </div>
                </div>
            @endif
        </div>

        <!-- Financial Metrics Grid -->
        <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-3 rounded-lg bg-white/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-700/50">
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">1. Dana Kas & Bank Likuid</span>
                <span class="text-base font-extrabold text-gray-900 dark:text-white block mt-0.5">
                    {{ PoSmartAssistHelper::formatRupiah($totalLiquid) }}
                </span>
            </div>

            <div class="p-3 rounded-lg bg-white/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-700/50">
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">2. Komitmen/Hutang Berjalan</span>
                <span class="text-base font-extrabold text-red-600 dark:text-red-400 block mt-0.5">
                    - {{ PoSmartAssistHelper::formatRupiah($commitments) }}
                </span>
            </div>

            <div class="p-3 rounded-lg bg-white/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-700/50">
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">3. Nilai Pengadaan PO Ini</span>
                <span class="text-base font-extrabold text-blue-600 dark:text-blue-400 block mt-0.5">
                    {{ PoSmartAssistHelper::formatRupiah($poTotal) }}
                </span>
            </div>

            <div class="p-3 rounded-lg bg-white/60 dark:bg-gray-900/40 border border-gray-200/50 dark:border-gray-700/50">
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">4. Proyeksi Sisa Kas Buffer</span>
                <span class="text-base font-extrabold block mt-0.5 {{ $postPoCash >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ PoSmartAssistHelper::formatRupiah($postPoCash) }}
                </span>
            </div>
        </div>

        <!-- Dynamic Recommendation Box -->
        <div class="px-4 pb-5 sm:px-5">
            <div class="p-3.5 rounded-lg bg-white/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80">
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider block text-gray-700 dark:text-gray-300">Rekomendasi & Analisis Smart Assist:</span>
                        <p class="text-sm font-medium mt-0.5 text-gray-800 dark:text-gray-200">
                            {{ $recommendation }}
                        </p>

                        @if(!empty($tips))
                            <ul class="mt-2 space-y-1 list-disc list-inside text-xs font-medium text-gray-700 dark:text-gray-300">
                                @foreach($tips as $tip)
                                    <li>{{ $tip }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-2.5 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center space-x-1.5 text-xs text-gray-500 dark:text-gray-400 font-normal">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Info:</strong> Indikator ini bersifat pertimbangan & peringatan dini. Pengguna/Owner tetap dapat menyimpan dan memproses PO secara bebas.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
