<div class="p-4 rounded-lg bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="grid grid-cols-2 gap-4 max-w-md ml-auto">
        <div class="text-sm text-gray-500 dark:text-gray-400">Total Qty (Satuan Pilihan):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            {{ $physicalQtyString }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Total Qty (Satuan Terkecil):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            {{ $smallestQtyString }}
        </div>

        <div class="col-span-2 border-t border-gray-200 dark:border-gray-600 my-1"></div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Total Sebelum Pajak (Subtotal):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($totalAmount, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Total Pajak (PPN):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($taxAmount, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Diskon Global:</div>
        <div class="text-sm font-semibold text-right text-red-500">
            - Rp {{ number_format($discountAmount, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Biaya Kirim (Ongkir):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($otherCost, 0, ',', '.') }}
            @if(($shippingBorneBy ?? 'self_direct') === 'third_party')
                <span class="text-xs text-gray-400 block font-normal">(Pihak Ke-3 - Tidak Ditagih Supplier)</span>
            @elseif(($shippingBorneBy ?? 'self_direct') === 'supplier')
                <span class="text-xs text-green-500 block font-normal">(Ditanggung Supplier)</span>
            @else
                <span class="text-xs text-gray-400 block font-normal">(Ditagih Supplier)</span>
            @endif
        </div>

        <div class="col-span-2 border-t border-gray-200 dark:border-gray-600 my-1"></div>

        <div class="text-base font-bold text-gray-800 dark:text-white">Grand Total:</div>
        <div class="text-lg font-extrabold text-right text-primary-600 dark:text-primary-400">
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
        </div>
    </div>
</div>
