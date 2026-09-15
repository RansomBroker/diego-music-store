<!-- Common Filter Toolbar Card (Expandable / Collapsible) -->
<div x-data="{ isExpanded: true }" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm transition-all duration-200 overflow-hidden">
    
    <!-- Filter Header Bar (Clickable to Collapse / Expand) -->
    <div @click="isExpanded = !isExpanded" class="p-4 px-4.5 flex items-center justify-between cursor-pointer select-none bg-slate-50/50 dark:bg-slate-800/20 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="ph ph-funnel text-base font-bold"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    Filter Laporan & Parameter
                </h3>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Klik untuk menyembunyikan / menampilkan opsi filter
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span x-show="!isExpanded" class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                Filter Sembunyi
            </span>
            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-200/50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                <i class="ph text-base transition-transform duration-200" :class="isExpanded ? 'ph-caret-up' : 'ph-caret-down'"></i>
            </button>
        </div>
    </div>

    <!-- Filter Form Content Body (Collapsible) -->
    <div x-show="isExpanded" x-collapse class="p-4.5 border-t border-slate-100 dark:border-slate-800 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Col 1: Mode Laporan -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Mode Laporan</label>
                <x-pos.form.select model="viewMode" :live="true" size="sm" icon="ph-squares-four">
                    <option value="detail">Detail Transaksi</option>
                    <option value="per_day">Per Hari</option>
                    <option value="per_nota">Per Nota</option>
                    <option value="top_selling">Terlaris</option>
                </x-pos.form.select>
            </div>

            <!-- Col 2: Periode Dari -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Dari Tanggal</label>
                <x-pos.form.input type="date" model="dateFrom" :live="true" size="sm" />
            </div>

            <!-- Col 3: Periode s/d -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Sampai Tanggal</label>
                <x-pos.form.input type="date" model="dateTo" :live="true" size="sm" />
            </div>

            <!-- Col 4: Cabang -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Cabang</label>
                <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 5: Pelanggan -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pelanggan</label>
                <x-pos.form.select model="selectedCustomerId" :live="true" size="sm" icon="ph-user">
                    <option value="">Semua Pelanggan</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 6: Jenis Pembayaran -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Jenis Pembayaran</label>
                <x-pos.form.select model="selectedPaymentMethod" :live="true" size="sm" icon="ph-credit-card">
                    <option value="">Semua Jenis Bayar</option>
                    @foreach ($paymentMethods as $pm)
                        <option value="{{ $pm }}">{{ $pm }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 7: Sales Rep -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Sales Representative</label>
                <x-pos.form.select model="selectedSalesRepId" :live="true" size="sm" icon="ph-identification-badge">
                    <option value="">Semua Sales</option>
                    @foreach ($salesUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 8: Kasir -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kasir / Operator</label>
                <x-pos.form.select model="selectedCashierId" :live="true" size="sm" icon="ph-user-gear">
                    <option value="">Semua Kasir</option>
                    @foreach ($cashierUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 9: Kategori Penjualan -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Penjualan</label>
                <x-pos.form.select model="selectedSaleCategory" :live="true" size="sm" icon="ph-tag">
                    <option value="">Semua Kat. Penjualan</option>
                    @foreach ($saleCategories as $sc)
                        <option value="{{ $sc }}">{{ $sc }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 10: Kategori Barang -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Barang</label>
                <x-pos.form.select model="selectedProductCategory" :live="true" size="sm" icon="ph-package">
                    <option value="">Semua Kat. Barang</option>
                    @foreach ($productCategories as $pc)
                        <option value="{{ $pc }}">{{ $pc }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 11: Filter Produk Spesifik -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Filter Produk</label>
                <x-pos.form.select model="selectedProductId" :live="true" size="sm" icon="ph-music-notes">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </x-pos.form.select>
            </div>

            <!-- Col 12: Pencarian / Search -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kata Kunci / Search</label>
                <x-pos.form.input model="search" :live="true" placeholder="Cari invoice/pelanggan/barang..." icon="ph-magnifying-glass" size="sm" />
            </div>

            <!-- Col 13: Actions (Reset) -->
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Reset</label>
                <x-pos.utility.button variant="danger" size="sm" icon="ph-arrow-counter-clockwise" class="w-full justify-center" wire:click="resetFilters">
                    Reset Filter
                </x-pos.utility.button>
            </div>
        </div>

        <!-- Presets Bar -->
        <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset Tanggal:</span>
                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('today')">Hari Ini</x-pos.utility.button>
                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_week')">Minggu Ini</x-pos.utility.button>
                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_month')">Bulan Ini</x-pos.utility.button>
                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_year')">Tahun Ini</x-pos.utility.button>
            </div>
        </div>
    </div>
</div>
