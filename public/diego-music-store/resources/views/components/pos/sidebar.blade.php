@props(['selectedLogoUrl' => null])

@php
    $hasLogo = !empty($selectedLogoUrl) && $selectedLogoUrl !== '/storage' && $selectedLogoUrl !== '/storage/';
@endphp

<aside class="w-24 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col items-center py-6 shadow-sm z-10 flex-shrink-0 hidden md:flex transition-colors">
    <!-- Logo -->
    <div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center mb-8 shadow-md {{ $hasLogo ? '' : 'bg-primary text-white shadow-blue-500/30' }}">
        @if ($hasLogo)
            <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-cover">
        @else
            <i class="ph-bold ph-storefront text-2xl"></i>
        @endif
    </div>

    @php
        $isPos = request()->is('pos') || request()->is('pos/login');
        $isSession = request()->is('pos/session*');
    @endphp

    <!-- Menu Items -->
    <nav class="flex flex-col gap-4 flex-1 w-full px-3">
        @if ($isPos)
            <button class="w-full aspect-square flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primaryLight dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-squares-four text-2xl mb-1"></i>
                <span class="text-[10px] font-semibold">Kasir</span>
            </button>
        @else
            <a href="/pos" class="w-full aspect-square flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-squares-four text-2xl mb-1"></i>
                <span class="text-[10px] font-medium">Kasir</span>
            </a>
        @endif

        @if ($isSession)
            <button class="w-full aspect-square flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primaryLight dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-clock-counter-clockwise text-2xl mb-1"></i>
                <span class="text-[10px] font-semibold">Sesi Kasir</span>
            </button>
        @else
            <a href="/pos/session" class="w-full aspect-square flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-clock-counter-clockwise text-2xl mb-1"></i>
                <span class="text-[10px] font-medium">Sesi Kasir</span>
            </a>
        @endif

        <a href="/backoffice" class="w-full aspect-square flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
            <i class="ph ph-house text-2xl mb-1"></i>
            <span class="text-[10px] font-medium">Backoffice</span>
        </a>
    </nav>

    <!-- Theme Switcher -->
    <div class="mt-auto flex flex-col gap-4 w-full px-3 items-center">
        <!-- Dark Mode Toggle Button -->
        <button onclick="toggleDarkMode()" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors" title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-lg"></i>
            <i class="ph-bold ph-moon hidden dark:block text-lg"></i>
        </button>
    </div>
    
    <script>
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</aside>
