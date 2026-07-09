@php
    $cols = "50px minmax(200px, 2fr) 130px 130px 160px 160px 200px";
    $minWidth = 1030;
@endphp

<style>
    /* Force the inner child-container of .sp-items-grid to layout horizontally as a grid */
    .sp-items-grid > div {
        display: grid !important;
        grid-template-columns: {{ $cols }} !important;
        gap: 1rem !important;
        align-items: center !important;
        width: 100% !important;
    }

    /* Style the repeater items inside our custom container to look like flat rows */
    .sp-items-table-container .fi-fo-repeater-item,
    .sp-items-table-container .fi-repeater-item {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 4px 0 !important;
        margin: 0 !important;
        border-bottom: 1px solid rgba(156, 163, 175, 0.15) !important;
        position: relative !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        padding-left: 0 !important;
        padding-right: 1rem !important;
        width: 100% !important;
        min-width: {{ $minWidth }}px !important;
    }

    /* Adjust padding of content within the repeater item */
    .sp-items-table-container .fi-fo-repeater-item-content,
    .sp-items-table-container .fi-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
        width: 100% !important;
    }

    /* Hide the delete button header and any repeater headers/actions */
    .sp-items-table-container .fi-fo-repeater-item-header,
    .sp-items-table-container .fi-repeater-item-header {
        display: none !important;
    }

    /* Sticky columns configuration for frozen first column ("Pilih") and second column ("No. Invoice / Transaksi") */
    .sp-items-header-row > :nth-child(1),
    .sp-items-grid > div > :nth-child(1) {
        position: sticky !important;
        left: 0 !important;
        z-index: 10 !important;
    }

    .sp-items-header-row > :nth-child(2),
    .sp-items-grid > div > :nth-child(2) {
        position: sticky !important;
        left: 66px !important; /* 50px (first col) + 16px (1rem gap) */
        z-index: 10 !important;
    }

    /* Set background colors and extend to cover grid gaps using pseudo-elements */
    .sp-items-header-row > :nth-child(1)::before,
    .sp-items-grid > div > :nth-child(1)::before {
        content: "" !important;
        position: absolute !important;
        top: -4px !important;
        bottom: -4px !important;
        left: -8px !important;
        right: -8px !important; /* Cover the 1rem (16px) gap */
        background-color: #ffffff !important;
        z-index: -1 !important;
    }

    .sp-items-header-row > :nth-child(2)::before,
    .sp-items-grid > div > :nth-child(2)::before {
        content: "" !important;
        position: absolute !important;
        top: -4px !important;
        bottom: -4px !important;
        left: -8px !important;
        right: -8px !important;
        background-color: #ffffff !important;
        z-index: -1 !important;
    }

    /* Dark mode background for sticky columns */
    .dark .sp-items-header-row > :nth-child(1)::before,
    .dark .sp-items-grid > div > :nth-child(1)::before,
    .dark .sp-items-header-row > :nth-child(2)::before,
    .dark .sp-items-grid > div > :nth-child(2)::before {
        background-color: #18181b !important;
    }
</style>

<div class="sp-items-header-row pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2" style="display: grid; grid-template-columns: {{ $cols }}; gap: 1rem; align-items: center; width: 100%; min-width: {{ $minWidth }}px; padding-left: 0; padding-right: 1rem; position: relative;">
    <div data-frozen-header style="text-align: center; position: relative; z-index: 25; will-change: transform;">Pilih</div>
    <div data-frozen-header-2 style="position: relative; z-index: 25; will-change: transform;">No. Invoice / Transaksi</div>
    <div>Tgl. Transaksi</div>
    <div>Jatuh Tempo</div>
    <div>Total Tagihan</div>
    <div>Sisa Hutang</div>
    <div>Jumlah Bayar</div>
</div>
