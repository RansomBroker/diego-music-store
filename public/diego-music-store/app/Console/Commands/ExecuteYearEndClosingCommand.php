<?php

namespace App\Console\Commands;

use App\Actions\Accounting\ExecuteYearEndClosing;
use App\Models\Branch;
use Exception;
use Illuminate\Console\Command;

class ExecuteYearEndClosingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:year-end-closing {--year= : Tahun buku yang akan ditutup (default: tahun berjalan)} {--branch= : ID Cabang (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksekusi Tutup Buku Tahunan: Pemindahan saldo Laba Tahun Berjalan ke Laba Ditahan pada 31 Desember 23:59';

    /**
     * Execute the console command.
     */
    public function handle(ExecuteYearEndClosing $action): int
    {
        $year = (int) ($this->option('year') ?: now()->format('Y'));
        $branchId = $this->option('branch') ? (int) $this->option('branch') : null;

        $this->info("Menjalankan Tutup Buku Tahunan untuk Tahun {$year}...");

        try {
            $journal = $action->execute(
                year: $year,
                branchId: $branchId,
                userId: null,
                notes: "Tutup Buku Akhir Tahun {$year} (Otomatis 31 Desember 23:59): Pemindahan Laba Tahun Berjalan ke Laba Ditahan"
            );

            if ($journal) {
                $this->info("✓ Tutup buku tahun {$year} berhasil. Jurnal Penutup #{$journal->entry_no} telah diterbitkan.");
            } else {
                $this->comment("i Tidak ada saldo Laba Tahun Berjalan pada tahun {$year} yang perlu dipindahkan.");
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Gagal menjalankan tutup buku tahun {$year}: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
