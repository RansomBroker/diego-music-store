@php
    $items = $comparison['items'] ?? [];
    $ratio = $comparison['ratio'] ?? null;
    $totalValue = $comparison['total_chart_value'] ?? 0;
    $hasPositiveData = $totalValue > 0;
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section
        :heading="$heading"
        :description="$description"
    >
        <x-slot name="headerEnd">
            <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <x-heroicon-m-chart-pie class="w-4 h-4 text-primary-500" />
                <span>Pie / Donut</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- Quick Presets --}}
            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-1">Preset:</span>
                <button
                    type="button"
                    wire:click="applyPreset('liabilities_vs_assets')"
                    class="px-2.5 py-1 text-xs rounded-full font-medium transition-colors border {{ in_array('100000000', $selectedAccounts) && in_array('200000000', $selectedAccounts) && count($selectedAccounts) === 2 ? 'bg-primary-50 text-primary-700 border-primary-300 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700' }}"
                >
                    Liabilitas vs Aset
                </button>
                <button
                    type="button"
                    wire:click="applyPreset('balance_sheet')"
                    class="px-2.5 py-1 text-xs rounded-full font-medium transition-colors border {{ in_array('300000000', $selectedAccounts) && count($selectedAccounts) === 3 ? 'bg-primary-50 text-primary-700 border-primary-300 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700' }}"
                >
                    Neraca (Aset-Liab-Ekuitas)
                </button>
                <button
                    type="button"
                    wire:click="applyPreset('income_statement')"
                    class="px-2.5 py-1 text-xs rounded-full font-medium transition-colors border {{ in_array('400000000', $selectedAccounts) ? 'bg-primary-50 text-primary-700 border-primary-300 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700' }}"
                >
                    Laba Rugi
                </button>
                <button
                    type="button"
                    wire:click="applyPreset('liquid_assets')"
                    class="px-2.5 py-1 text-xs rounded-full font-medium transition-colors border {{ in_array('111100000', $selectedAccounts) ? 'bg-primary-50 text-primary-700 border-primary-300 dark:bg-primary-950/40 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700' }}"
                >
                    Aset Lancar
                </button>
            </div>

            {{-- Multi-Select Dropdown with Alpine.js --}}
            <div
                x-data="{
                    open: false,
                    search: '',
                    matchesSearch(text) {
                        if (!this.search) return true;
                        return text.toLowerCase().includes(this.search.toLowerCase());
                    }
                }"
                class="relative"
            >
                <div class="flex items-center justify-between gap-2 p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex flex-wrap items-center gap-1.5 flex-1 min-w-0">
                        @forelse ($items as $item)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium text-white shadow-sm" style="background-color: {{ $item['color'] }}">
                                <span>{{ $item['code'] }} - {{ $item['name'] }}</span>
                                <button
                                    type="button"
                                    wire:click="removeAccount('{{ $item['code'] }}')"
                                    class="hover:opacity-75 focus:outline-none"
                                    title="Hapus"
                                >
                                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                </button>
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">Belum ada akun COA dipilih. Silakan pilih akun...</span>
                        @endforelse
                    </div>

                    <button
                        type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm shrink-0"
                    >
                        <x-heroicon-m-plus class="w-3.5 h-3.5 text-primary-500" />
                        <span>Pilih Akun COA</span>
                        <x-heroicon-m-chevron-down class="w-3.5 h-3.5 text-gray-400" />
                    </button>
                </div>

                {{-- Dropdown Menu --}}
                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute z-50 left-0 right-0 mt-1 max-h-80 overflow-y-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-2 space-y-2 text-xs"
                    style="display: none;"
                >
                    {{-- Search Input --}}
                    <div class="relative">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari kode atau nama akun..."
                            class="w-full px-2.5 py-1.5 pl-8 text-xs rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                        />
                        <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" />
                    </div>

                    {{-- Account Groups --}}
                    <div class="space-y-3">
                        @foreach ($coaOptions as $groupName => $options)
                            <div>
                                <div class="font-semibold text-gray-400 uppercase tracking-wider text-[10px] px-1 py-0.5 border-b border-gray-100 dark:border-gray-700">
                                    {{ $groupName }}
                                </div>
                                <div class="mt-1 space-y-0.5">
                                    @foreach ($options as $code => $label)
                                        @php
                                            $isSelected = in_array((string) $code, $selectedAccounts, true);
                                        @endphp
                                        <label
                                            x-show="matchesSearch('{{ addslashes($label) }}')"
                                            class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer transition-colors"
                                        >
                                            <input
                                                type="checkbox"
                                                wire:click="toggleAccount('{{ $code }}')"
                                                {{ $isSelected ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                            />
                                            <span class="flex-1 {{ $isSelected ? 'font-semibold text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Ratio Highlight Banner (e.g. Liabilitas terhadap Aset) --}}
            @if ($ratio)
                <div class="flex items-center justify-between p-2.5 bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 rounded-lg text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚖️</span>
                        <div>
                            <span class="font-semibold text-blue-900 dark:text-blue-200">{{ $ratio['label'] }}:</span>
                            <span class="font-bold text-blue-700 dark:text-blue-300 text-sm ml-1">{{ $ratio['percentage'] }}%</span>
                        </div>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 text-[11px]">{{ $ratio['description'] }}</span>
                </div>
            @endif

            {{-- The Chart --}}
            <div class="relative">
                <div
                    x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                    wire:ignore
                    data-chart-type="doughnut"
                    x-data="chart({
                        cachedData: @js($cachedData),
                        options: @js($chartOptions),
                        type: 'doughnut',
                    })"
                    class="fi-wi-chart-canvas-ctn relative flex items-center justify-center my-2"
                    style="height: 220px;"
                >
                    <canvas x-ref="canvas" style="max-height: 220px;"></canvas>

                    <span
                        x-ref="backgroundColorElement"
                        class="fi-wi-chart-bg-color"
                    ></span>

                    <span
                        x-ref="borderColorElement"
                        class="fi-wi-chart-border-color"
                    ></span>

                    <span
                        x-ref="gridColorElement"
                        class="fi-wi-chart-grid-color"
                    ></span>

                    <span
                        x-ref="textColorElement"
                        class="fi-wi-chart-text-color"
                    ></span>
                </div>

                @if (!$hasPositiveData)
                    <div class="text-center text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                        Belum ada mutasi jurnal terposting untuk akun ini (Saldo Rp 0).
                    </div>
                @endif
            </div>

            {{-- Accounts Breakdown Table / Legend --}}
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3">
                <div class="space-y-1.5">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between text-xs py-1 px-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="w-3 h-3 rounded-full shrink-0 shadow-xs" style="background-color: {{ $item['color'] }}"></span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $item['code'] }} - {{ $item['name'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 ml-2">
                                <span class="font-semibold text-gray-700 dark:text-gray-200 font-mono">
                                    {{ $item['formatted_balance'] }}
                                </span>
                                <span class="inline-flex items-center justify-center w-12 px-1.5 py-0.5 rounded text-[11px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    {{ $item['percentage'] }}%
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
