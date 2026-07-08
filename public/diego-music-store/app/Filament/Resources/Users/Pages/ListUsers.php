<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\User\CreateUser;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(fn (array $data): Model => app(CreateUser::class)->execute($data)),
        ];
    }
}
