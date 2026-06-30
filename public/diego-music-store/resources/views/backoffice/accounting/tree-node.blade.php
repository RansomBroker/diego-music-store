@php
    $hasChildren = $account->children()->exists();
    $normal = $account->getNormalBalance();
@endphp

<div x-data="{ open: true }" class="space-y-1">
    <!-- Row representation of the account -->
    <div class="flex items-center justify-between p-2 rounded-lg transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40 border border-transparent {{ $account->is_header ? 'font-bold bg-gray-50/50 dark:bg-gray-800/20' : 'bg-transparent' }}">
        <div class="flex items-center gap-2">
            <!-- Collapse toggle or dot -->
            @if($hasChildren)
                <button @click="open = !open" type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-transform" :class="open ? 'rotate-90' : ''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @else
                <span class="w-4 h-4 inline-block"></span>
            @endif

            <!-- Icon (folder for header, doc for transaction) -->
            @if($account->is_header)
                <span class="text-amber-500 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                </span>
            @else
                <span class="text-sky-500 dark:text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            @endif

            <!-- Code and Name -->
            <span class="text-sm tracking-wide text-gray-700 dark:text-gray-200">
                <span class="font-mono text-gray-400 dark:text-gray-500 mr-1">{{ $account->code }}</span> - {{ $account->name }}
            </span>

            <!-- Badge for Classification -->
            @if($account->is_header && $depth === 0)
                <span class="px-2 py-0.5 text-[10px] rounded-full capitalize font-semibold {{
                    match(strtolower($account->classification ?? '')) {
                        'asset' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                        'liability' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                        'equity' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'revenue' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                        'expense' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                    }
                }}">
                    {{ $account->classificationRelation->name ?? $account->classification }}
                </span>
            @endif
        </div>

        <!-- Right side: balance and ledger action -->
        <div class="flex items-center gap-4">
            <span class="font-mono text-sm font-bold {{ $account->balance >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format($account->balance, 0, ',', '.') }}
            </span>

            <!-- ledger button if not header -->
            @if(!$account->is_header)
                <button 
                    wire:click="mountTableAction('ledger', {{ $account->id }})"
                    type="button" 
                    class="p-1 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 rounded transition-colors"
                    title="Lihat Buku Besar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </button>
            @else
                <span class="w-7 h-7 inline-block"></span>
            @endif
        </div>
    </div>

    <!-- Render Children recursively -->
    @if($hasChildren)
        <div x-show="open" x-collapse x-transition class="pl-6 border-l border-gray-100 dark:border-gray-800/80 space-y-1 mt-1">
            @foreach($account->children as $child)
                @include('backoffice.accounting.tree-node', ['account' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
