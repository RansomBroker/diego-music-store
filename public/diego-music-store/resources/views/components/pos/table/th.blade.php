@props([
    'sortable' => false,
    'field' => null,
    'sortField' => null,
    'sortDirection' => 'asc'
])

@php
    $classes = $attributes->get('class', '');
    $hasTextColor = preg_match('/\b!?text-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black|primary)\b/', $classes);
    $defaultColor = $hasTextColor ? '' : 'text-slate-550 dark:text-slate-400';
@endphp

<th {{ $attributes->merge(['class' => "px-6 py-3 text-xs font-semibold uppercase tracking-wider {$defaultColor}"]) }}>
    @if($sortable && $field)
        <button type="button" wire:click="sortBy('{{ $field }}')" class="group inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider {{ $hasTextColor ? '' : 'text-slate-550 dark:text-slate-400' }} hover:text-slate-700 dark:hover:text-slate-200 transition-colors focus:outline-none">
            {{ $slot }}
            <span class="inline-flex">
                @if ($sortField === $field)
                    <i class="ph-bold {{ $sortDirection === 'asc' ? 'ph-arrow-up' : 'ph-arrow-down' }} text-primary dark:text-blue-400 text-xs"></i>
                @else
                    <i class="ph-bold ph-arrows-down-up text-slate-350 dark:text-slate-600 text-[10px] group-hover:text-slate-500 dark:group-hover:text-slate-300 transition-colors"></i>
                @endif
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</th>

