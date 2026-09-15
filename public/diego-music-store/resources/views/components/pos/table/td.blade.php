@php
    $classes = $attributes->get('class', '');
    $hasTextColor = preg_match('/\b!?text-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black|primary)\b/', $classes);
    $defaultColor = $hasTextColor ? '' : 'text-slate-800 dark:text-slate-200';
@endphp
<td {{ $attributes->merge(['class' => "px-6 py-3.5 text-sm font-normal {$defaultColor}"]) }}>
    {{ $slot }}
</td>

