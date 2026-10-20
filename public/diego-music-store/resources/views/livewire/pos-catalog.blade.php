<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900">
    <x-pos.toast />

    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <x-pos.navbar pageTitle="Katalog Produk" backLabel="Dashboard" />

        <div class="flex-1 overflow-y-auto p-4 md:p-6">
            <div class="mx-auto max-w-7xl space-y-5">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white">Katalog Produk</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Informasi produk dan stok cabang {{ $branch->name }}. Halaman ini hanya untuk melihat data.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 md:grid-cols-[minmax(0,1fr)_240px_120px]">
                    <label class="block">
                        <span class="mb-1 block text-xs font-semibold text-slate-500">Cari produk, SKU, barcode, merek</span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Ketik nama produk atau SKU..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-semibold text-slate-500">Kategori</span>
                        <select wire:model.live="category" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-semibold text-slate-500">Baris per halaman</span>
                        <select wire:model.live="perPage" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-100 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-4 py-3">Varian / SKU</th>
                                    <th class="px-4 py-3 text-right">Harga</th>
                                    <th class="px-4 py-3 text-right">Stok Cabang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($products as $product)
                                    @foreach ($product->variants as $variant)
                                        <tr wire:key="catalog-variant-{{ $variant->id }}" class="align-top">
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ $product->brand ?: 'Tanpa merek' }} · {{ $product->category ?: 'Tanpa kategori' }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-slate-800 dark:text-slate-200">{{ $variant->name ?: 'Default' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">SKU: {{ $variant->sku ?: '—' }}</div>
                                                @if ($variant->barcode)
                                                    <div class="text-xs text-slate-500">Barcode: {{ $variant->barcode }}</div>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-900 dark:text-white">
                                                Rp {{ number_format($variant->priceForBranch($branch->id), 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                @if ($product->isService())
                                                    <span class="text-slate-500">Layanan</span>
                                                @else
                                                    @php($stock = $variant->stockForBranch($branch->id))
                                                    <span class="{{ $stock <= 0 ? 'font-semibold text-rose-600' : 'text-slate-800 dark:text-slate-200' }}">{{ number_format($stock, 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center text-slate-500">Produk tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
