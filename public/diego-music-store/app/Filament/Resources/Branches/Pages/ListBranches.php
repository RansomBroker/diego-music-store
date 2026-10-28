<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_coa')
                ->label('Sinkronisasi 97 Akun COA Cabang')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi 97 Akun COA Cabang')
                ->modalDescription('Apakah Anda ingin memastikan seluruh cabang memiliki 97 akun COA resmi (lengkap seperti cabang utama) secara otomatis?')
                ->action(function () {
                    $count = \App\Actions\Branch\ProvisionBranchCoaAccounts::executeAll();
                    Notification::make()
                        ->title('Sinkronisasi Berhasil')
                        ->body("97 Akun COA untuk {$count} cabang berhasil diperiksa dan disinkronkan.")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->steps(BranchForm::getWizardSteps())
                ->using(fn (array $data): Model => app(CreateBranch::class)->execute($data)),
        ];
    }
}
