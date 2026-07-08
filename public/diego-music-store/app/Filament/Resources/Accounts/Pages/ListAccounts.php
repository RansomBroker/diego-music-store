<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Actions\Account\CreateAccount;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    public bool $isTreeView = true;

    protected string $view = 'backoffice.accounting.list-accounts';

    public function toggleTreeView(): void
    {
        $this->isTreeView = !$this->isTreeView;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleView')
                ->label(fn() => $this->isTreeView ? 'Tampilan Tabel' : 'Tampilan Pohon (Tree)')
                ->icon(fn() => $this->isTreeView ? 'heroicon-o-table-cells' : 'heroicon-o-list-bullet')
                ->color('gray')
                ->action(fn() => $this->toggleTreeView()),

            CreateAction::make()
                ->modalWidth('md')
                ->using(fn (array $data): Model => app(CreateAccount::class)->execute($data)),
        ];
    }
}
