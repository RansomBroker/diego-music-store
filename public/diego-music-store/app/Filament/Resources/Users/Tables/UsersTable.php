<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\User\UpdateUser;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Full Name'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->label('Username'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Email Address'),

                TextColumn::make('branches.name')
                    ->badge()
                    ->label('Branches'),

                TextColumn::make('roles.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'danger',
                        'admin' => 'warning',
                        'cashier' => 'success',
                        'sales' => 'info',
                        'technician' => 'gray',
                        default => 'primary',
                    })
                    ->label('Roles'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['branches'] = $record->branches->pluck('id')->toArray();
                        $data['roles'] = $record->roles->pluck('id')->toArray();
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdateUser::class)->execute($record, $data)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
