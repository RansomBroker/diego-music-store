<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sku_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('prefix', 32)->unique();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unsignedTinyInteger('min_digits')->default(4);
            $table->timestamps();
        });

        $normalizePrefix = static function (?string $raw): string {
            $clean = strtoupper(trim((string) $raw));
            if ($clean === '') {
                return '';
            }

            $clean = preg_replace('/[^A-Z0-9_-]+/', '-', $clean) ?? $clean;
            $clean = preg_replace('/-{2,}/', '-', $clean) ?? $clean;

            return trim($clean, '-');
        };

        $branchIdByPrefix = [];
        $branches = DB::table('branches')
            ->whereNotNull('sku_prefix')
            ->orderBy('id')
            ->get(['id', 'sku_prefix']);

        foreach ($branches as $branch) {
            $normalized = $normalizePrefix($branch->sku_prefix);
            if ($normalized !== '' && ! array_key_exists($normalized, $branchIdByPrefix)) {
                $branchIdByPrefix[$normalized] = (int) $branch->id;
            }
        }

        if ($branchIdByPrefix === []) {
            return;
        }

        $now = now();

        foreach ($branchIdByPrefix as $prefix => $branchId) {
            $pattern = '/^' . preg_quote($prefix, '/') . '-(\d+)$/i';
            $maxNumber = 0;

            $skus = DB::table('product_variants')
                ->whereNotNull('sku')
                ->where('sku', 'like', $prefix . '-%')
                ->pluck('sku');

            foreach ($skus as $sku) {
                if (preg_match($pattern, trim((string) $sku), $matches) === 1) {
                    $num = (int) $matches[1];
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                    }
                }
            }

            DB::table('sku_sequences')->updateOrInsert(
                ['prefix' => $prefix],
                [
                    'branch_id' => $branchId,
                    'last_number' => $maxNumber,
                    'min_digits' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_sequences');
    }
};

