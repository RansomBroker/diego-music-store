<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\FocusProductResource;
use App\Helpers\BranchHelper;
use App\Models\FocusProductRecommendation;
use Filament\Widgets\Widget;

class FocusProductRecommendationWidget extends Widget
{
    protected string $view = 'filament.widgets.focus-product-recommendation-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    public function getViewData(): array
    {
        $activeBranchId = BranchHelper::getActiveBranchId();

        $query = FocusProductRecommendation::query()
            ->with(['branch', 'productVariant.product', 'rule'])
            ->where('status', 'PENDING');

        if ($activeBranchId) {
            $query->where('branch_id', $activeBranchId);
        }

        $recommendations = $query->orderBy('score', 'desc')
            ->take(5)
            ->get();

        $totalPending = $query->count();

        return [
            'recommendations' => $recommendations,
            'totalPending' => $totalPending,
            'recommendationsUrl' => FocusProductResource::getUrl('recommendations'),
        ];
    }
}
