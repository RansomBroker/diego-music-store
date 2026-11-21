<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-bold">Prinsip Allow-List Hak Akses (FO &amp; BO)</p>
                    <p class="mt-0.5 text-xs opacity-90">
                        Menu atau fitur hanya aktif dan tampil bila <strong>Akses Panel</strong> (FO / BO) serta izin fitur terkait dicentang.
                        Item yang tidak dicentang otomatis disembunyikan dari sidebar &amp; tombol UI serta diblokir di server. Role <strong>Owner</strong> memiliki akses penuh permanen.
                    </p>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
