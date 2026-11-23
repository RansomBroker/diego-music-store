@props([
    'model',
    'options' => [],
    'live' => false,
    'placeholder' => '-- Pilih --',
    'searchPlaceholder' => 'Cari nama atau kode...',
    'icon' => null,
    'accent' => 'primary',
])

@php
    $accentStyles = match ($accent) {
        'emerald' => [
            'focus' => 'focus:border-emerald-500 focus:ring-emerald-500',
            'active' => 'bg-emerald-50/90 dark:bg-emerald-950/40 font-bold text-emerald-700 dark:text-emerald-300',
            'hover' => 'hover:bg-emerald-50 dark:hover:bg-emerald-950/30',
        ],
        'rose' => [
            'focus' => 'focus:border-rose-500 focus:ring-rose-500',
            'active' => 'bg-rose-50/90 dark:bg-rose-950/40 font-bold text-rose-700 dark:text-rose-300',
            'hover' => 'hover:bg-rose-50 dark:hover:bg-rose-950/30',
        ],
        'blue' => [
            'focus' => 'focus:border-blue-500 focus:ring-blue-500',
            'active' => 'bg-blue-50/90 dark:bg-blue-950/40 font-bold text-blue-700 dark:text-blue-300',
            'hover' => 'hover:bg-blue-50 dark:hover:bg-blue-950/30',
        ],
        default => [
            'focus' => 'focus:border-primary focus:ring-primary',
            'active' => 'bg-primary/10 dark:bg-blue-950/40 font-bold text-primary dark:text-blue-300',
            'hover' => 'hover:bg-slate-50 dark:hover:bg-slate-800/70',
        ],
    };

    $normalizedOptions = collect($options)->map(fn ($item) => [
        'value' => (string) ($item['value'] ?? ''),
        'label' => (string) ($item['label'] ?? ''),
        'sublabel' => isset($item['sublabel']) ? (string) $item['sublabel'] : '',
    ])->values()->all();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: @if($live) @entangle($model).live @else @entangle($model) @endif,
        options: @js($normalizedOptions),
        dropdownStyle: '',
        get selectedOption() {
            const cur = this.selected === null || this.selected === undefined ? '' : String(this.selected);
            return this.options.find(o => String(o.value) === cur) || null;
        },
        get filteredOptions() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o =>
                o.label.toLowerCase().includes(q) || o.sublabel.toLowerCase().includes(q)
            );
        },
        updatePosition() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const menuHeight = 280;
            const openUp = spaceBelow < menuHeight && rect.top > spaceBelow;
            const top = openUp ? Math.max(8, rect.top - menuHeight - 6) : (rect.bottom + 6);
            this.dropdownStyle = `position: fixed; top: ${top}px; left: ${rect.left}px; width: ${rect.width}px; z-index: 9999;`;
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.updatePosition();
                this.$nextTick(() => {
                    this.updatePosition();
                    this.$refs.searchInput?.focus();
                });
            }
        },
        close() {
            this.open = false;
            this.search = '';
        },
        choose(opt) {
            this.selected = opt.value;
            this.close();
        }
    }"
    x-on:keydown.escape.window="if (open) close()"
    x-on:scroll.window.capture="if (open) updatePosition()"
    x-on:resize.window="if (open) updatePosition()"
    class="relative"
>
    <button
        x-ref="trigger"
        type="button"
        @click="toggle()"
        class="w-full flex items-center justify-between gap-2 {{ $icon ? 'pl-9 pr-3' : 'px-3' }} py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-left text-slate-900 dark:text-white focus:ring-1 focus:outline-none transition-colors cursor-pointer {{ $accentStyles['focus'] }}"
    >
        @if ($icon)
            <i class="ph {{ $icon }} absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none"></i>
        @endif
        <span
            class="truncate"
            :class="selectedOption ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-400'"
            x-text="selectedOption ? (selectedOption.sublabel ? `${selectedOption.label} (${selectedOption.sublabel})` : selectedOption.label) : @js($placeholder)"
        ></span>
        <i class="ph-bold ph-caret-down text-slate-400 text-xs flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            :style="dropdownStyle"
            @click.outside="if (!$refs.trigger.contains($event.target)) close()"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden"
        >
            <div class="p-2 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input
                        x-ref="searchInput"
                        type="text"
                        x-model="search"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 focus:outline-none {{ $accentStyles['focus'] }}"
                    />
                </div>
            </div>
            <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/70">
                <template x-for="opt in filteredOptions" :key="opt.value + '-' + opt.label">
                    <button
                        type="button"
                        @click="choose(opt)"
                        class="w-full px-3 py-2 text-left text-xs flex items-center justify-between gap-2 transition-colors cursor-pointer {{ $accentStyles['hover'] }}"
                        :class="String(selected ?? '') === String(opt.value) ? '{{ $accentStyles['active'] }}' : 'text-slate-700 dark:text-slate-200'"
                    >
                        <span class="truncate" x-text="opt.label"></span>
                        <template x-if="opt.sublabel">
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex-shrink-0" x-text="opt.sublabel"></span>
                        </template>
                    </button>
                </template>
                <div x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">
                    Data tidak ditemukan.
                </div>
            </div>
        </div>
    </template>
</div>

