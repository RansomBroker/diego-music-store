<!-- Toolbar Filters -->
@php
    $typeFilterOptions = [
        ['value' => 'all', 'label' => 'Semua Tipe'],
        ['value' => 'in', 'label' => 'Kas Masuk'],
        ['value' => 'out', 'label' => 'Kas Keluar'],
        ['value' => 'transfer', 'label' => 'Setor Kas'],
    ];

    $accountFilterOptions = collect([
        ['value' => '', 'label' => 'Semua Akun', 'sublabel' => ''],
    ])->merge(
        collect($filterAccounts)->map(fn ($acc) => [
            'value' => (string) $acc->id,
            'label' => (string) $acc->name,
            'sublabel' => (string) $acc->code,
        ])
    )->values()->all();

    $sessionFilterOptions = collect();
    if ($activeSession) {
        $sessionFilterOptions->push([
            'value' => 'active',
            'label' => 'Sesi Aktif (#' . str_pad($activeSession->id, 5, '0', STR_PAD_LEFT) . ')',
            'sublabel' => 'OPEN',
        ]);
    }
    $sessionFilterOptions->push([
        'value' => 'all',
        'label' => 'Semua Sesi (Sesuai Tanggal)',
        'sublabel' => '',
    ]);
    foreach ($availableSessions as $session) {
        $sessionFilterOptions->push([
            'value' => (string) $session->id,
            'label' => '#' . str_pad($session->id, 5, '0', STR_PAD_LEFT) . ' — ' . ($session->opened_at?->format('d/m/y H:i') ?? '-'),
            'sublabel' => $session->status === 'open' ? 'Aktif' : 'Tutup',
        ]);
    }
    $sessionFilterOptions = $sessionFilterOptions->values()->all();

    $branchFilterOptions = collect($branches)->map(fn ($b) => [
        'value' => (string) $b->id,
        'label' => (string) $b->name,
        'sublabel' => '',
    ])->values()->all();
@endphp

<div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-12 gap-3.5">
        <!-- Pencarian -->
        <div class="sm:col-span-2 lg:col-span-2 xl:col-span-3">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Pencarian</label>
            <x-pos.form.input
                model="search"
                :live="true"
                placeholder="No. transaksi atau catatan..."
                icon="ph-magnifying-glass"
                rounded="rounded-lg"
            />
        </div>

        <!-- Filter Tipe -->
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Tipe Kas</label>
            <x-pos.form.searchable-select
                model="selectedType"
                :live="true"
                :options="$typeFilterOptions"
                icon="ph-arrows-left-right"
                placeholder="Semua Tipe"
                searchPlaceholder="Cari tipe kas..."
            />
        </div>

        <!-- Filter Kategori / Akun Kontra -->
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Kategori / Akun</label>
            <x-pos.form.searchable-select
                model="selectedAccountId"
                :live="true"
                :options="$accountFilterOptions"
                icon="ph-tag"
                placeholder="Semua Akun"
                searchPlaceholder="Cari nama / kode akun..."
            />
        </div>

        <!-- Filter Sesi Kasir -->
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Sesi Kasir</label>
            <x-pos.form.searchable-select
                model="selectedSessionId"
                :live="true"
                :options="$sessionFilterOptions"
                icon="ph-clock-counter-clockwise"
                placeholder="Pilih Sesi"
                searchPlaceholder="Cari nomor / tanggal sesi..."
            />
        </div>

        @if(count($branches) > 1)
            <!-- Filter Cabang -->
            <div class="xl:col-span-3">
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Cabang</label>
                <x-pos.form.searchable-select
                    model="selectedBranchId"
                    :live="true"
                    :options="$branchFilterOptions"
                    icon="ph-storefront"
                    placeholder="Pilih Cabang"
                    searchPlaceholder="Cari cabang..."
                />
            </div>
        @endif

        <!-- Dari Tanggal -->
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Dari Tanggal</label>
            <x-pos.form.input type="date" model="fromDate" :live="true" rounded="rounded-lg" />
        </div>

        <!-- Sampai Tanggal -->
        <div class="xl:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 block">Sampai Tanggal</label>
            <x-pos.form.input type="date" model="toDate" :live="true" rounded="rounded-lg" />
        </div>

        <!-- Tombol Reset -->
        <div class="flex items-end xl:col-span-2">
            <x-pos.utility.button
                type="button"
                variant="danger"
                size="sm"
                icon="ph-arrow-counter-clockwise"
                class="w-full"
                wire:click="resetFilters"
                title="Reset Filter"
            >
                Reset
            </x-pos.utility.button>
        </div>
    </div>
</div>
