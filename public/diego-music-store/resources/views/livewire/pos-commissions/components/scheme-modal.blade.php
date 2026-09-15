<!-- Modal Form Skema Komisi -->
<x-pos.modal
    wire:model="showSchemeModal"
    title="{{ $editingSchemeId ? 'Edit Skema Komisi' : 'Tambah Skema Komisi Baru' }}"
    subtitle="Atur kriteria kalkulasi komisi sales untuk cabang dan produk/kategori"
    icon="ph-percent"
    maxWidth="md"
>
    <form wire:submit.prevent="saveScheme" class="space-y-4">
        <!-- Nama Skema -->
        <x-pos.form.input
            label="Nama Skema Komisi"
            model="schemeName"
            :required="true"
            placeholder="Contoh: Komisi Sales Senior 3.5%"
            icon="ph-tag"
        />

        <!-- Target Staf Karyawan Spesifik (Opsional - Multiple Choice) -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Target Staf Sales / Karyawan Spesifik
            </label>
            <div class="max-h-36 overflow-y-auto p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl space-y-1.5 no-scrollbar">
                @php
                    $availableCount = 0;
                @endphp
                @foreach ($employees as $emp)
                    @php
                        $isUnavailable = in_array($emp->id, $unavailableEmployeeIds);
                    @endphp
                    @if (!$isUnavailable)
                        @php $availableCount++; @endphp
                        <label class="flex items-center gap-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 cursor-pointer p-1.5 rounded-lg hover:bg-slate-200/50 dark:hover:bg-slate-800 transition">
                            <input
                                type="checkbox"
                                value="{{ $emp->id }}"
                                wire:model="targetEmployeeIds"
                                class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4 cursor-pointer"
                            />
                            <span>{{ $emp->name }}</span>
                            <span class="text-[10px] font-mono text-slate-400">({{ $emp->nik }})</span>
                        </label>
                    @endif
                @endforeach

                @if ($availableCount === 0)
                    <div class="text-[11px] text-slate-400 p-2 text-center">
                        Semua karyawan sudah terdaftar pada skema komisi lain (atau belum ada data karyawan).
                    </div>
                @endif
            </div>
            <p class="text-[10px] text-slate-400 mt-1">* Karyawan yang sudah terdaftar pada skema komisi khusus lain secara otomatis disembunyikan.</p>
        </div>

        <!-- Tipe Kalkulasi & Rate -->
        <div class="grid grid-cols-2 gap-3">
            <x-pos.form.dropdown
                label="Tipe Kalkulasi"
                model="calculationType"
                :live="true"
                :required="true"
                icon="ph-calculator"
            >
                <option value="percentage">Persentase (%)</option>
                <option value="fixed_amount">Nominal Flat (Rp)</option>
            </x-pos.form.dropdown>

            @if ($calculationType === 'percentage')
                <x-pos.form.input
                    label="Tarif Komisi (%)"
                    type="number"
                    step="0.01"
                    model="rate"
                    :required="true"
                    placeholder="2.0"
                    icon="ph-percent"
                />
            @else
                <x-pos.form.input
                    label="Nominal Komisi (Rp)"
                    type="currency"
                    model="rate"
                    :required="true"
                    placeholder="25.000"
                    icon="ph-money"
                />
            @endif
        </div>

        <!-- Target Penerapan -->
        <x-pos.form.dropdown
            label="Target Penerapan"
            model="appliesTo"
            :live="true"
            :required="true"
            icon="ph-target"
        >
            <option value="all_sales">Semua Transaksi Sales</option>
            <option value="category">Spesifik Kategori Penjualan</option>
            <option value="product">Spesifik Produk Tertentu</option>
        </x-pos.form.dropdown>

        @if ($appliesTo === 'category')
            <x-pos.form.dropdown
                label="Pilih Kategori Penjualan"
                model="targetSaleCategoryId"
                :required="true"
                icon="ph-folder"
                placeholder="-- Pilih Kategori --"
            >
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </x-pos.form.dropdown>
        @endif

        @if ($appliesTo === 'product')
            <x-pos.form.dropdown
                label="Pilih Produk Spesifik"
                model="targetProductId"
                :required="true"
                icon="ph-package"
                placeholder="-- Pilih Produk --"
            >
                @foreach ($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                @endforeach
            </x-pos.form.dropdown>
        @endif

        <!-- Minimal Target Omset Bulanan -->
        <x-pos.form.input
            label="Target Minimal Omset Bulanan (Opsional)"
            type="currency"
            model="minMonthlySalesTarget"
            placeholder="0"
            icon="ph-chart-line-up"
        />

        <!-- Status Aktif Toggle -->
        <x-pos.form.toggle
            label="Aktifkan Skema Komisi Ini"
            sublabel="Skema yang aktif akan dihitung otomatis saat transaksi penjualan"
            model="isActive"
        />

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                size="md"
                wire:click="$set('showSchemeModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                variant="primary"
                size="md"
                icon="ph-check"
            >
                Simpan Skema
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
