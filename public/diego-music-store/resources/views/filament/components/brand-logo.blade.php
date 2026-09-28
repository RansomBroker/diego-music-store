@php
    $isAuthPage = request()->routeIs('filament.backoffice.auth.*') || request()->is('backoffice/login*') || ! auth()->check();
    $branchId = \App\Helpers\BranchHelper::getActiveBranchId();
    $branch = \App\Models\Branch::find($branchId);
    $storeName = $branch ? ($branch->store_name ?: "Diego Music Store ({$branch->name})") : 'Diego Music Store';
    $logoUrl = ($branch && !empty($branch->logo_path)) ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : asset('images/logo.png');
@endphp

@if ($isAuthPage)
    <div class="flex flex-col items-center justify-center mb-1">
        <div class="relative p-2 rounded-2xl group">
            <div class="absolute inset-0 bg-blue-500/10 dark:bg-blue-400/15 rounded-2xl blur-xl transition-all duration-300 group-hover:scale-125"></div>
            <img src="{{ $logoUrl }}" alt="Diego Music Logo" class="w-24 h-24 object-contain relative z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105">
        </div>
    </div>
@else
    <div class="flex items-center gap-3">
        <div class="h-18 w-18 flex items-center justify-center flex-shrink-0">
            <img src="{{ $logoUrl }}" alt="Diego Music Logo" class="w-full h-full object-contain">
        </div>
        <div class="flex flex-col text-left min-w-0">
            <span class="font-extrabold text-slate-900 dark:text-white tracking-tight text-sm leading-tight">DIEGO MUSIC</span>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium leading-tight truncate max-w-[170px]">
                {{ $storeName }}
            </span>
        </div>
    </div>
@endif
