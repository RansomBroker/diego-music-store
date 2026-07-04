@props(['activeCategory'])

<div class="flex items-center gap-3 mb-6 overflow-x-auto no-scrollbar pb-2">
    @php
        $categories = [
            'Semua' => '🌐',
            'Gitar & Bass' => '🎸',
            'Keyboard & Piano' => '🎹',
            'Drum & Perkusi' => '🥁',
            'Aksesoris' => '🔌',
            'Jasa Reparasi' => '🛠️',
        ];
    @endphp

    @foreach ($categories as $cat => $emoji)
        @if ($activeCategory === $cat)
            <button wire:click="setCategory('{{ $cat }}')" class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold whitespace-nowrap shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <span>{{ $emoji }}</span> {{ $cat }}
            </button>
        @else
            <button wire:click="setCategory('{{ $cat }}')" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-primary dark:hover:border-blue-500 hover:text-primary dark:hover:text-blue-400 rounded-xl text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-2">
                <span>{{ $emoji }}</span> {{ $cat }}
            </button>
        @endif
    @endforeach
</div>
