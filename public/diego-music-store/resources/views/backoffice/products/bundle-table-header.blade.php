@php
    $cols = "minmax(300px, 1fr) 120px";
    $minWidth = 420;
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
    .bundle-table-container .fi-fo-repeater-item {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 4px 0 !important;
        margin: 0 !important;
        border-bottom: 1px solid rgba(156, 163, 175, 0.15) !important;
        position: relative !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        padding-left: 2rem !important; /* Space for drag handle on left */
        padding-right: 2.5rem !important; /* Space for trash button on right */
        width: 100% !important;
        min-width: {{ $minWidth }}px !important;
    }

    /* Adjust padding of content within the repeater item */
    .bundle-table-container .fi-fo-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
    }

    /* Override header positioning so actions can be absolute positioned relative to item */
    .bundle-table-container .fi-fo-repeater-item-header {
        position: static !important;
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Position the drag handle on the left, centered vertically */
    .bundle-table-container .fi-fo-repeater-item-header-start-actions {
        position: absolute !important;
        left: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    /* Position the delete button on the right, centered vertically */
    .bundle-table-container .fi-fo-repeater-item-header-end-actions {
        position: absolute !important;
        right: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
</style>

<div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Daftar Item dalam Paket Bundling ini</div>

<div style="display: grid; grid-template-columns: {{ $cols }}; gap: 1rem; align-items: center; width: 100%; min-width: {{ $minWidth }}px; padding-left: 2rem; padding-right: 2.5rem;" class="pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2">
    <div>Pilih Produk Fisik/Jasa</div>
    <div>Jumlah Qty</div>
</div>
