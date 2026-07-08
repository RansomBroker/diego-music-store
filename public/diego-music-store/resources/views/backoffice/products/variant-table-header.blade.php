@props([
    'pricingTiers' => []
])

@php
    // Column widths in rem (First column is 13.75rem = 220px)
    $cols = "13.75rem 10rem 10rem 8.75rem 8.75rem 6.875rem 8.75rem " . str_repeat('8.75rem ', count($pricingTiers));
    // 1210px (sum of columns) + 56px (padding & header) = 1266px
    $minWidth = 1210 + (140 * count($pricingTiers)) + 56;
@endphp

<style>
    /* Force the inner child-container of .variant-grid to layout horizontally as a grid */
    .variant-grid > div {
        display: grid !important;
        grid-template-columns: {{ $cols }} !important;
        gap: 1rem !important;
        align-items: center !important;
        width: 100% !important;
    }

    /* Style the repeater items inside our custom container to look like flat rows */
    .variant-table-container .fi-fo-repeater-item,
    .variant-table-container .fi-repeater-item {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 4px 0 !important;
        margin: 0 !important;
        border-bottom: 1px solid rgba(156, 163, 175, 0.15) !important;
        position: relative !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important; /* Stretch to fill height */
        padding-left: 0 !important; /* No left padding on row wrapper */
        padding-right: 1rem !important; /* Normal right padding */
        width: 100% !important;
        min-width: {{ $minWidth }}px !important;
    }

    /* Adjust padding of content within the repeater item */
    .variant-table-container .fi-fo-repeater-item-content,
    .variant-table-container .fi-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
        width: 100% !important;
    }

    /* Position the delete button header as a sticky element on the left */
    .variant-table-container .fi-fo-repeater-item-header,
    .variant-table-container .fi-repeater-item-header {
        position: sticky !important;
        left: 0 !important;
        align-self: stretch !important;
        width: 2.5rem !important; /* 40px width */
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 20 !important; /* Higher z-index to stay on top of inputs */
        background-color: #ffffff !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Dark mode delete button header background */
    .dark .variant-table-container .fi-fo-repeater-item-header,
    .dark .variant-table-container .fi-repeater-item-header {
        background-color: #18181b !important;
    }

    .variant-table-container .fi-fo-repeater-item-header-end-actions,
    .variant-table-container .fi-repeater-item-header-end-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    /* Sticky columns configuration for frozen first three columns */
    .variant-table-header-row > :nth-child(1),
    .variant-grid > div > :nth-child(1) {
        position: sticky !important;
        left: 2.5rem !important; /* Starts at left 2.5rem (40px) */
        z-index: 10 !important;
    }

    .variant-table-header-row > :nth-child(2),
    .variant-grid > div > :nth-child(2) {
        position: sticky !important;
        left: 17.25rem !important; /* 2.5rem + 13.75rem + 1rem gap = 17.25rem */
        z-index: 10 !important;
    }

    .variant-table-header-row > :nth-child(3),
    .variant-grid > div > :nth-child(3) {
        position: sticky !important;
        left: 28.25rem !important; /* 17.25rem + 10rem + 1rem gap = 28.25rem */
        z-index: 10 !important;
    }

    /* Set background colors and extend to cover grid gaps using pseudo-elements */
    .variant-table-header-row > :nth-child(1)::before,
    .variant-table-header-row > :nth-child(2)::before,
    .variant-table-header-row > :nth-child(3)::before,
    .variant-grid > div > :nth-child(1)::before,
    .variant-grid > div > :nth-child(2)::before,
    .variant-grid > div > :nth-child(3)::before {
        content: "" !important;
        position: absolute !important;
        top: -4px !important;
        bottom: -4px !important;
        left: -8px !important;
        right: -8px !important; /* Cover the 1rem (16px) gap (8px left + 8px right) */
        background-color: #ffffff !important;
        z-index: -1 !important;
    }

    /* Dark mode background for sticky columns */
    .dark .variant-table-header-row > :nth-child(1)::before,
    .dark .variant-table-header-row > :nth-child(2)::before,
    .dark .variant-table-header-row > :nth-child(3)::before,
    .dark .variant-grid > div > :nth-child(1)::before,
    .dark .variant-grid > div > :nth-child(2)::before,
    .dark .variant-grid > div > :nth-child(3)::before {
        background-color: #18181b !important;
    }

    /* Special mask for the header row's left padding area (0 to 2.5rem) */
    .variant-table-header-row > :nth-child(1)::after {
        content: "" !important;
        position: absolute !important;
        left: -2.5rem !important;
        width: 2.5rem !important;
        top: -4px !important;
        bottom: -4px !important;
        background-color: #ffffff !important;
        z-index: -1 !important;
    }
    .dark .variant-table-header-row > :nth-child(1)::after {
        background-color: #18181b !important;
    }
</style>

<div class="variant-table-header-row pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2" style="display: grid; grid-template-columns: {{ $cols }}; gap: 1rem; align-items: center; width: 100%; min-width: {{ $minWidth }}px; padding-left: 2.5rem; padding-right: 1rem; position: relative;">
    <div>Nama Varian</div>
    <div>SKU</div>
    <div>Barcode</div>
    <div>Harga Jual</div>
    <div>Harga Beli</div>
    <div>Est. Ongkir</div>
    <div>HPP</div>
    @foreach($pricingTiers as $tier)
        <div>Harga Tier: {{ $tier->name }}</div>
    @endforeach
</div>
