<?php

namespace App\Console\Commands;

use App\Actions\FocusProduct\ExpireFocusProducts;
use Illuminate\Console\Command;

class ExpireFocusProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'focus-products:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa dan ubah status produk fokus yang telah melewati batas aktif (active_until) menjadi EXPIRED';

    /**
     * Execute the console command.
     */
    public function handle(ExpireFocusProducts $action): int
    {
        $this->info('Memeriksa produk fokus yang telah kadaluarsa...');

        $count = $action->execute();

        $this->info("Berhasil mengubah {$count} produk fokus menjadi EXPIRED.");

        return Command::SUCCESS;
    }
}
