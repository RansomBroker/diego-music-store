<!-- Right Column: Live Thermal Receipt Simulator -->
<div class="space-y-4">
    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
        <i class="ph-bold ph-printer text-primary text-base"></i>
        Live Thermal Receipt Preview ({{ $paper_width }})
    </div>

    <!-- Thermal Receipt Simulator Container -->
    <div class="bg-white text-slate-900  text-xs p-5 shadow-2xl rounded-lg border border-slate-200 max-w-xs mx-auto space-y-3 relative overflow-hidden select-none">
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
