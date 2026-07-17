<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'POS Kasir Modern' }}</title>
    <!-- Tailwind CSS compiled via Vite -->
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 h-screen w-full overflow-hidden flex transition-colors duration-200">
    
    {{ $slot }}

    @livewireScripts
    <script>
        // Check for saved dark mode preference or system preference
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        window.addEventListener('print-receipt', event => {
            const saleId = event.detail.saleId;
            const url = `{{ url('/pos/receipt') }}/${saleId}`;
            window.open(url, '_blank');
        });

        window.addEventListener('open-draft-bill', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>
</body>
</html>
