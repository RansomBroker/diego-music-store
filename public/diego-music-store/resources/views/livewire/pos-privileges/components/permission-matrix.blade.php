<!-- Two-Section Permission Matrix (Front Office & Backoffice) -->
<div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden bg-white dark:bg-slate-900/40">
    <!-- Section Tabs & Search Toolbar -->
    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div class="flex items-center gap-2">
            @foreach ($filteredSections as $secKey => $sec)
                @php
                    $isActiveTab = $activeSectionTab === $secKey;
                    $tabCount = $secKey === 'frontoffice' ? $panelSummary['fo_count'] : $panelSummary['bo_count'];
                    $tabTotal = $secKey === 'frontoffice' ? $panelSummary['fo_total'] : $panelSummary['bo_total'];
                @endphp
                <button
                    type="button"
                    wire:click="setSectionTab('{{ $secKey }}')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $isActiveTab ? 'bg-primary text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
                >
                    <span>{{ $sec['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $tabCount }}/{{ $tabTotal }}
                    </span>
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <i class="ph ph-magnifying-glass text-slate-400 text-sm absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    wire:model.live.debounce.200ms="permissionSearch"
                    placeholder="Filter menu atau fitur..."
                    class="pl-8 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-primary"
                >
            </div>
            <button
                type="button"
                wire:click="selectSectionPermissions('{{ $activeSectionTab }}')"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer"
            >
                Pilih Semua Tab
            </button>
            <button
                type="button"
                wire:click="deselectSectionPermissions('{{ $activeSectionTab }}')"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition cursor-pointer"
            >
                Kosongkan Tab
            </button>
        </div>
    </div>

    <!-- Scrollable Feature Groups -->
    @php $currentSection = $filteredSections[$activeSectionTab] ?? null; @endphp
    <div class="p-4 space-y-4 max-h-[48vh] overflow-y-auto">
        @if ($currentSection && ! empty($currentSection['groups']))
            @foreach ($currentSection['groups'] as $groupKey => $group)
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                    <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                            {{ $group['label'] }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                wire:click="selectGroupPermissions('{{ $activeSectionTab }}', '{{ $groupKey }}')"
                                class="px-2 py-1 rounded text-[11px] font-bold text-primary hover:bg-primary/10 dark:text-blue-400 transition cursor-pointer"
                            >
                                Pilih Grup
                            </button>
                            <button
                                type="button"
                                wire:click="deselectGroupPermissions('{{ $activeSectionTab }}', '{{ $groupKey }}')"
                                class="px-2 py-1 rounded text-[11px] font-bold text-slate-500 hover:bg-slate-200/70 dark:text-slate-400 dark:hover:bg-slate-700 transition cursor-pointer"
                            >
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach ($group['features'] as $featureKey => $feature)
                            @php
                                $featurePerms = array_keys($feature['permissions'] ?? []);
                                $featureSelectedCount = count(array_intersect($selectedPermissions, $featurePerms));
                            @endphp
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/40 dark:bg-slate-950/40">
                                <div class="flex items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-200/70 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                        {{ $feature['label'] }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="toggleFeaturePermissions('{{ $activeSectionTab }}', '{{ $groupKey }}', '{{ $featureKey }}')"
                                        class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $featureSelectedCount === count($featurePerms) && count($featurePerms) > 0 ? 'bg-primary text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} cursor-pointer"
                                    >
                                        {{ $featureSelectedCount }}/{{ count($featurePerms) }}
                                    </button>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach ($feature['permissions'] as $permKey => $permLabel)
                                        <label class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer hover:text-slate-900 dark:hover:text-white">
                                            <input
                                                type="checkbox"
                                                value="{{ $permKey }}"
                                                wire:model.live="selectedPermissions"
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                                            >
                                            <span>{{ $permLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-10 text-center text-xs text-slate-400">
                Tidak ada fitur atau izin yang cocok dengan pencarian "{{ $permissionSearch }}".
            </div>
        @endif
    </div>
</div>

