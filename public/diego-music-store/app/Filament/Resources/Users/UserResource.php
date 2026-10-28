<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getNavigationGroup(): ?string
    {
        return 'Kelola User';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $authUser = auth()->user();

        // Owner dan Super Admin memiliki akses global melihat seluruh pengguna lintas cabang
        if ($authUser && $authUser->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin'])) {
            return $query;
        }

        // Pengguna non-owner (misal: Admin Cabang) HANYA boleh melihat user cabang mereka dan BUKAN akun Owner
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            return $query->whereHas('branches', fn ($q) => $q->where('branches.id', $activeBranchId))
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['owner', 'Owner', 'super_admin', 'Super Admin']));
        }

        if ($authUser) {
            $userBranchIds = $authUser->branches()->pluck('branches.id');
            return $query->whereHas('branches', fn ($q) => $q->whereIn('branches.id', $userBranchIds))
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['owner', 'Owner', 'super_admin', 'Super Admin']));
        }

        return $query;
    }
}
