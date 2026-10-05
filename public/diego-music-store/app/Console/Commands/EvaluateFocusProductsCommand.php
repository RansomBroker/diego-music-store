<?php

namespace App\Console\Commands;

use App\Actions\FocusProduct\EvaluateFocusRules;
use Illuminate\Console\Command;

class EvaluateFocusProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'focus-products:evaluate {branch? : ID cabang spesifik (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan analisis rule engine produk fokus (Dead Stock, Slow Moving, Aging Stock)';

    /**
     * Execute the console command.
     */
    public function handle(EvaluateFocusRules $action): int
    {
        $branchId = $this->argument('branch') ? (int) $this->argument('branch') : null;

        $this->info('Menjalankan evaluasi rule produk fokus...');

        $result = $action->execute($branchId);

        $this->info(sprintf(
            'Evaluasi selesai. Cabang dievaluasi: %d, Rekomendasi dibuat: %d, Rekomendasi diperbarui: %d',
            $result['branches_evaluated'],
            $result['recommendations_generated'],
            $result['recommendations_updated']
        ));

        return Command::SUCCESS;
    }
}
