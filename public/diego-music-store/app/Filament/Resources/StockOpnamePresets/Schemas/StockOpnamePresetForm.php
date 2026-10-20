<?php

namespace App\Filament\Resources\StockOpnamePresets\Schemas;

use App\Helpers\BranchHelper;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class StockOpnamePresetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Select::make('branch_id')
                ->label('Cabang')
                ->options(function () {
                    $user = auth()->user();
                    $query = Branch::query()->where('is_active', true)->orderBy('name');
                    if ($user && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
                        $query->whereIn('id', $user->branches()->pluck('branches.id'));
                    } else {
                        $activeBranchId = BranchHelper::getActiveBranchId();
                        if ($activeBranchId) {
                            $query->where('id', $activeBranchId);
                        }
                    }
                    return $query->pluck('name', 'id')->all();
                })
                ->default(fn () => BranchHelper::getActiveBranchId())
                ->required()
                ->searchable()
                ->disabled(fn ($record) => $record !== null)
                ->rules(['exists:branches,id']),

            TextInput::make('name')
                ->label('Nama Lokasi')
                ->placeholder('Contoh: Lantai 1')
                ->required()
                ->maxLength(100)
                ->unique(
                    \App\Models\StockOpnamePreset::class,
                    'name',
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule, $get, $record) => $rule->where(
                        'branch_id',
                        $get('branch_id') ?: ($record?->branch_id ?: BranchHelper::getActiveBranchId())
                    )
                ),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(2)
                ->maxLength(1000),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
