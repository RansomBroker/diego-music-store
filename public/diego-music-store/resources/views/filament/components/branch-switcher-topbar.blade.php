@php
    $user = auth()->user();
    if (!$user) return;

    $currentBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
    $currentBranch = \App\Models\Branch::find($currentBranchId);
    $userBranches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
@endphp

@if ($userBranches->count() > 0)
    <div class="relative flex items-center mr-2" x-data="{ open: false }">
        <button 
            type="button"
            @click="open = !open" 
            class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 cursor-pointer"
            title="Ganti Lokasi Cabang Aktif"
        >
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="font-bold uppercase tracking-wider text-[11px]">{{ $currentBranch?->name ?: 'Pilih Cabang' }}</span>
            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div 
            x-show="open" 
            @click.away="open = false" 
            x-cloak 
            class="absolute right-0 top-full mt-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-2 space-y-1"
        >
            <div class="px-3 py-1.5 text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                Lokasi Cabang Operasional
            </div>

            @foreach ($userBranches as $b)
                <a 
                    href="{{ route('pos.switch-branch', $b->id) }}" 
                    class="flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition-colors {{ $b->id == $currentBranchId ? 'bg-blue-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    <div class="truncate">
                        <div>{{ $b->name }}</div>
                        <div class="text-[10px] font-normal opacity-80">{{ $b->city ?: $b->store_name }}</div>
                    </div>
                    @if ($b->id == $currentBranchId)
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
