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

        <!-- Row 5: Metode Pembayaran -->
        <div class="space-y-3 relative" x-data="{ open: false }">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Metode Pembayaran
            </label>
            @php
                $paymentMethods = \App\Models\PaymentMethod::whereNull('parent_id')->with('children')->get();
                $activeMethod = $paymentMethods->firstWhere('code', $payment_method);
            @endphp
            
            <div class="relative">
                <!-- Dropdown Trigger -->
                <div 
                    @click="open = !open" 
                    @click.away="open = false"
                    class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-primary hover:ring-1 hover:ring-primary/50 transition-all"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        @if(!$activeMethod)
                            <span class="text-sm font-medium text-slate-400">Pilih Metode Pembayaran...</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary dark:bg-blue-950/40 dark:text-blue-400 rounded-full font-bold text-xs uppercase tracking-wider">
                                {{ $activeMethod->name }}
                            </span>
                        @endif
                    </div>
                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </div>

                <!-- Dropdown Options List -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                    class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg dark:shadow-slate-950/80 max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"
                    style="display: none;"
                >
                    @foreach ($paymentMethods as $pm)
                        @php
                            $iconClass = 'ph-credit-card';
                            $bgClass = 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-455';
                            if ($pm->code === 'cash') {
                                $iconClass = 'ph-money';
                                $bgClass = 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450';
                            } elseif (in_array($pm->code, ['credit', 'complimentary'])) {
                                $iconClass = 'ph-hand-coins';
                                $bgClass = 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-455';
                            }
                        @endphp

                        <button 
                            type="button" 
                            wire:click="$set('payment_method', '{{ $pm->code }}')" 
                            @click="open = false"
                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 {{ $bgClass }} rounded-lg flex items-center justify-center">
                                    <i class="ph-bold {{ $iconClass }} text-base"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-750 dark:text-slate-300">{{ $pm->name }}</span>
                                    @if (isset($pm->children) && count($pm->children) > 0)
                                        <span class="text-[10px] text-slate-400 font-medium">Sub-metode: {{ $pm->children->pluck('name')->implode(', ') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($payment_method === $pm->code)
                                <i class="ph-bold ph-check text-primary dark:text-blue-400 text-sm"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Sub Method Selection -->
            @if($activeMethod && count($activeMethod->children) > 0)
                <div class="mt-3 p-4 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-800 rounded-xl">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">
                        Pilih Sub-metode ({{ $activeMethod->name }})
                    </label>
                    <select wire:model="account_id" required class="w-full text-sm rounded-lg border-slate-200 focus:border-primary focus:ring-primary dark:bg-slate-800 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                        <option value="">-- Pilih Rekening / Kartu --</option>
                        @foreach($activeMethod->children as $child)
                            <option value="{{ $child->account_id }}">{{ $child->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
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
