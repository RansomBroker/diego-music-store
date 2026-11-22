<?php

namespace App\Actions\Accounting;

use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class ResetOpeningBalances
{
    /**
     * Delete only OpeningBalance bypass journals on the target branch (defaults to Cabang Pusat).
     * Operational journals as well as InitialStock and InitialDebt imports are left untouched.
     *
     * @return array{deleted_entries: int, deleted_items: int}
     */
    public function execute(?int $branchId = null): array
    {
        $targetBranchId = $branchId ?? $this->resolveDefaultBranchId();

        if (! $targetBranchId) {
            return [
                'deleted_entries' => 0,
                'deleted_items' => 0,
            ];
        }

        return DB::transaction(function () use ($targetBranchId): array {
            $entryIds = JournalEntry::query()
                ->where('branch_id', $targetBranchId)
                ->where('reference_type', 'OpeningBalance')
                ->pluck('id');

            if ($entryIds->isEmpty()) {
                return [
                    'deleted_entries' => 0,
                    'deleted_items' => 0,
                ];
            }

            $deletedItems = JournalItem::query()
                ->whereIn('journal_entry_id', $entryIds)
                ->delete();

            $deletedEntries = JournalEntry::query()
                ->whereIn('id', $entryIds)
                ->delete();

            return [
                'deleted_entries' => (int) $deletedEntries,
                'deleted_items' => (int) $deletedItems,
            ];
        });
    }

    private function resolveDefaultBranchId(): ?int
    {
        $branch = Branch::query()
            ->where('name', 'like', '%Pusat%')
            ->where('is_active', true)
            ->orderBy('id')
            ->first()
            ?? Branch::query()->where('is_active', true)->orderBy('id')->first();

        return $branch?->id;
    }
}

