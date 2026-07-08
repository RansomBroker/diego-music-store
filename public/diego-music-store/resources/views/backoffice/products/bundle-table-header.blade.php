<style>
    /* Force the inner child-container of .bundle-grid to layout horizontally as a grid */
    .bundle-grid > div {
        display: grid !important;
        grid-template-columns: 1fr 7.5rem !important; /* 1fr stretches to fill available space, 7.5rem (120px) is fixed for Qty */
        gap: 1rem !important;
        align-items: center !important;
        width: 100% !important;
    }

    /* Style the repeater items inside our custom container to look like flat rows spanning 100% width */
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
        align-items: center !important;
        padding-left: 0 !important; /* No left padding on row wrapper */
        padding-right: 0 !important; /* No right padding on row wrapper */
        width: 100% !important;
    }

    /* Adjust padding of content within the repeater item */
    .bundle-table-container .fi-fo-repeater-item-content,
    .bundle-table-container .fi-repeater-item-content {
        padding: 0 !important;
        flex-grow: 1 !important;
    }

    /* Override header positioning to place it on the left */
    .bundle-table-container .fi-fo-repeater-item-header,
    .bundle-table-container .fi-repeater-item-header {
        position: static !important;
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 2.5rem !important; /* 40px width */
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
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
</style>

<div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Daftar Item dalam Paket Bundling ini</div>

<div style="display: grid; grid-template-columns: 1fr 7.5rem; gap: 1rem; align-items: center; width: 100%; padding-left: 2.5rem; padding-right: 0;" class="pb-2 font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 mb-2">
    <div>Pilih Produk Fisik/Jasa</div>
    <div>Jumlah Qty</div>
</div>
