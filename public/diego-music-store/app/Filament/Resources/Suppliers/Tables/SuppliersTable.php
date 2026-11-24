<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Actions\Supplier\UpdateSupplier;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Supplier / Vendor'),

                TextColumn::make('status')
                    ->label('Jenis / Status')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        Supplier::STATUS_VENDOR => 'warning',
                        Supplier::STATUS_SUPPLIER => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('contact_person')
                    ->searchable()
                    ->label('Contact Person'),

                TextColumn::make('phone')
                    ->label('Telepon'),

                TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                TextColumn::make('bank_name')
                    ->label('Bank'),

                TextColumn::make('bank_account_number')
                    ->label('No. Rekening'),

                TextColumn::make('outstanding_debt')
                    ->money('idr')
                    ->sortable()
                    ->label('Hutang Berjalan'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Jenis / Status')
                    ->options([
                        Supplier::STATUS_SUPPLIER => 'SUPPLIER',
                        Supplier::STATUS_VENDOR => 'VENDOR',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl')
                    ->using(fn (Model $record, array $data): Model => app(UpdateSupplier::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
