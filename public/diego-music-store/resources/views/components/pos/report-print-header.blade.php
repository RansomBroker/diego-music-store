@props([
    'title' => 'LAPORAN ERP',
    'branch' => null,
    'dateFrom' => null,
    'dateTo' => null
])

@php
    $storeName = $branch?->store_name ?: ($branch?->name ?: 'DIEGO MUSIC STORE');
    $branchName = $branch?->name ?: 'Kantor Pusat / Utama';
    $address = $branch?->address ?: 'Jl. Diego Music Store ERP';
    $phone = $branch?->phone ?: '-';
@endphp

<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body, html, main, .overflow-y-auto, .overflow-hidden {
            overflow: visible !important;
            height: auto !important;
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        aside, nav, .no-print, button, input, select, .ph-caret-down {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .bg-white, .bg-slate-50, .bg-slate-900 {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        .text-white, .text-slate-900, .text-slate-700, .text-slate-600 {
            color: #000000 !important;
        }
        .shadow-sm, .shadow-md, .shadow {
            box-shadow: none !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9pt !important;
        }
        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 8px !important;
            color: #000000 !important;
        }
        th {
            background-color: #f1f5f9 !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
    }
    .print-only {
        display: none;
    }
</style>

<div class="print-only mb-6 pb-4 border-b-2 border-slate-900">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-xl font-black uppercase tracking-wide text-slate-900">{{ $storeName }}</h1>
            <p class="text-xs text-slate-600 font-semibold mt-0.5">Cabang: {{ $branchName }} | {{ $address }} | Telp: {{ $phone }}</p>
            <h2 class="text-lg font-black text-slate-900 mt-3 uppercase tracking-wider">{{ $title }}</h2>
            @if ($dateFrom || $dateTo)
                <p class="text-xs text-slate-600 font-bold mt-0.5">Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Awal' }} s/d {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang' }}</p>
            @endif
        </div>
        <div class="text-right text-xs text-slate-600 font-mono space-y-1">
            <div><strong>TGL CETAK:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
            <div><strong>DICETAK OLEH:</strong> {{ auth()->user()?->name ?: 'Admin Kasir' }}</div>
            <div><strong>SISTEM:</strong> Diego Music Store ERP</div>
        </div>
    </div>
</div>
