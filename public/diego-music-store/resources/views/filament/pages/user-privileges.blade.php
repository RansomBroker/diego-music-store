<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Banner -->
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 dark:bg-gray-800 dark:border-blue-900 flex items-start space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg text-blue-600 dark:text-blue-400">
                <x-heroicon-o-shield-check class="w-6 h-6" />
            </div>
            <div>
                <h4 class="font-semibold text-blue-900 dark:text-blue-200 text-sm">Informasi Manajemen Hak Akses User</h4>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                    Tabel di bawah ini menggunakan antarmuka resmi <strong>Filament Table Builder</strong>. Klik tombol <strong>"Atur Hak Akses"</strong> pada baris peran untuk membuka modal pengaturan hak akses.
                </p>
            </div>
        </div>

        <!-- Filament Native Table Component -->
        <div>
            {{ $this->table }}
        </div>
    </div>

    <!-- Render Native Filament Action Modals -->
    <x-filament-actions::modals />
</x-filament-panels::page>
