<div class="p-4 rounded-lg bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="grid grid-cols-2 gap-4 max-w-md ml-auto">
        <div class="text-sm text-gray-500 dark:text-gray-400">Total Hutang (Outstanding):</div>
        <div class="text-sm font-semibold text-right text-gray-800 dark:text-gray-200">
            Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">Total Pelunasan:</div>
        <div class="text-sm font-semibold text-right text-green-600 dark:text-green-400">
            Rp {{ number_format($totalPayment, 0, ',', '.') }}
        </div>

        <div class="col-span-2 border-t border-gray-200 dark:border-gray-600 my-1"></div>

        <div class="text-base font-bold text-gray-800 dark:text-white">Sisa Hutang Akhir:</div>
        <div class="text-lg font-extrabold text-right text-primary-600 dark:text-primary-400">
            Rp {{ number_format(max(0, $totalOutstanding - $totalPayment), 0, ',', '.') }}
        </div>
    </div>
</div>
