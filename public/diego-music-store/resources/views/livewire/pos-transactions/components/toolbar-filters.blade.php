<!-- Toolbar (Filters & Search) -->
<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3.5">
        
        <!-- Pencarian -->
        <div class="sm:col-span-2 lg:col-span-4 xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian Transaksi</label>
            <x-pos.form.input model="search" :live="true" placeholder="No. Invoice, pelanggan, kasir..." icon="ph-magnifying-glass" rounded="rounded-lg" />
        </div>

        <!-- Filter Cabang -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Filter Cabang</label>
            <x-pos.form.select model="selectedBranchId" :live="true" rounded="rounded-lg" icon="ph-storefront">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.select>
        </div>

        <!-- Filter Status -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Status Transaksi</label>
            <x-pos.form.select model="selectedStatus" :live="true" rounded="rounded-lg" icon="ph-squares-four">
                <option value="all">Semua Status</option>
                <option value="completed">Selesai</option>
                <option value="pending">Belum Selesai (Piutang)</option>
                <option value="draft">Draft</option>
                <option value="cancelled">Dibatalkan</option>
            </x-pos.form.select>
        </div>

        <!-- Filter Metode Bayar -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Metode Bayar</label>
            <x-pos.form.select model="selectedPaymentMethod" :live="true" rounded="rounded-lg" icon="ph-credit-card">
                <option value="all">Semua Metode</option>
                <option value="Tunai">Tunai / Cash</option>
                <option value="Debit">Debit BCA</option>
                <option value="Piutang">Piutang</option>
            </x-pos.form.select>
        </div>

        <!-- Dari Tanggal -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Dari Tanggal</label>
            <x-pos.form.input type="date" model="fromDate" :live="true" rounded="rounded-lg" />
        </div>

        <!-- Sampai Tanggal -->
        <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Sampai Tanggal</label>
            <x-pos.form.input type="date" model="toDate" :live="true" rounded="rounded-lg" />
        </div>

        <!-- Tombol Reset -->
        <div class="flex items-end">
            <x-pos.utility.button variant="danger" icon="ph-arrow-counter-clockwise" class="w-full" wire:click="resetFilters" title="Reset Filter">
                Reset
            </x-pos.utility.button>
        </div>

    </div>
</div>
