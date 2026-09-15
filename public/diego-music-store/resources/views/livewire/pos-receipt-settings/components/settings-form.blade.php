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
