<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Actions\Employee\UpdateEmployee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik')
                    ->searchable()
                    ->sortable()
                    ->label('NIK'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Karyawan'),

                TextColumn::make('user.roles.name')
                    ->badge()
                    ->color('info')
                    ->label('Role Access'),

                TextColumn::make('branch.name')
                    ->sortable()
                    ->label('Cabang'),

                TextColumn::make('phone')
                    ->searchable()
                    ->label('Telepon'),

                TextColumn::make('monthly_off_days_quota')
                    ->numeric()
                    ->sortable()
                    ->label('Quota Off (Bln)'),

                TextColumn::make('basic_salary')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->label('Gaji Pokok'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Aktif'),

                TextColumn::make('join_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tgl Bergabung'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl')
                    ->using(fn (Model $record, array $data): Model => app(UpdateEmployee::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
