<!-- Toolbar (Filters) -->
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
    <!-- Search Input -->
    <div class="w-full sm:max-w-xs">
        <x-pos.form.input
            model="search"
            :live="true"
            debounce="300ms"
            placeholder="Cari nama cabang atau kota..."
            icon="ph-magnifying-glass"
            size="sm"
        />
    </div>
</div>
