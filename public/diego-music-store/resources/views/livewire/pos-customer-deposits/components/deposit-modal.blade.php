<!-- MODAL 1: FORM TAMBAH / EDIT DEPOSIT -->
<x-pos.modal
    wire:model="showFormModal"
    :title="$isEditMode ? 'Edit Deposit Pelanggan' : 'Tambah Deposit Pelanggan Baru'"
    subtitle="Uang muka otomatis masuk ke akun COA Penitipan Dana (Liabilitas)"
    icon="ph-hand-coins"
    maxWidth="2xl"
>
    <form wire:submit="saveDeposit" class="space-y-5">

        <!-- Row 1: Pilih Pelanggan & Tanggal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Searchable Customer Dropdown Component -->
            <x-pos.form.dropdown-searchable
                label="Pelanggan"
                required
                searchModel="customer_search"
                openModel="showCustomerDropdown"
                placeholder="Ketik nama atau no HP pelanggan..."
                :items="$searchedCustomers"
                :selectedId="$customer_id"
                :selectedName="$customer_name"
                selectAction="selectCustomer"
                clearAction="clearSelectedCustomer"
                errorField="customer_id"
                selectedLabel="Pelanggan terpilih"
                changeLabel="Ganti Pelanggan"
                icon="ph-user"
                :minChars="0"
            />

            <x-pos.form.input
                label="Tanggal Deposit"
                type="date"
                model="deposit_date"
                required
            />
        </div>

        <!-- Row 2: Tipe Produk (Katalog vs Manual PO) -->
        <x-pos.form.dropdown
            label="Pilihan Jenis Produk"
            model="product_type"
            :live="true"
            required
            icon="ph-package"
        >
            <option value="existing">Produk yang Sudah Ada (Katalog)</option>
            <option value="manual">Belum Ada / Manual (PO / Inden)</option>
        </x-pos.form.dropdown>

        <!-- Row 3: Detail Produk -->
        @if ($product_type === 'existing')
            <!-- Searchable Catalog Dropdown Component -->
            <x-pos.form.dropdown-searchable
                label="Cari & Pilih Produk Katalog"
                required
                searchModel="product_search"
                openModel="showProductDropdown"
                placeholder="Ketik nama produk untuk mencari katalog..."
                :items="$catalogProducts"
                :selectedId="$product_id"
                :selectedName="$product_name"
                selectAction="selectProduct"
                clearAction="clearSelectedProduct"
                errorField="product_name"
                selectedLabel="Produk terpilih"
                changeLabel="Ganti Produk"
                :minChars="0"
            />
        @else
            <!-- Manual Product Input (PO Inden) -->
            <x-pos.form.input
                label="Nama Barang / Pesanan PO Manual"
                model="product_name"
                placeholder="Contoh: Fender Stratocaster Custom Shop 1960 (PO Inden)"
                required
            />
        @endif

        <!-- Row 4: Harga Satuan & Qty -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-pos.form.input
                label="Harga Satuan"
                type="currency"
                model="price"
                :live="true"
                required
            />

            <x-pos.form.input
                label="Jumlah (Qty)"
                type="number"
                min="1"
                model="qty"
                :live="true"
                required
            />
        </div>

        <!-- Calculation Preview Card: Total vs Deposit vs Sisa -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between text-xs sm:text-sm font-extrabold text-slate-700 dark:text-slate-300">
                <span>Total Harga Pesanan:</span>
                <span class="text-base text-slate-900 dark:text-white ">
                    Rp {{ number_format($total_amount, 0, ',', '.') }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-200 dark:border-slate-700">
                <x-pos.form.input
                    label="Nilai Deposit / Uang Muka"
                    type="currency"
                    model="deposit_amount"
                    :live="true"
                    required
                    class="border-2 !border-primary dark:!border-blue-500"
                />

                <div class="flex flex-col justify-center">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Sisa Tagihan / Pelunasan:
                    </span>
                    <span class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1 ">
                        Rp {{ number_format($remaining_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Row 6: Catatan / Referensi -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-pos.form.input
                label="No. Referensi Transfer / Bukti"
                model="payment_reference"
                placeholder="Misal: TRF-BCA-98762"
            />

            <x-pos.form.input
                label="Catatan Tambahan (Opsional)"
                model="notes"
                placeholder="Estimasi barang datang, spesifikasi khusus, dll."
            />
        </div>

        <!-- Modal Actions Footer -->
        <div class="pt-5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showFormModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                variant="primary"
                icon="ph-floppy-disk"
            >
                {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Deposit' }}
            </x-pos.utility.button>
        </div>

    </form>
</x-pos.modal>
