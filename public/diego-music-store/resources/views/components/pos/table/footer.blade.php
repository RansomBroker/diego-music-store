@props([
    'paginator' => null,
    'perPageModel' => 'perPage',
    'perPage' => null,
    'showPerPage' => null,
    'total' => null,
])

@php
    $hasPaginator = $paginator instanceof \Illuminate\Contracts\Pagination\Paginator || $paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator || (is_object($paginator) && method_exists($paginator, 'total'));
    $displayTotal = $hasPaginator ? $paginator->total() : ($total ?? null);
    $shouldShowPerPage = $showPerPage ?? ($hasPaginator && $perPageModel);

    // Get current per-page selection from prop, Livewire component, or paginator
    $currentPerPage = $perPage;
    if ($currentPerPage === null && isset($this) && !empty($perPageModel) && isset($this->{$perPageModel})) {
        $currentPerPage = (int) $this->{$perPageModel};
    }
    if ($currentPerPage === null && $hasPaginator && method_exists($paginator, 'perPage')) {
        $currentPerPage = (int) $paginator->perPage();
    }
    if ($currentPerPage === null) {
        $currentPerPage = 15;
    }

    $pageOptions = [5, 10, 15, 20, 25, 50, 100];
    if ($currentPerPage > 0 && !in_array($currentPerPage, $pageOptions)) {
        $pageOptions[] = $currentPerPage;
        sort($pageOptions);
    }
@endphp

<div {{ $attributes->merge(['class' => 'px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs']) }}>
    <div class="flex items-center flex-wrap gap-2 text-slate-500 dark:text-slate-400">
        @if ($shouldShowPerPage)
            <div class="flex items-center gap-1.5">
                <span>Tampilkan:</span>
                <select 
                    wire:key="per-page-select-{{ $perPageModel }}"
                    wire:model.live="{{ $perPageModel }}" 
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-lg px-2.5 py-1 text-xs font-semibold focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer"
                >
                    @foreach ($pageOptions as $opt)
                        <option value="{{ $opt }}" @selected($currentPerPage === $opt)>{{ $opt }} per halaman</option>
                    @endforeach
                    <option value="0" @selected($currentPerPage === 0)>Semua Data</option>
                </select>
            </div>
        @endif

        @if ($hasPaginator && method_exists($paginator, 'firstItem') && method_exists($paginator, 'lastItem'))
            <span class="text-slate-500 dark:text-slate-400 font-medium">
                (Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($paginator->total(), 0, ',', '.') }}</span> data)
            </span>
        @elseif ($displayTotal !== null)
            <span class="text-slate-500 dark:text-slate-400 font-medium">
                (Total <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($displayTotal, 0, ',', '.') }}</span> data ditampilkan)
            </span>
        @endif
    </div>

    @if ($hasPaginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
        <div class="w-full sm:w-auto flex justify-center sm:justify-end">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
