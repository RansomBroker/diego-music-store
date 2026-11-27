<?php

namespace App\Filament\Resources\ReceiptSettings\Tables;

use App\Actions\Setting\UpdateReceiptSettings;
use App\Filament\Resources\ReceiptSettings\ReceiptSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReceiptSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('store_display_name')
                    ->label('Judul Utama Struk')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('paper_width')
                    ->label('Ukuran Kertas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '58mm' => 'warning',
                        '80mm' => 'info',
                        'A4' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('header_text')
                    ->label('Header')
                    ->limit(35)
                    ->placeholder('-'),

                TextColumn::make('footer_text')
                    ->label('Footer Struk')
                    ->limit(35)
                    ->placeholder('-'),

                IconColumn::make('show_logo')
                    ->label('Logo')
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('show_tax_details')
                    ->label('PPN')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn (Model $record): bool => ReceiptSettingResource::canEdit($record))
                    ->using(function (Model $record, array $data): Model {
                        return app(UpdateReceiptSettings::class)->execute((int) $record->branch_id, $data);
                    }),

                DeleteAction::make()
                    ->visible(fn (Model $record): bool => ReceiptSettingResource::canDelete($record)),
            ]);
    }
}

