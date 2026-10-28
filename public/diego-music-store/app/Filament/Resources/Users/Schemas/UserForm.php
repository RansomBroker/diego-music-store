<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->helperText('Format foto profil (JPG/PNG). Maks 2MB.')
                    ->alignCenter(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Full Name'),

                TextInput::make('username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Username'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Email Address'),

                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255)
                    ->label('Password'),

                Toggle::make('is_active')
                    ->label('Active Status')
                    ->default(true),

                Select::make('branches')
                    ->multiple()
                    ->relationship(
                        name: 'branches',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                            $authUser = auth()->user();
                            if ($authUser && !$authUser->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin'])) {
                                $userBranchIds = $authUser->branches()->pluck('branches.id');
                                return $query->whereIn('branches.id', $userBranchIds);
                            }
                            return $query;
                        }
                    )
                    ->default(fn () => array_filter([\App\Helpers\BranchHelper::getActiveBranchId()]))
                    ->preload()
                    ->label('Assigned Branches'),

                Select::make('roles')
                    ->multiple()
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                            $authUser = auth()->user();
                            if ($authUser && !$authUser->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin'])) {
                                return $query->whereNotIn('name', ['owner', 'Owner', 'super_admin', 'Super Admin']);
                            }
                            return $query;
                        }
                    )
                    ->preload()
                    ->disabled(fn (): bool => ! auth()->user()?->hasRole(['owner', 'Owner', 'super_admin', 'Super Admin']))
                    ->helperText('Hanya Owner / Super Admin yang dapat mengubah role pengguna.')
                    ->label('Roles'),
            ]);
    }
}
