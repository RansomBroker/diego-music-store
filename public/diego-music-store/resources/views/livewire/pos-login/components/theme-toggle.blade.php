<!-- Theme Switcher -->
<div class="mt-6 flex justify-center">
    <button onclick="toggleTheme()" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 shadow-md border border-slate-200/50 dark:border-slate-700/50 transition-colors" title="Ubah Tema">
        <i class="ph-bold ph-sun dark:hidden text-lg"></i>
        <i class="ph-bold ph-moon hidden dark:block text-lg"></i>
    </button>
</div>

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
