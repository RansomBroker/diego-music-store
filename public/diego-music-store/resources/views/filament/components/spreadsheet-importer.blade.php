@php
    $title = match($type) {
        'customer'      => 'Pelanggan',
        'supplier'      => 'Supplier',
        'supplier_debt' => 'Saldo Awal Faktur Hutang Supplier',
        'product'       => 'Produk & Stok Awal',
        default         => ucfirst($type),
    };
    $csvUrl = asset(match($type) {
        'customer'      => 'templates/template_import_pelanggan.csv',
        'supplier'      => 'templates/template_import_supplier.csv',
        'supplier_debt' => 'templates/template_import_hutang_supplier.csv',
        'product'       => 'templates/template_import_produk.csv',
        default         => '',
    });
    $xlsxUrl = asset(match($type) {
        'customer'      => 'templates/template_import_pelanggan.xlsx',
        'supplier'      => 'templates/template_import_supplier.xlsx',
        'supplier_debt' => 'templates/template_import_hutang_supplier.xlsx',
        'product'       => 'templates/template_import_produk.xlsx',
        default         => '',
    });
@endphp

<div class="space-y-5 text-gray-900 dark:text-gray-100">
    <!-- Download Template Header -->
    <div class="p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/70 dark:bg-blue-950/20 backdrop-blur-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Template Resmi Import {{ $title }}
                </h4>
                <p class="text-xs text-blue-700 dark:text-blue-400/80 mt-0.5">
                    Gunakan template berikut agar susunan kolom terdeteksi otomatis dan valid saat diimpor.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $xlsxUrl }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Excel (.xlsx)
                </a>
                <a href="{{ $csvUrl }}" download class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh CSV (.csv)
                </a>
            </div>
        </div>
    </div>

    @if($type === 'product' && !$isImporting && !$importFinished)
        <!-- Product Import Configuration (Branch & Double-Entry Accounting Setup) -->
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-white/5 space-y-3.5 shadow-xs">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-2.5">
                <div>
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Konfigurasi Alokasi Stok & Jurnal Saldo Awal
                    </h5>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        Saldo awal stok (Qty × HPP) akan dialokasikan ke cabang dan dibukukan ke jurnal akuntansi berpasangan.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- 1. Branch Selector -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Cabang Alokasi Stok
                    </label>
                    <select wire:model.live="branchId" class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                        @foreach(\App\Models\Branch::where('is_active', true)->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Inventory Account [DEBIT] -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                        <span>Akun Persediaan [DEBIT]</span>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Aset (+)</span>
                    </label>
                    <input type="text" 
                           value="111401001 - PERSEDIAAN BARANG DAGANG" 
                           readonly 
                           class="w-full text-xs font-semibold rounded-lg border-emerald-300 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-300 cursor-not-allowed shadow-xs" />
                </div>

                <!-- 3. Contra Account [KREDIT] -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                        <span>Akun Penyeimbang [KREDIT]</span>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Ekuitas / Modal</span>
                    </label>
                    <select wire:model.live="contraAccountId" class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                        @php
                            $modalAcc = \App\Helpers\AccountHelper::findByCode('311101001');
                            $retainedAcc = \App\Helpers\AccountHelper::findByCode('311201001');
                            $invAcc = \App\Helpers\AccountHelper::findByCode('111401001');
                        @endphp
                        @if($modalAcc)
                            <option value="{{ $modalAcc->id }}">311101001 - MODAL DISETOR (Default Ekuitas)</option>
                        @endif
                        @if($retainedAcc)
                            <option value="{{ $retainedAcc->id }}">311201001 - LABA DITAHAN</option>
                        @endif
                        @if($invAcc)
                            <option value="{{ $invAcc->id }}">111401001 - PERSEDIAAN BARANG DAGANG</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Double-entry Journal Preview Box -->
            @php
                $selectedContra = \App\Models\Account::find($contraAccountId);
                $isSameAccount = $selectedContra && $selectedContra->code === '111401001';
            @endphp
            <div class="p-3 rounded-lg border {{ $isSameAccount ? 'border-amber-300 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-950/30' : 'border-blue-100 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-950/20' }} text-[11px] space-y-1">
                <div class="font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 {{ $isSameAccount ? 'text-amber-600' : 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simulasi Jurnal Otomatis yang Terbentuk:
                    </span>
                    <span class="font-mono text-[10px] text-gray-500">Status: POSTED</span>
                </div>
                <div class="font-mono space-y-0.5 text-gray-700 dark:text-gray-300 pl-5">
                    <div>[D] <strong>111401001 - PERSEDIAAN BARANG DAGANG</strong> <span class="text-emerald-600 dark:text-emerald-400">(Aset Persediaan Bertambah)</span></div>
                    <div>[K] <strong>{{ $selectedContra ? ($selectedContra->code . ' - ' . $selectedContra->name) : '311101001 - MODAL DISETOR' }}</strong> <span class="{{ $isSameAccount ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-blue-600 dark:text-blue-400' }}">{{ $isSameAccount ? '(Perhatian: Debit dan Kredit di akun yang sama, saldo akhir di neraca menjadi Rp 0)' : '(Penyeimbang Modal/Ekuitas)' }}</span></div>
                </div>
            </div>
        </div>
    @endif

    @if($type === 'supplier_debt' && !$isImporting && !$importFinished)
        <!-- Supplier Debt Import Configuration (Branch & Double-Entry AP Accounting Setup) -->
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-white/5 space-y-3.5 shadow-xs">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-2.5">
                <div>
                    <h5 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Konfigurasi Cabang & Jurnal Saldo Awal Hutang Supplier
                    </h5>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        Setiap nota/faktur akan dicatat sebagai faktur pembelian kredit (posted) dan dibukukan ke jurnal akuntansi berpasangan (Opsi 1).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- 1. Branch Selector -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Cabang Alokasi Hutang
                    </label>
                    <select wire:model.live="branchId" class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                        @foreach(\App\Models\Branch::where('is_active', true)->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Accounts Payable Account [KREDIT] -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                        <span>Akun Hutang Dagang [KREDIT]</span>
                        <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase">Kewajiban (+)</span>
                    </label>
                    <input type="text" 
                           value="211101001 - HUTANG DAGANG" 
                           readonly 
                           class="w-full text-xs font-semibold rounded-lg border-rose-300 dark:border-rose-800 bg-rose-50/70 dark:bg-rose-950/30 text-rose-900 dark:text-rose-300 cursor-not-allowed shadow-xs" />
                </div>

                <!-- 3. Contra Account [DEBIT] -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center justify-between">
                        <span>Akun Penyeimbang [DEBIT]</span>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Ekuitas (-)</span>
                    </label>
                    <select wire:model.live="contraAccountId" class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                        @php
                            $modalAcc = \App\Helpers\AccountHelper::findByCode('311101001');
                            $retainedAcc = \App\Helpers\AccountHelper::findByCode('311201001');
                        @endphp
                        @if($modalAcc)
                            <option value="{{ $modalAcc->id }}">311101001 - MODAL DISETOR (Default Ekuitas)</option>
                        @endif
                        @if($retainedAcc)
                            <option value="{{ $retainedAcc->id }}">311201001 - LABA DITAHAN</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Double-entry Journal Preview Box -->
            @php
                $selectedContra = \App\Models\Account::find($contraAccountId);
            @endphp
            <div class="p-3 rounded-lg border border-blue-100 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-950/20 text-[11px] space-y-1">
                <div class="font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simulasi Jurnal Otomatis yang Terbentuk (Opsi 1):
                    </span>
                    <span class="font-mono text-[10px] text-gray-500">Status: POSTED</span>
                </div>
                <div class="font-mono space-y-0.5 text-gray-700 dark:text-gray-300 pl-5">
                    <div>[D] <strong>{{ $selectedContra ? ($selectedContra->code . ' - ' . $selectedContra->name) : '311101001 - MODAL DISETOR' }}</strong> <span class="text-blue-600 dark:text-blue-400">(Penyeimbang Ekuitas Saldo Awal)</span></div>
                    <div>[K] <strong>211101001 - HUTANG DAGANG</strong> <span class="text-rose-600 dark:text-rose-400">(Kewajiban Hutang Supplier Bertambah)</span></div>
                </div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 pt-1">
                    * Sistem secara otomatis meneruskan nama supplier untuk baris beruntun (merged cells) dan mengabaikan baris subtotal "Total :".
                </p>
            </div>
        </div>
    @endif


    @if(!$filePath && !$importFinished)
        <!-- File Upload Area -->
        <div class="relative">
            <label class="group relative flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-8 hover:border-blue-500 dark:hover:border-blue-500 bg-gray-50/50 dark:bg-gray-800/20 hover:bg-gray-50 dark:hover:bg-gray-800/40 cursor-pointer transition">
                <input type="file" wire:model="file" accept=".csv,.xlsx,.xls,.txt" class="sr-only" />
                
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full group-hover:scale-110 transition duration-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Pilih berkas Excel atau CSV</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400"> atau seret ke area ini</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Format didukung: <span class="font-semibold text-gray-700 dark:text-gray-300">.xlsx, .xls, .csv</span> (Maksimal 20 MB)
                    </p>
                </div>
            </label>

            <!-- Loading indicator for Livewire upload -->
            <div wire:loading wire:target="file" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xs flex flex-col items-center justify-center rounded-2xl z-10">
                <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-2">Membaca berkas...</span>
            </div>

            @error('file')
                <p class="text-xs text-rose-600 dark:text-rose-400 mt-2 flex items-center gap-1 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="pt-2 flex items-center justify-end border-t border-gray-100 dark:border-gray-800">
            <button type="button" 
                    x-on:click="$dispatch('close-modal', { id: $el.closest('[data-fi-modal-id]')?.getAttribute('data-fi-modal-id') || 'fi-modal' })" 
                    class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 transition cursor-pointer">
                Tutup
            </button>
        </div>
    @endif

    @if($filePath && !$importFinished)
        <!-- File Details & Action Header -->
        <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 truncate">
                <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="truncate">
                    <h5 class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $originalFileName }}</h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalRows }} baris data terdeteksi</p>
                </div>
            </div>
            @if(!$isImporting)
                <button type="button" wire:click="resetAll" class="px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition">
                    Ganti Berkas
                </button>
            @endif
        </div>

        <!-- Multi-Sheet Selector -->
        @if(count($sheets) > 1)
            <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Pilih Lembar Kerja (Sheet)
                    </span>
                    <span class="text-xs text-amber-700 dark:text-amber-400/80">{{ count($sheets) }} sheet terdeteksi</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($sheets as $sheet)
                        <button type="button" 
                                wire:click="selectSheet('{{ addslashes($sheet) }}')" 
                                @disabled($isImporting)
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition flex items-center gap-1.5 {{ $selectedSheet === $sheet 
                                    ? 'bg-amber-600 text-white shadow-sm ring-2 ring-amber-500/50' 
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40' }}">
                            @if($selectedSheet === $sheet)
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                            {{ $sheet }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Column Detection & Validation Status Card -->
        @if($validation['is_valid'])
            <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/60 dark:bg-emerald-950/20 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-300">Format Kolom Cocok & Sesuai Template!</h4>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400">Seluruh {{ count($requiredHeaders) }} kolom wajib terdeteksi dengan tepat pada sheet <strong>{{ $selectedSheet }}</strong>.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                        {{ count($validation['matched']) }}/{{ count($requiredHeaders) }} Kolom
                    </span>
                </div>

                <!-- Matched Columns Pill List -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($validation['matched'] as $col)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono bg-emerald-100/80 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <svg class="w-2.5 h-2.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $col }}
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-4 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/60 dark:bg-rose-950/20 space-y-3">
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-900 dark:text-rose-300">Format Kolom Tidak Sesuai Template!</h4>
                        <p class="text-xs text-rose-700 dark:text-rose-400/90 mt-0.5">
                            Sistem mendeteksi kolom yang hilang atau nama kolom yang tidak sesuai. Harap sesuaikan baris header berkas dengan template.
                        </p>
                    </div>
                </div>

                <!-- Missing Columns (in red) -->
                @if(!empty($validation['missing']))
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-rose-900 dark:text-rose-300">Kolom Wajib yang Hilang:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($validation['missing'] as $col)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold">
                                    <svg class="w-2.5 h-2.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    {{ $col }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Matched Columns (in green) -->
                @if(!empty($validation['matched']))
                    <div class="space-y-1 pt-1">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Kolom Cocok yang Ditemukan:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($validation['matched'] as $col)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $col }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Data Preview (Collapsible) -->
        @if(!empty($previewRows))
            <div x-data="{ open: false }" class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900/40 shadow-xs">
                <button type="button" @click="open = !open" class="w-full px-4 py-2.5 bg-gray-50/80 dark:bg-gray-800/40 flex items-center justify-between text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/80 transition">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Pratinjau Data (Contoh {{ count($previewRows) }} Baris Pertama)
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" class="p-3 overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300 border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20">
                                @foreach($requiredHeaders as $header)
                                    <th class="px-2.5 py-1.5 font-semibold text-[11px] text-gray-500 uppercase tracking-wider">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($previewRows as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                    @foreach($requiredHeaders as $header)
                                        <td class="px-2.5 py-1.5 whitespace-nowrap text-xs text-gray-800 dark:text-gray-200">
                                            {{ $row[$header] ?? '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Progress Bar & Active Importing State -->
        @if($isImporting)
            <div wire:poll.100ms="processBatch" class="p-5 rounded-2xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/50 dark:bg-blue-950/20 space-y-4">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <div class="flex items-center gap-2 text-blue-700 dark:text-blue-300">
                        <svg class="animate-spin w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Mengimpor data ke database... (Baris {{ $processedRows }} dari {{ $totalRows }})</span>
                    </div>
                    <span class="text-sm font-black text-blue-600 dark:text-blue-400">{{ $progressPercent }}%</span>
                </div>

                <!-- Animated Progress Bar Container -->
                <div class="w-full bg-gray-200 dark:bg-gray-700/60 rounded-full h-3.5 overflow-hidden p-0.5 shadow-inner">
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500 h-full rounded-full transition-all duration-300 ease-out" 
                         style="width: {{ $progressPercent }}%">
                    </div>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center">
                    Harap tidak menutup jendela modal saat proses import sedang berlangsung.
                </p>
            </div>
        @endif

        <!-- Bottom Actions (When Not Importing) -->
        @if(!$isImporting)
            <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-800">
                <button type="button" 
                        wire:click="resetAll" 
                        class="px-4 py-2.5 text-xs font-semibold rounded-xl text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        wire:click="startImport" 
                        @disabled(!$validation['is_valid'] || $totalRows === 0)
                        style="background-color: {{ $validation['is_valid'] && $totalRows > 0 ? '#2563eb' : '#94a3b8' }} !important; color: #ffffff !important;"
                        class="px-5 py-2.5 text-xs font-bold rounded-xl text-white shadow-md transition flex items-center gap-2 {{ $validation['is_valid'] && $totalRows > 0 
                            ? 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 cursor-pointer hover:shadow-lg' 
                            : 'bg-slate-400 dark:bg-slate-700 cursor-not-allowed opacity-75' }}">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-white font-bold tracking-wide">
                        @if(!$validation['is_valid'])
                            Konfirmasi Import (Kolom Belum Sesuai)
                        @elseif($totalRows === 0)
                            Konfirmasi Import (Tidak Ada Data)
                        @else
                            Konfirmasi & Mulai Import ({{ $totalRows }} Data)
                        @endif
                    </span>
                </button>
            </div>
        @endif
    @endif

    <!-- Import Completed Summary -->
    @if($importFinished)
        <div class="p-6 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/60 dark:bg-emerald-950/20 text-center space-y-4">
            <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <div>
                <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-300">Import Data Selesai!</h3>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">
                    Data {{ $title }} dari berkas Anda telah berhasil diproses ke sistem.
                </p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto pt-1">
                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-emerald-200 dark:border-emerald-800">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Berhasil</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $importedCount }}</p>
                </div>
                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-800">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Dilewati</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $skippedCount }}</p>
                </div>
            </div>

            @if($type === 'product' && $generatedJournalNumber)
                <!-- Initial Stock Journal Information -->
                <div class="p-4 bg-white/90 dark:bg-gray-800/90 border border-blue-200 dark:border-blue-900/50 rounded-2xl text-left space-y-2.5 max-w-md mx-auto shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Jurnal Saldo Awal Persediaan
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                            POSTED
                        </span>
                    </div>

                    <div class="text-xs space-y-1.5 text-gray-700 dark:text-gray-300">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">No. Bukti Jurnal:</span>
                            <span class="font-mono font-bold text-blue-700 dark:text-blue-300">{{ $generatedJournalNumber }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Total Nilai Persediaan:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700/60 space-y-1">
                            <div class="flex items-center justify-between">
                                <span>[D] 111401001 - PERSEDIAAN BARANG DAGANG</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>[K] {{ \App\Models\Account::find($contraAccountId)?->name ?? 'MODAL DISETOR' }}</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Errors Log if any -->
            @if(!empty($importErrors))
                <div class="text-left p-3 bg-white dark:bg-gray-800 rounded-xl border border-amber-200 dark:border-amber-800 max-h-36 overflow-y-auto text-xs space-y-1">
                    <span class="font-bold text-amber-800 dark:text-amber-400">Catatan Baris yang Dilewati:</span>
                    <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-0.5">
                        @foreach($importErrors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Completion Actions -->
            <div class="flex items-center justify-center gap-3 pt-3">
                <button type="button" wire:click="resetAll" class="px-4 py-2 text-xs font-semibold rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    Import Berkas Lain
                </button>
                <button type="button" onclick="window.location.reload()" class="px-5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-md hover:shadow-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Selesai & Muat Ulang Halaman
                </button>
            </div>
        </div>
    @endif
</div>
