<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Setting Struk & Invoice"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                <div>
                    <!-- Breadcrumbs -->
                    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-400 dark:text-slate-500">Utility</span>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Setting Struk & Invoice</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Setting Struk & Invoice</h1>
                </div>

                <!-- Form & Live Thermal Receipt Preview Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left 2 Cols: Setting Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-6 transition-colors duration-200">
                            
                            <form wire:submit.prevent="save" class="space-y-6">
                                
                                <div class="space-y-4">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Format Kertas & Identitas</h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Store Display Name Override -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Judul Utama Struk</label>
                                            <input
                                                type="text"
                                                wire:model.live="store_display_name"
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                                placeholder="e.g. Diego Music Store"
                                            >
                                        </div>

                                        <!-- Ukuran Kertas Printer -->
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ukuran Kertas Thermal Printer</label>
                                            <select
                                                wire:model.live="paper_width"
                                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                            >
                                                <option value="80mm">80mm Standard Thermal Printer</option>
                                                <option value="58mm">58mm Mini Thermal Printer</option>
                                                <option value="A4">A4 Full Page (Invoice / Faktur)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Pesan Header Struk -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pesan Header / Sambutan Struk</label>
                                        <input
                                            type="text"
                                            wire:model.live="header_text"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                            placeholder="e.g. Selamat Datang! Layanan Musik Terbaik"
                                        >
                                    </div>
                                </div>

                                <!-- Checklist Tampilan Detail Struk -->
                                <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Komponen Yang Ditampilkan</h3>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/40 cursor-pointer">
                                            <input type="checkbox" wire:model.live="show_logo" class="w-4 h-4 rounded text-primary border-slate-300 dark:border-slate-700">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Logo Toko</span>
                                        </label>

                                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/40 cursor-pointer">
                                            <input type="checkbox" wire:model.live="show_customer" class="w-4 h-4 rounded text-primary border-slate-300 dark:border-slate-700">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Pelanggan</span>
                                        </label>

                                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/40 cursor-pointer">
                                            <input type="checkbox" wire:model.live="show_cashier" class="w-4 h-4 rounded text-primary border-slate-300 dark:border-slate-700">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Kasir</span>
                                        </label>

                                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-950/40 cursor-pointer">
                                            <input type="checkbox" wire:model.live="show_tax_details" class="w-4 h-4 rounded text-primary border-slate-300 dark:border-slate-700">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Detail PPN / Tax</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Pesan Footer Struk & Catatan Invoice -->
                                <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Kaki Struk & Faktur</h3>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Kaki Struk (Footer)</label>
                                        <textarea
                                            wire:model.live="footer_text"
                                            rows="2"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                            placeholder="e.g. Terima Kasih atas Kunjungan Anda. Barang yang sudah dibeli tidak dapat ditukar/dikembalikan."
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan Invoice / Faktur Penjualan</label>
                                        <textarea
                                            wire:model.live="invoice_footer_notes"
                                            rows="2"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                            placeholder="e.g. Rekening Pembayaran BCA 123-456-7890 a.n Diego Music Store."
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                                    >
                                        <i class="ph-bold ph-floppy-disk text-base"></i>
                                        <span>Simpan Setting Struk</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Live Thermal Receipt Simulator -->
                    <div class="space-y-4">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-printer text-primary text-base"></i>
                            Live Thermal Receipt Preview ({{ $paper_width }})
                        </div>

                        <!-- Thermal Receipt Simulator Container -->
                        <div class="bg-white text-slate-900 font-mono text-xs p-5 shadow-2xl rounded-lg border border-slate-200 max-w-xs mx-auto space-y-3 relative overflow-hidden select-none">
                            <div class="w-full text-center border-b-2 border-dashed border-slate-900 pb-3 space-y-1">
                                @if ($show_logo && $selectedLogoUrl)
                                    <div class="w-10 h-10 mx-auto rounded overflow-hidden mb-1">
                                        <img src="{{ $selectedLogoUrl }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="font-bold text-sm uppercase">{{ $store_display_name ?: ($branch?->store_name ?: 'Diego Music Store') }}</div>
                                @if ($header_text)
                                    <div class="text-[10px] text-slate-600 italic">{{ $header_text }}</div>
                                @endif
                                <div class="text-[10px]">{{ $branch?->name ?: 'Cabang Utama' }}</div>
                                <div class="text-[10px]">Telp: {{ $branch?->phone ?: '08123456789' }}</div>
                            </div>

                            <div class="space-y-1 text-[11px] border-b border-dashed border-slate-900 pb-2">
                                <div class="flex justify-between">
                                    <span>No:</span>
                                    <span class="font-bold">INV/20260720/0001</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Tgl:</span>
                                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                                </div>
                                @if ($show_cashier)
                                    <div class="flex justify-between">
                                        <span>Kasir:</span>
                                        <span>{{ auth()->user()->name }}</span>
                                    </div>
                                @endif
                                @if ($show_customer)
                                    <div class="flex justify-between">
                                        <span>Pelanggan:</span>
                                        <span>Budi Santoso</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Dummy Items -->
                            <div class="space-y-1.5 text-[11px] border-b border-dashed border-slate-900 pb-3">
                                <div>Gitar Akustik Yamaha F310</div>
                                <div class="flex justify-between">
                                    <span>1 x Rp 1.750.000</span>
                                    <span>Rp 1.750.000</span>
                                </div>
                                <div>Senar Gitar D'Addario 0.10</div>
                                <div class="flex justify-between">
                                    <span>2 x Rp 95.000</span>
                                    <span>Rp 190.000</span>
                                </div>
                            </div>

                            <div class="space-y-1 text-[11px] border-b-2 border-dashed border-slate-900 pb-2">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>Rp 1.940.000</span>
                                </div>
                                @if ($show_tax_details)
                                    <div class="flex justify-between">
                                        <span>PPN (11%):</span>
                                        <span>Rp 213.400</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-bold text-sm pt-1">
                                    <span>Total:</span>
                                    <span>Rp 2.153.400</span>
                                </div>
                            </div>

                            <div class="text-center text-[10px] pt-2 leading-tight text-slate-700">
                                {{ $footer_text ?: 'Terima Kasih atas Kunjungan Anda.' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
