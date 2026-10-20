<?php

namespace App\Filament\Resources\StockOpnamePresets\Tables;

use App\Models\StockOpnamePreset;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StockOpnamePresetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Lokasi')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Cabang')->sortable(),
                TextColumn::make('description')->label('Keterangan')->limit(50)->placeholder('-'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth('lg'),
                EditAction::make()
                    ->modalWidth('lg')
                    ->mutateFormDataUsing(function (array $data, StockOpnamePreset $record): array {
                        $user = auth()->user();
                        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                        if (!$user || !$user->hasRole($allowedRoles)) {
                            abort(403);
                        }

                        $data['branch_id'] = $record->branch_id;

                        return $data;
                    }),
                DeleteAction::make()
                    ->visible(fn (StockOpnamePreset $record) => $record->assignments()->doesntExist()),
            ]);
    }
}
