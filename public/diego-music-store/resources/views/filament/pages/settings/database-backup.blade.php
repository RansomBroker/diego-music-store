<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Banner -->
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 dark:bg-gray-800 dark:border-blue-900 flex items-start space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg text-blue-600 dark:text-blue-400">
                <x-heroicon-o-information-circle class="w-6 h-6" />
            </div>
            <div>
                <h4 class="font-semibold text-blue-900 dark:text-blue-200 text-sm">Informasi Backup Database</h4>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                    File backup dibuat menggunakan Spatie Laravel Backup dan disimpan di direktori terisolasi aplikasi. Disarankan melakukan backup secara berkala sebelum pembaruan sistem.
                </p>
            </div>
        </div>

        <!-- Backup Table Card -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Riwayat File Backup</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Daftar arsip database dan berkas yang telah dibuat</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button wire:click="loadBackups" type="button" class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        Refresh
                    </button>
                </div>
            </div>

            @if(empty($backups))
                <div class="p-12 text-center">
                    <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                        <x-heroicon-o-circle-stack class="w-6 h-6" />
                    </div>
                    <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Belum Ada File Backup</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik tombol "Buat Backup Baru" di atas untuk membuat arsip database pertama Anda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-6 py-3">Nama File</th>
                                <th class="px-6 py-3">Tanggal Dibuat</th>
                                <th class="px-6 py-3">Ukuran File</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-gray-900 dark:text-white flex items-center gap-x-2">
                                        <x-heroicon-o-archive-box class="w-5 h-5 text-primary-500 shrink-0" />
                                        <span>{{ $backup['name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        {{ $backup['date'] }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button wire:click="downloadBackup('{{ $backup['path'] }}')" type="button" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/30 px-2.5 py-1.5 rounded-md transition-colors">
                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                            Download
                                        </button>
                                        <button 
                                            wire:click="deleteBackup('{{ $backup['path'] }}')" 
                                            wire:confirm="Apakah Anda yakin ingin menghapus file backup {{ $backup['name'] }}?"
                                            type="button" 
                                            class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-50 dark:bg-red-900/30 px-2.5 py-1.5 rounded-md transition-colors">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
