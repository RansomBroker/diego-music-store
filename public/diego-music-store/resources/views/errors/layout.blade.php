<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pemberitahuan Sistem | Diego Music Store')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS compiled via Vite -->
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Alpine.js (Stand-alone support for error pages) -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    {{-- POS-Style Global Navbar --}}
    <x-pos.navbar
        :pageTitle="$pageTitle ?? 'Pemberitahuan Sistem'"
        :showBack="false"
        :showSidebarToggle="false"
        :showNotifications="false"
        :showAttendance="false"
        :showCashSession="false"
        :showClock="false"
    />

    {{-- Main Content Error Area --}}
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-2xl mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- Footer Info --}}
    <footer class="py-4 text-center text-xs font-semibold text-slate-400 dark:text-slate-600">
        &copy; {{ date('Y') }} Diego Music Store ERP. Hak Cipta Dilindungi.
    </footer>

    <script>
        // Check for saved dark mode preference or system preference
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>
</html>

