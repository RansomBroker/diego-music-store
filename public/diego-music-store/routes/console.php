<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:process-scheduled-journals')->daily();

// Tutup Buku Akhir Tahun: Pemindahan Laba Tahun Berjalan ke Laba Ditahan setiap 31 Desember 23:59
Schedule::command('app:year-end-closing')
    ->yearlyOn(12, 31, '23:59')
    ->name('year-end-closing-31-dec');

