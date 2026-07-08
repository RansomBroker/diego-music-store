@php
    $cols = "1fr 7.5rem";
    $minWidth = 600;
@endphp

<style>
    /* Force the inner child-container of .bundle-grid to layout horizontally as a grid */
    .bundle-grid > div {
        display: grid !important;
        grid-template-columns: {{ $cols }} !important;
        gap: 1rem !important;
        align-items: center !important;
        width: 100% !important;
    }

    /* Style the repeater items inside our custom container to look like flat rows */
    .bundle-table-container .fi-fo-repeater-item,
    .bundle-table-container .fi-repeater-item {
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
    .bundle-table-container .fi-fo-repeater-item-content,
    .bundle-table-container .fi-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
        width: 100% !important;
    }

    /* Position the delete button header as a sticky element on the left */
    .bundle-table-container .fi-fo-repeater-item-header,
    .bundle-table-container .fi-repeater-item-header {
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
    .dark .bundle-table-container .fi-fo-repeater-item-header,
    .dark .bundle-table-container .fi-repeater-item-header {
        background-color: #18181b !important;
    }

    .bundle-table-container .fi-fo-repeater-item-header-end-actions,
    .bundle-table-container .fi-repeater-item-header-end-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    /* Sticky columns configuration for frozen first column */
    .bundle-header-row > :nth-child(1),
    .bundle-grid > div > :nth-child(1) {
        position: sticky !important;
        left: 2.5rem !important; /* Starts at left 2.5rem (40px) */
        z-index: 10 !important;
    }

    /* Set background colors and extend to cover grid gaps using pseudo-elements */
    .bundle-header-row > :nth-child(1)::before,
    .bundle-grid > div > :nth-child(1)::before {
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
    .dark .bundle-header-row > :nth-child(1)::before,
    .dark .bundle-grid > div > :nth-child(1)::before {
        background-color: #18181b !important;
    }

    /* Special mask for the header row's left padding area (0 to 2.5rem) */
    .bundle-header-row > :nth-child(1)::after {
        content: "" !important;
        position: absolute !important;
        left: -2.5rem !important;
        width: 2.5rem !important;
        top: -4px !important;
        bottom: -4px !important;
        background-color: #ffffff !important;
        z-index: -1 !important;
    }
    .dark .bundle-header-row > :nth-child(1)::after {
        background-color: #18181b !important;
    }
</style>

<div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Daftar Item dalam Paket Bundling ini</div>

<div class="bundle-header-row pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2" style="display: grid; grid-template-columns: {{ $cols }}; gap: 1rem; align-items: center; width: 100%; min-width: {{ $minWidth }}px; padding-left: 2.5rem; padding-right: 1rem; position: relative;">
    <div data-frozen-header style="position: relative; z-index: 25; will-change: transform;">Pilih Produk Fisik/Jasa</div>
    <div>Jumlah Qty</div>
</div>
