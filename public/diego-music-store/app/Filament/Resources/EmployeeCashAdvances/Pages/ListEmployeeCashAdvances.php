<?php

namespace App\Filament\Resources\EmployeeCashAdvances\Pages;

use App\Actions\CashAdvance\CreateCashAdvanceRequest;
use App\Filament\Resources\EmployeeCashAdvances\EmployeeCashAdvanceResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeCashAdvances extends ListRecords
{
    protected static string $resource = EmployeeCashAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_advance')
                ->label('Ajukan Kasbon Karyawan')
                ->color('primary')
                ->icon('heroicon-o-plus')
                ->form([
                    Forms\Components\Select::make('employee_id')
                        ->label('Karyawan')
                        ->options(\App\Models\Employee::where('is_active', true)->pluck('name', 'id')->toArray())
                        ->required()
                        ->searchable(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Nominal Kasbon (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),

                    Forms\Components\TextInput::make('tenor_months')
                        ->label('Tenor Cicilan (Bulan)')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(12)
                        ->required(),

                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan / Keperluan Kasbon'),
                ])
                ->action(function (array $data) {
                    try {
                        $advance = app(CreateCashAdvanceRequest::class)->execute(
                            (int) $data['employee_id'],
                            (float) $data['amount'],
                            (int) ($data['tenor_months'] ?? 1),
                            $data['reason'] ?? null,
                            auth()->user()
                        );

                        Notification::make()
                            ->title('Pengajuan Kasbon Berhasil')
                            ->body("Pengajuan kasbon {$advance->advance_number} sebesar Rp " . number_format($advance->amount, 0, ',', '.') . " berhasil dibuat.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Mengajukan Kasbon')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
