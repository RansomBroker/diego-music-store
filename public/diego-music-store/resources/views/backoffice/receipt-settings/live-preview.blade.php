@php
    $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
    $selectedLogoUrl = ($branch && ! empty($branch->logo_path))
        ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
        : null;
    $resolvedStoreName = $store_display_name ?: ($branch?->store_name ?: ($branch?->name ?: 'Diego Music Store'));
    $resolvedBranchName = $branch?->name ?: 'Cabang Utama';
    $resolvedPhone = $branch?->phone ?: '08123456789';
    $resolvedFooter = $footer_text ?: 'Terima Kasih atas Kunjungan Anda.';
    $previewWidthStyle = match ($paper_width) {
        '58mm' => 'width: 240px;',
        'A4' => 'width: 100%; max-width: 440px;',
        default => 'width: 310px;',
    };
@endphp

<div x-data="{ previewMode: 'struk' }" class="space-y-3">
    <!-- Mode Switcher: Struk / Tagihan (Bill) / Invoice / Penawaran -->
    <div class="flex flex-wrap items-center justify-center gap-1.5 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <button
            type="button"
            x-on:click="previewMode = 'struk'"
            :class="previewMode === 'struk'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'text-gray-600 dark:text-gray-300 hover:bg-white/70 dark:hover:bg-gray-700/60'"
            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer"
        >
            Struk ({{ $paper_width }})
        </button>
        <button
            type="button"
            x-on:click="previewMode = 'bill'"
            :class="previewMode === 'bill'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'text-gray-600 dark:text-gray-300 hover:bg-white/70 dark:hover:bg-gray-700/60'"
            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer"
        >
            Tagihan (Bill)
        </button>
        <button
            type="button"
            x-on:click="previewMode = 'invoice'"
            :class="previewMode === 'invoice'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'text-gray-600 dark:text-gray-300 hover:bg-white/70 dark:hover:bg-gray-700/60'"
            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer"
        >
            Invoice
        </button>
        <button
            type="button"
            x-on:click="previewMode = 'penawaran'"
            :class="previewMode === 'penawaran'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'text-gray-600 dark:text-gray-300 hover:bg-white/70 dark:hover:bg-gray-700/60'"
            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer"
        >
            Penawaran (A4)
        </button>
    </div>

    <div class="overflow-x-auto pb-2">
        <!-- 1. PREVIEW STRUK THERMAL (Template dari pos/receipt-settings) -->
        <div x-show="previewMode === 'struk'" style="{{ $previewWidthStyle }}" class="bg-white text-gray-900 font-mono text-xs p-4 shadow-md rounded-lg border border-gray-200 mx-auto space-y-2.5 select-none">
            <div class="w-full text-center border-b-2 border-dashed border-gray-800 pb-2.5 space-y-1">
                @if ($show_logo && $selectedLogoUrl)
                    <div class="w-10 h-10 mx-auto rounded overflow-hidden mb-1">
                        <img src="{{ $selectedLogoUrl }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                @endif
                <div class="font-bold text-sm uppercase">{{ $resolvedStoreName }}</div>
                @if (! empty($header_text))
                    <div class="text-[10px] text-gray-600 italic">{!! nl2br(e($header_text)) !!}</div>
                @endif
                <div class="text-[10px]">{{ $resolvedBranchName }}</div>
                @if ($branch?->address)
                    <div class="text-[10px] text-gray-600">{{ $branch->address }}</div>
                @endif
                <div class="text-[10px]">Telp: {{ $resolvedPhone }}</div>
            </div>

            <div class="space-y-1 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">No:</span>
                    <span class="font-bold text-right">INV/20261127/0001</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Tgl:</span>
                    <span class="text-right whitespace-nowrap">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                @if ($show_cashier)
                    <div class="flex justify-between gap-2">
                        <span class="shrink-0">Kasir:</span>
                        <span class="text-right">{{ auth()->user()?->name ?: 'Kasir' }}</span>
                    </div>
                @endif
                @if ($show_customer)
                    <div class="flex justify-between gap-2">
                        <span class="shrink-0">Pelanggan:</span>
                        <span class="text-right">Budi Santoso</span>
                    </div>
                @endif
            </div>

            <div class="space-y-1.5 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div>Gitar Akustik Yamaha F310</div>
                <div class="flex justify-between gap-2">
                    <span class="whitespace-nowrap">1 x Rp 1.750.000</span>
                    <span class="whitespace-nowrap font-semibold">Rp 1.750.000</span>
                </div>
                <div>Senar Gitar D'Addario 0.10</div>
                <div class="flex justify-between gap-2">
                    <span class="whitespace-nowrap">2 x Rp 95.000</span>
                    <span class="whitespace-nowrap font-semibold">Rp 190.000</span>
                </div>
            </div>

            <div class="space-y-1 text-[11px] border-b-2 border-dashed border-gray-800 pb-2">
                <div class="flex justify-between gap-2">
                    <span>Subtotal:</span>
                    <span class="whitespace-nowrap">Rp 1.940.000</span>
                </div>
                @if ($show_tax_details)
                    <div class="flex justify-between gap-2">
                        <span>PPN (11%):</span>
                        <span class="whitespace-nowrap">Rp 213.400</span>
                    </div>
                @endif
                <div class="flex justify-between gap-2 font-bold text-xs pt-1">
                    <span>Total:</span>
                    <span class="whitespace-nowrap">Rp 2.153.400</span>
                </div>
            </div>

            <!-- Metode Bayar, Kembalian (Cash), & Sales -->
            <div class="space-y-1 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div class="flex justify-between gap-2">
                    <span>Pembayaran:</span>
                    <span class="font-bold">CASH</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span>Dibayar:</span>
                    <span class="whitespace-nowrap">Rp 2.200.000</span>
                </div>
                <div class="flex justify-between gap-2 font-bold">
                    <span>Kembalian:</span>
                    <span class="whitespace-nowrap">Rp 46.600</span>
                </div>
            </div>

            <div class="space-y-1 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div class="flex justify-between gap-2">
                    <span class="shrink-0">Sales:</span>
                    <span class="text-right">Andi Pratama</span>
                </div>
            </div>

            <div class="text-center text-[10px] pt-1 leading-tight text-gray-700">
                {!! nl2br(e($resolvedFooter)) !!}
            </div>
        </div>

        <!-- 2. PREVIEW TAGIHAN / BILL (Base Template Bawaan POS Kasir) -->
        <div x-show="previewMode === 'bill'" x-cloak style="{{ $previewWidthStyle }}" class="bg-white text-gray-900 font-mono text-xs p-4 shadow-md rounded-lg border border-gray-200 mx-auto space-y-2.5 select-none">
            <div class="bg-black text-white text-center font-bold py-1 text-[11px] tracking-wider">
                DRAFT TAGIHAN
            </div>

            <div class="w-full text-center border-b-2 border-double border-gray-900 pb-2 space-y-0.5">
                @if ($show_logo && $selectedLogoUrl)
                    <div class="w-9 h-9 mx-auto rounded overflow-hidden mb-1">
                        <img src="{{ $selectedLogoUrl }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                @endif
                <div class="font-bold text-sm uppercase">{{ $resolvedStoreName }}</div>
                @if (! empty($header_text))
                    <div class="text-[10px] text-gray-600 italic">{!! nl2br(e($header_text)) !!}</div>
                @endif
                <div class="text-[10px]">{{ $resolvedBranchName }}</div>
                <div class="text-[10px]">Telp: {{ $resolvedPhone }}</div>
            </div>

            <div class="space-y-1 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div class="flex justify-between gap-2">
                    <span>No. Ref:</span>
                    <span class="font-bold">DRAFT-20261127</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span>Tanggal:</span>
                    <span class="whitespace-nowrap">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                @if ($show_cashier)
                    <div class="flex justify-between gap-2">
                        <span>Kasir:</span>
                        <span>{{ auth()->user()?->name ?: 'Kasir' }}</span>
                    </div>
                @endif
                @if ($show_customer)
                    <div class="flex justify-between gap-2">
                        <span>Pelanggan:</span>
                        <span>Budi Santoso</span>
                    </div>
                @endif
            </div>

            <div class="space-y-1 text-[11px] border-b border-dashed border-gray-800 pb-2">
                <div class="font-bold">Rincian Belanja:</div>
                <div>[DMS-P-0001] Gitar Yamaha F310</div>
                <div class="flex justify-between gap-2">
                    <span class="whitespace-nowrap">1 x Rp 1.750.000</span>
                    <span class="whitespace-nowrap">Rp 1.750.000</span>
                </div>
            </div>

            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between gap-2">
                    <span>Subtotal:</span>
                    <span class="whitespace-nowrap">Rp 1.750.000</span>
                </div>
                @if ($show_tax_details)
                    <div class="flex justify-between gap-2">
                        <span>PPN (11%):</span>
                        <span class="whitespace-nowrap">Rp 192.500</span>
                    </div>
                @endif
                <div class="flex justify-between gap-2 font-bold border-t-2 border-double border-gray-900 pt-1">
                    <span>Total Akhir:</span>
                    <span class="whitespace-nowrap">Rp 1.942.500</span>
                </div>
            </div>

            <div class="border border-gray-900 text-center font-bold text-[10px] py-1">
                BUKAN BUKTI PEMBAYARAN RESMI
            </div>

            <div class="text-center text-[10px] text-gray-700 space-y-1">
                <div>{!! nl2br(e($resolvedFooter)) !!}</div>
                @if (! empty($invoice_footer_notes))
                    <div class="border-t border-dashed border-gray-500 pt-1 text-gray-600">{!! nl2br(e($invoice_footer_notes)) !!}</div>
                @endif
            </div>
        </div>

        <!-- 3. PREVIEW INVOICE (Base Template Bawaan POS Kasir) -->
        <div x-show="previewMode === 'invoice'" x-cloak class="bg-white text-gray-900 font-mono text-[10px] p-4 shadow-md rounded-lg border border-gray-200 w-full max-w-[440px] mx-auto space-y-2.5 select-none">
            <div class="text-center space-y-0.5">
                @if ($show_logo && $selectedLogoUrl)
                    <img src="{{ $selectedLogoUrl }}" alt="Logo" class="h-8 mx-auto mb-1 object-contain">
                @endif
                <div class="font-bold text-sm uppercase">{{ $resolvedStoreName }}</div>
                @if (! empty($header_text))
                    <div class="italic text-gray-600">{!! nl2br(e($header_text)) !!}</div>
                @endif
                <div>{{ $resolvedBranchName }} | Telp: {{ $resolvedPhone }}</div>
                <div class="font-bold text-xs tracking-wider pt-1">INVOICE</div>
                <div class="inline-block border-2 border-gray-900 px-2 py-0.5 font-bold text-[10px] rounded">
                    LUNAS / PAID
                </div>
            </div>

            <div class="flex justify-between border-y border-gray-300 py-1.5 gap-2">
                <div>
                    <div><span class="font-bold">No. Ref:</span> INV/20261127/0001</div>
                    <div><span class="font-bold">Tanggal:</span> {{ now()->format('d/m/Y H:i') }}</div>
                </div>
                <div class="text-right">
                    @if ($show_cashier)
                        <div><span class="font-bold">Kasir:</span> {{ auth()->user()?->name ?: 'Kasir' }}</div>
                    @endif
                    @if ($show_customer)
                        <div><span class="font-bold">Pelanggan:</span> Budi Santoso</div>
                    @endif
                </div>
            </div>

            <table class="w-full border-collapse border border-gray-900 text-[9.5px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-900 px-1.5 py-1 text-left">Produk</th>
                        <th class="border border-gray-900 px-1.5 py-1 text-center">Qty</th>
                        <th class="border border-gray-900 px-1.5 py-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-900 px-1.5 py-1">Gitar Yamaha F310</td>
                        <td class="border border-gray-900 px-1.5 py-1 text-center">1</td>
                        <td class="border border-gray-900 px-1.5 py-1 text-right whitespace-nowrap">Rp 1.750.000</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end">
                <div class="w-44 space-y-0.5">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-bold">Rp 1.750.000</span>
                    </div>
                    @if ($show_tax_details)
                        <div class="flex justify-between">
                            <span>PPN (11%)</span>
                            <span class="font-bold">Rp 192.500</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-[11px] border-t border-gray-900 pt-0.5">
                        <span>Total Akhir</span>
                        <span>Rp 1.942.500</span>
                    </div>
                </div>
            </div>

            @if (! empty($invoice_footer_notes) || ! empty($footer_text))
                <div class="border border-dashed border-gray-800 p-2 text-[9.5px] space-y-1">
                    @if (! empty($invoice_footer_notes))
                        <div class="font-bold">Catatan Pembayaran / Invoice:</div>
                        <div>{!! nl2br(e($invoice_footer_notes)) !!}</div>
                    @endif
                    @if (! empty($footer_text))
                        <div class="italic text-gray-600">{!! nl2br(e($footer_text)) !!}</div>
                    @endif
                </div>
            @endif

            <div class="flex justify-between px-4 pt-4 text-center text-[9.5px]">
                <div>
                    <div class="mb-6">Penerima</div>
                    <div class="border-b border-gray-900 w-20"></div>
                </div>
                <div>
                    <div class="mb-6">Hormat Kami</div>
                    <div class="border-b border-gray-900 w-20"></div>
                </div>
            </div>
        </div>

        <!-- 4. PREVIEW PENAWARAN / DRAFT INVOICE A4 (Base Template Modern A4 POS Kasir) -->
        <div x-show="previewMode === 'penawaran'" x-cloak class="bg-white text-slate-700 font-sans text-[10px] p-4 shadow-md rounded-xl border border-slate-200 w-full max-w-[440px] mx-auto space-y-3 select-none">
            <div class="bg-red-50 border border-red-200 text-red-600 text-center font-extrabold py-1.5 rounded-lg text-[10px] tracking-wider">
                DOKUMEN PENAWARAN HARGA
            </div>

            <div class="flex justify-between items-start border-b border-slate-200 pb-2.5 gap-2">
                <div>
                    @if ($show_logo && $selectedLogoUrl)
                        <img src="{{ $selectedLogoUrl }}" alt="Logo" class="h-7 mb-1 object-contain">
                    @endif
                    <div class="text-sm font-extrabold text-slate-800">{{ $resolvedStoreName }}</div>
                    @if (! empty($header_text))
                        <div class="text-[9.5px] italic text-slate-500">{!! nl2br(e($header_text)) !!}</div>
                    @endif
                    <div class="font-semibold text-slate-600">{{ $resolvedBranchName }}</div>
                    <div class="text-slate-500">Telp: {{ $resolvedPhone }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-extrabold text-blue-600 uppercase">PENAWARAN HARGA</div>
                    <div class="text-[9.5px] font-mono text-slate-600">DRAFT-20261127</div>
                    <div class="text-[9.5px] text-slate-500">{{ now()->format('d M Y') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <div class="text-[9px] font-bold uppercase text-slate-400">Disiapkan Oleh</div>
                    <div class="font-semibold text-slate-800">{{ auth()->user()?->name ?: 'Kasir' }}</div>
                </div>
                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <div class="text-[9px] font-bold uppercase text-slate-400">Ditujukan Kepada</div>
                    <div class="font-semibold text-slate-800">Budi Santoso</div>
                </div>
            </div>

            <table class="w-full text-[9.5px]">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <th class="py-1.5 px-2 text-left">Produk</th>
                        <th class="py-1.5 px-2 text-center">Qty</th>
                        <th class="py-1.5 px-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-100">
                        <td class="py-1.5 px-2 font-semibold text-slate-800">Gitar Yamaha F310</td>
                        <td class="py-1.5 px-2 text-center">1</td>
                        <td class="py-1.5 px-2 text-right font-bold whitespace-nowrap">Rp 1.750.000</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end">
                <div class="w-44 space-y-0.5 text-[10px]">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-semibold">Rp 1.750.000</span>
                    </div>
                    @if ($show_tax_details)
                        <div class="flex justify-between">
                            <span class="text-slate-500">PPN (11%)</span>
                            <span class="font-semibold">Rp 192.500</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-extrabold text-slate-900 border-t border-slate-200 pt-1">
                        <span>Total Akhir</span>
                        <span>Rp 1.942.500</span>
                    </div>
                </div>
            </div>

            @if (! empty($invoice_footer_notes) || ! empty($footer_text))
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-[9.5px] space-y-1">
                    @if (! empty($invoice_footer_notes))
                        <div class="font-bold text-slate-800">Catatan Pembayaran / Tagihan</div>
                        <div class="text-slate-600">{!! nl2br(e($invoice_footer_notes)) !!}</div>
                    @endif
                    @if (! empty($footer_text))
                        <div class="italic text-slate-500">{!! nl2br(e($footer_text)) !!}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
