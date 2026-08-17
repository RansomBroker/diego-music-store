@props([
    'paginator' => null,
    'perPageModel' => 'perPage',
])

<div {{ $attributes->merge(['class' => 'px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs']) }}>
    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
        <span>Tampilkan:</span>
        <select wire:model.live="{{ $perPageModel }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-lg px-2.5 py-1 text-xs font-semibold focus:ring-1 focus:ring-primary focus:border-primary">
            <option value="15">15 per halaman</option>
            <option value="25">25 per halaman</option>
            <option value="50">50 per halaman</option>
            <option value="100">100 per halaman</option>
            <option value="0">Semua Data</option>
        </select>

        @if ($paginator)
            <span class="text-slate-400 dark:text-slate-500">
                (Menampilkan {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data)
            </span>
        @endif
    </div>

    @if ($paginator && $paginator->hasPages())
        <div>
            {{ $paginator->links() }}
        </div>
    @endif
</div>
