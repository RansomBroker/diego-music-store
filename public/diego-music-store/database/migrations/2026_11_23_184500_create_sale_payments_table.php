<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->string('reference', 150)->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'payment_method_id']);
        });

        // Backfill existing sales into sale_payments so historical transactions stay synced with payment_methods
        $methods = DB::table('payment_methods')->get()->keyBy('id');
        if ($methods->isEmpty()) {
            return;
        }

        $parents = $methods->whereNull('parent_id');
        $childrenByParent = $methods->whereNotNull('parent_id')->groupBy('parent_id');

        $resolveMethodId = function (string $part) use ($methods, $parents, $childrenByParent): ?int {
            $clean = trim(preg_replace('/\s*\((Lunas|Dicicil)[^)]*\)\s*/i', '', $part) ?? $part);
            if ($clean === '') {
                return null;
            }

            $parentName = null;
            $childName = null;

            if (preg_match('/^(.+?)\s*-\s*(.+)$/', $clean, $m)) {
                $parentName = trim($m[1]);
                $childName = trim($m[2]);
            } elseif (preg_match('/^(.+?)\s*\((.+?)\)$/', $clean, $m)) {
                $parentName = trim($m[1]);
                $childName = trim($m[2]);
            }

            if ($parentName !== null && $childName !== null) {
                $parent = $parents->first(fn ($p) => strcasecmp((string) $p->name, $parentName) === 0 || strcasecmp((string) $p->code, $parentName) === 0);
                if ($parent) {
                    $children = $childrenByParent->get($parent->id, collect());
                    // Handle renamed child names like "Transfer BCA" -> "BCA"
                    $strippedChild = trim(preg_replace('/^' . preg_quote((string) $parent->name, '/') . '\s+/i', '', $childName) ?? $childName);
                    $child = $children->first(function ($c) use ($childName, $strippedChild) {
                        return strcasecmp((string) $c->name, $childName) === 0
                            || strcasecmp((string) $c->name, $strippedChild) === 0
                            || strcasecmp((string) $c->code, $childName) === 0;
                    });
                    if ($child) {
                        return (int) $child->id;
                    }
                    return (int) $parent->id;
                }
            }

            // Direct match by name or code (including Cash/Tunai/Kas alias)
            $lower = strtolower($clean);
            if (in_array($lower, ['cash', 'tunai', 'kas'], true)) {
                $cash = $methods->first(fn ($m) => strtolower((string) $m->code) === 'cash');
                if ($cash) {
                    return (int) $cash->id;
                }
            }

            $direct = $methods->first(fn ($m) => strcasecmp((string) $m->name, $clean) === 0 || strcasecmp((string) $m->code, $clean) === 0);
            return $direct ? (int) $direct->id : null;
        };

        $now = now();
        DB::table('sales')->orderBy('id')->chunkById(200, function ($sales) use ($resolveMethodId, $now) {
            $rows = [];
            foreach ($sales as $sale) {
                $parts = array_values(array_filter(array_map('trim', explode('&', (string) ($sale->payment_method ?? '')))));
                if (empty($parts)) {
                    continue;
                }
                $perPartAmount = count($parts) === 1 ? (int) $sale->grand_total : 0;
                foreach ($parts as $part) {
                    $pmId = $resolveMethodId($part);
                    if ($pmId !== null) {
                        $rows[] = [
                            'sale_id' => $sale->id,
                            'payment_method_id' => $pmId,
                            'amount' => $perPartAmount,
                            'reference' => null,
                            'created_at' => $sale->created_at ?? $now,
                            'updated_at' => $sale->updated_at ?? $now,
                        ];
                    }
                }
            }
            if (! empty($rows)) {
                DB::table('sale_payments')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

