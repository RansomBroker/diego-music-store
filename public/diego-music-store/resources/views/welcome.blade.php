<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diego Music Store & Repair - Portal</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS via Vite -->
    @vite('resources/css/app.css')
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        // Init dark mode
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-between transition-colors duration-200">
    <!-- Header/Theme Toggle -->
    <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                <i class="ph-bold ph-music-notes-simple text-xl"></i>
            </div>
            <div>
                <span class="font-extrabold text-slate-900 dark:text-white tracking-tight text-lg">DIEGO MUSIC</span>
                <span class="text-xs block -mt-1 text-slate-400 dark:text-slate-500 font-medium">Store & Repair ERP</span>
            </div>
        </div>
        
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 shadow-sm border border-slate-200/50 dark:border-slate-700/50 transition-colors" title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-lg"></i>
            <i class="ph-bold ph-moon hidden dark:block text-lg"></i>
        </button>
    </header>

    <!-- Main Selection Section -->
    <main class="flex-1 flex flex-col items-center justify-center px-6 py-12">
        <div class="text-center max-w-xl mb-12">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-950 dark:text-white tracking-tight mb-4">
                Selamat Datang di Portal <span class="text-primary dark:text-blue-400">Diego Music</span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-base md:text-lg">
                Pilih modul aplikasi di bawah ini untuk memulai operasional atau manajemen toko.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 max-w-4xl w-full">
            <!-- Backoffice Portal -->
            <a href="/backoffice" class="group relative bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-700/60 shadow-xl shadow-slate-100/50 dark:shadow-none hover:shadow-2xl hover:shadow-primary/5 dark:hover:border-primary/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute -right-16 -bottom-16 w-44 h-44 rounded-full bg-primary/5 group-hover:scale-125 transition-transform duration-500"></div>
                
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                        <i class="ph-bold ph-chart-line text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-950 dark:text-white mb-3 group-hover:text-primary dark:group-hover:text-blue-400 transition-colors">Aplikasi Backoffice</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-8">
                        Kelola manajemen persediaan (stok), pembelian, akuntansi keuangan, laporan laba rugi, pengelolaan cabang, dan data master.
                    </p>
                </div>
                
                <div class="flex items-center gap-2 font-semibold text-primary dark:text-blue-400">
                    <span>Masuk Dashboard</span>
                    <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>

            <!-- POS Portal -->
            <a href="/pos" class="group relative bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-700/60 shadow-xl shadow-slate-100/50 dark:shadow-none hover:shadow-2xl hover:shadow-primary/5 dark:hover:border-primary/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute -right-16 -bottom-16 w-44 h-44 rounded-full bg-primary/5 group-hover:scale-125 transition-transform duration-500"></div>
                
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                        <i class="ph-bold ph-shopping-bag-open text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-950 dark:text-white mb-3 group-hover:text-primary dark:group-hover:text-blue-400 transition-colors">Sistem Kasir POS</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-8">
                        Aplikasi penjualan kasir ritel modern dan cepat. Proses checkout transaksi pelanggan, diskon, integrasi pembayaran, dan cetak struk.
                    </p>
                </div>
                
                <div class="flex items-center gap-2 font-semibold text-primary dark:text-blue-400">
                    <span>Masuk Kasir POS</span>
                    <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </div>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-slate-400 dark:text-slate-600">
        &copy; {{ date('Y') }} Diego Music Store & Repair. All rights reserved.
    </footer>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>
