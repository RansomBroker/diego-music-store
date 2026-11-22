@props([
    'amount' => 0,
    'rupiah' => true,
    'zeroAsDash' => true,
    'pdf' => false,
])

@php
    $value = (float) $amount;
    $formatted = $rupiah
        ? \App\Helpers\FinancialReportHelper::formatRupiah($value)
        : \App\Helpers\FinancialReportHelper::formatNumber($value, $zeroAsDash);
@endphp

@if($value < 0)
    <span {{ $attributes->merge(['class' => 'text-rose-600 dark:text-rose-400']) }} style="{{ \App\Helpers\FinancialReportHelper::amountStyle($value, $pdf) }}">{{ $formatted }}</span>
@else
    {{ $formatted }}
@endif

