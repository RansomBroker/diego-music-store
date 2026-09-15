<!-- Layout Setting Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-5 space-y-4">
    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Format Kertas & Label</h3>
    
    <!-- Preset Selector -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Preset Tata Letak Stiker</label>
        <select
            wire:model.live="paperLayout"
            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
        >
            <option value="3col">3 Kolom per Baris (Stiker 33 x 18 mm)</option>
            <option value="2col">2 Kolom per Baris (Stiker 40 x 22 mm)</option>
            <option value="1col">1 Kolom / Label Thermal (50 x 30 mm)</option>
            <option value="custom">⚙️ Manual / Custom Setting</option>
        </select>
    </div>

    <!-- Manual Dimensions Accordion / Grid -->
    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                <i class="ph-bold ph-sliders-horizontal text-primary"></i>
                Ukuran & Jarum Stiker (Manual)
            </span>
            @if ($paperLayout === 'custom')
                <span class="text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800">Mode Custom</span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Lebar Stiker (mm)</label>
                <input
                    type="number"
                    wire:model.live="labelWidth"
                    wire:change="touchCustom"
                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                >
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Tinggi Stiker (mm)</label>
                <input
                    type="number"
                    wire:model.live="labelHeight"
                    wire:change="touchCustom"
                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                >
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Kolom (1-5)</label>
                <input
                    type="number"
                    min="1"
                    max="5"
                    wire:model.live="columns"
                    wire:change="touchCustom"
                    class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                >
            </div>
            <div>
                <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Jarak X (mm)</label>
                <input
                    type="number"
                    wire:model.live="gapX"
                    wire:change="touchCustom"
                    class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                >
            </div>
            <div>
                <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Jarak Y (mm)</label>
                <input
                    type="number"
                    wire:model.live="gapY"
                    wire:change="touchCustom"
                    class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                >
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Ukuran Font (px)</label>
                <input
                    type="number"
                    min="7"
                    max="16"
                    wire:model.live="fontSize"
                    wire:change="touchCustom"
                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                >
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Tinggi Barcode (px)</label>
                <input
                    type="number"
                    min="20"
                    max="70"
                    wire:model.live="barcodeHeight"
                    wire:change="touchCustom"
                    class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                >
            </div>
        </div>
    </div>

    <!-- Content Toggles -->
    <div class="space-y-2 pt-3 border-t border-slate-200 dark:border-slate-800">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Informasi Ditampilkan</label>
        
        <div class="grid grid-cols-2 gap-2">
            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" wire:model.live="showStoreName" class="w-4 h-4 rounded text-primary">
                <span>Nama Toko</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" wire:model.live="showProductName" class="w-4 h-4 rounded text-primary">
                <span>Nama Produk</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" wire:model.live="showPrice" class="w-4 h-4 rounded text-primary">
                <span>Harga Jual</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" wire:model.live="showCode" class="w-4 h-4 rounded text-primary">
                <span>Kode SKU</span>
            </label>
        </div>
    </div>
</div>
