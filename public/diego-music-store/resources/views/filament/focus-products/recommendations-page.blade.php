<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Banner Info Rekomendasi -->
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/60 flex items-start space-x-3">
            <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg text-amber-700 dark:text-amber-300">
                <x-heroicon-o-light-bulb class="w-6 h-6" />
            </div>
            <div>
                <h4 class="font-semibold text-amber-900 dark:text-amber-200 text-sm">Rekomendasi Produk Fokus dari Sistem</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300/90 mt-0.5">
                    Daftar di bawah ini merupakan produk yang terdeteksi membutuhkan perhatian khusus berdasarkan analisis aturan <strong>Dead Stock</strong>, <strong>Slow Moving</strong>, dan <strong>Aging Stock</strong> per cabang.
                    Pilih produk satu per satu atau gunakan pilihan centang untuk menambahkan beberapa produk sekaligus ke dalam Produk Fokus.
                </p>
            </div>
        </div>

        <!-- Tabel Rekomendasi Filament -->
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
