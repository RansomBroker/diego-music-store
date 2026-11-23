<?php

namespace App\Filament\Resources\CashTransactions\Pages;

use App\Filament\Resources\CashTransactions\CashTransactionResource;
use App\Actions\CashManagement\CreateCashTransaction as CreateActionClass;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCashTransaction extends CreateRecord
{
    protected static string $resource = CashTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateActionClass::class)->execute($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Transaksi Kas & Bank berhasil dibuat.';
    }
}
