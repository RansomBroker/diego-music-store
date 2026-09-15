<!-- Dynamic Live Barcode Sticker Preview Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-5 space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pratinjau Stiker (Dynamic)</h3>
        <span class="text-[10px] text-slate-400 font-mono">{{ $labelWidth }}x{{ $labelHeight }}mm | {{ $columns }} Col</span>
    </div>
    
    <div
        class="bg-white border-2 border-slate-900 rounded-lg text-slate-900 text-center mx-auto space-y-1 shadow-md overflow-hidden transition-all duration-200"
        style="width: {{ min($labelWidth * 4, 260) }}px; padding: 6px;"
    >
        @if ($showStoreName)
            <div class="font-bold uppercase tracking-wider truncate" style="font-size: {{ max($fontSize - 2, 7) }}px;">Diego Music Store</div>
        @endif
        @if ($showProductName)
            <div class="font-bold truncate" style="font-size: {{ $fontSize }}px;">Gitar Yamaha F310</div>
        @endif
        
        <div class="w-full flex items-center justify-center my-1" style="height: {{ $barcodeHeight }}px;">
            {!! \App\Helpers\BarcodeHelper::generateCode128Svg('SKU-10023', 180, $barcodeHeight) !!}
        </div>

        @if ($showCode)
            <div class="font-mono tracking-widest" style="font-size: {{ max($fontSize - 2, 7) }}px;">SKU-10023</div>
        @endif
        @if ($showPrice)
            <div class="font-extrabold border-t border-slate-300 pt-0.5" style="font-size: {{ $fontSize }}px;">Rp 1.750.000</div>
        @endif
    </div>
</div>
