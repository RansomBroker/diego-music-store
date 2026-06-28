<div class="p-4 rounded-lg bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="grid grid-cols-2 gap-4 max-w-md ml-auto">
        <div class="text-sm text-gray-500 dark:text-gray-400">Subtotal Sebelum Pajak & Diskon:</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($subtotal, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Total Pajak PPN:</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($taxAmount, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Diskon Global (Header):</div>
        <div class="text-sm font-semibold text-right text-red-500">
            - Rp {{ number_format($discount, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Biaya Kirim (Ongkir):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($shippingCost, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Biaya Lain-Lain:</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($otherCost, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Potongan PPh:</div>
        <div class="text-sm font-semibold text-right text-red-500">
            - Rp {{ number_format($pphAmount, 0, ',', '.') }}
        </div>

        <div class="col-span-2 border-t border-gray-200 dark:border-gray-600 my-1"></div>

        <div class="text-base font-bold text-gray-800 dark:text-white">Grand Total:</div>
        <div class="text-lg font-extrabold text-right text-primary-600 dark:text-primary-400">
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
        </div>
    </div>
</div>
