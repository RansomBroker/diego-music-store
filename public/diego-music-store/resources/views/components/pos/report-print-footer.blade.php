<div class="print-only mt-10 pt-4 border-t border-slate-300">
    <div class="grid grid-cols-3 text-center text-xs font-bold text-slate-800">
        <div>
            <p>Dibuat Oleh,</p>
            <div class="h-16"></div>
            <p class="underline">{{ auth()->user()?->name ?: 'Staf Kasir' }}</p>
            <p class="text-[10px] text-slate-500 font-normal">Staf POS / Operasional</p>
        </div>
        <div>
            <p>Diperiksa Oleh,</p>
            <div class="h-16"></div>
            <p class="underline">( __________________ )</p>
            <p class="text-[10px] text-slate-500 font-normal">Supervisor / Head Office</p>
        </div>
        <div>
            <p>Disetujui Oleh,</p>
            <div class="h-16"></div>
            <p class="underline">( __________________ )</p>
            <p class="text-[10px] text-slate-500 font-normal">Pemilik / Owner</p>
        </div>
    </div>
</div>
