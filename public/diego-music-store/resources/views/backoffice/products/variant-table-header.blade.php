@props([
    'pricingTiers' => []
])

@php
    $cols = "minmax(200px, 1fr) 150px 150px 130px 130px 100px 130px " . str_repeat('130px ', count($pricingTiers));
    $minWidth = 990 + (130 * count($pricingTiers));
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
    .variant-table-container .fi-fo-repeater-item {
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
    .variant-table-container .fi-fo-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
        width: 100% !important;
    }

    /* Override header positioning so actions can be absolute positioned relative to item */
    .variant-table-container .fi-fo-repeater-item-header {
        position: static !important;
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Position the drag handle on the left, centered vertically */
    .variant-table-container .fi-fo-repeater-item-header-start-actions {
        position: absolute !important;
        left: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    /* Position the delete button on the right, centered vertically */
    .variant-table-container .fi-fo-repeater-item-header-end-actions {
        position: absolute !important;
        right: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
</style>

<div style="display: grid; grid-template-columns: {{ $cols }}; gap: 1rem; align-items: center; width: 100%; min-width: {{ $minWidth }}px; padding-left: 2rem; padding-right: 2.5rem;" class="pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2">
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
