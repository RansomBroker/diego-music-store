@props([
    'activeCategory', 
    'categoryCounts' => [],
    'availableCategories' => [],
])

@php
    /**
     * Emoji mapping berdasarkan kata kunci di nama kategori.
     * Digunakan untuk memperindah tab kategori secara otomatis.
     */
    function getCategoryEmoji(string $cat): string {
        $c = strtolower($cat);
        if (str_contains($c, 'gitar') || str_contains($c, 'guitar') || str_contains($c, 'bass') || str_contains($c, 'ukulele') || str_contains($c, 'cajon') || str_contains($c, 'harmonica') || str_contains($c, 'kalimba') || str_contains($c, 'pianika') || str_contains($c, 'suling') || str_contains($c, 'tamborin')) return '🎸';
        if (str_contains($c, 'keyboard') || str_contains($c, 'piano') || str_contains($c, 'saxophone') || str_contains($c, 'footstool')) return '🎹';
        if (str_contains($c, 'drum') || str_contains($c, 'stick') || str_contains($c, 'perkusi') || str_contains($c, 'metronom')) return '🥁';
        if (str_contains($c, 'senar') || str_contains($c, 'kabel') || str_contains($c, 'jack') || str_contains($c, 'strap') || str_contains($c, 'pick') || str_contains($c, 'capo')) return '🎵';
        if (str_contains($c, 'amplifier') || str_contains($c, 'speaker') || str_contains($c, 'mixer') || str_contains($c, 'audio') || str_contains($c, 'soundcard') || str_contains($c, 'recording') || str_contains($c, 'microphone') || str_contains($c, 'wireless')) return '🔊';
        if (str_contains($c, 'effects') || str_contains($c, 'pedal') || str_contains($c, 'preamp') || str_contains($c, 'pedalboard')) return '🎛️';
        if (str_contains($c, 'biola')) return '🎻';
        if (str_contains($c, 'stand')) return '🗿';
        if (str_contains($c, 'tas') || str_contains($c, 'gigbag') || str_contains($c, 'hardcase')) return '🎒';
        if (str_contains($c, 'headphone') || str_contains($c, 'in ear')) return '🎧';
        if (str_contains($c, 'ongkos')) return '🛠️';
        if (str_contains($c, 'cleaner') || str_contains($c, 'tools')) return '🔧';
        return '🎼';
    }
@endphp

<div class="flex items-center gap-2 mb-4 overflow-x-auto no-scrollbar pb-1">
    {{-- Tab "Semua" selalu di depan --}}
    @php $totalInCart = $categoryCounts['Semua'] ?? 0; @endphp
    <button 
        wire:click="setCategory('Semua')" 
        class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 flex-shrink-0
            {{ $activeCategory === 'Semua' 
                ? 'bg-primary text-white shadow-md shadow-blue-500/20' 
                : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-primary dark:hover:border-blue-500 hover:text-primary dark:hover:text-blue-400' }}"
    >
        <span>🌐</span>
        <span>Semua</span>
        @if ($totalInCart > 0)
            <span class="px-1.5 py-0.5 {{ $activeCategory === 'Semua' ? 'bg-white text-primary' : 'bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-400' }} text-[10px] font-black rounded-full leading-none">
                {{ $totalInCart }}
            </span>
        @endif
    </button>

    {{-- Tab kategori dari DB, diurutkan berdasarkan jumlah produk terbanyak --}}
    @foreach ($availableCategories as $cat)
        @php
            $catName  = $cat['name'];
            $emoji    = getCategoryEmoji($catName);
            $inCart   = $categoryCounts[$catName] ?? 0;
            $isActive = $activeCategory === $catName;
        @endphp
        <button 
            wire:click="setCategory('{{ $catName }}')" 
            class="px-4 py-2 rounded-xl text-sm whitespace-nowrap transition-all flex items-center gap-1.5 flex-shrink-0
                {{ $isActive 
                    ? 'bg-primary text-white font-semibold shadow-md shadow-blue-500/20' 
                    : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-medium hover:border-primary dark:hover:border-blue-500 hover:text-primary dark:hover:text-blue-400' }}"
        >
            <span>{{ $emoji }}</span>
            <span>{{ $catName }}</span>
            @if ($inCart > 0)
                <span class="px-1.5 py-0.5 {{ $isActive ? 'bg-white text-primary' : 'bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-400' }} text-[10px] font-black rounded-full leading-none">
                    {{ $inCart }}
                </span>
            @endif
        </button>
    @endforeach
</div>
