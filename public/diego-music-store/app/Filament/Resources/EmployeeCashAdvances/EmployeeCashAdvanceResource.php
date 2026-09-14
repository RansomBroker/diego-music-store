<?php

namespace App\Filament\Resources\EmployeeCashAdvances;

use App\Actions\CashAdvance\ApproveCashAdvance;
use App\Actions\CashAdvance\CreateCashAdvanceRequest;
use App\Actions\CashAdvance\RejectCashAdvance;
use App\Filament\Resources\EmployeeCashAdvances\Pages\ListEmployeeCashAdvances;
use App\Models\EmployeeCashAdvance;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeCashAdvanceResource extends Resource
{
    protected static ?string $model = EmployeeCashAdvance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Kasbon / Cash Advance';

    protected static ?string $pluralModelLabel = 'Kasbon / Cash Advance Karyawan';

    protected static ?string $modelLabel = 'Kasbon Karyawan';

    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->label('Karyawan'),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->label('Nominal Kasbon'),

                Forms\Components\TextInput::make('tenor_months')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(12)
                    ->required()
                    ->label('Tenor Cicilan (Bulan)'),

                Forms\Components\Textarea::make('reason')
                    ->label('Alasan / Keperluan Kasbon')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('advance_number')
                    ->searchable()
                    ->sortable()
                    ->label('No. Ref')
                    ->weight('bold'),

                TextColumn::make('employee.nik')
                    ->searchable()
                    ->label('NIK'),

                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Karyawan')
                    ->weight('bold'),

                TextColumn::make('request_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tgl Pengajuan'),

                TextColumn::make('amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable()
                    ->label('Nominal Kasbon'),

                TextColumn::make('tenor_months')
                    ->badge()
                    ->color('gray')
                    ->suffix(' Bln')
                    ->label('Tenor'),

                TextColumn::make('monthly_installment')
                    ->numeric()
                    ->prefix('Rp ')
                    ->label('Cicilan/Bln'),

                TextColumn::make('paid_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->color('success')
                    ->label('Telah Terpotong'),

                TextColumn::make('remaining_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->color('warning')
                    ->weight('bold')
                    ->label('Sisa Saldo'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'paid_off' => 'success',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'paid_off' => 'Lunas',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Pending',
                    })
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Cabang'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid_off' => 'Lunas',
                    ])
                    ->label('Status'),

                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->label('Karyawan'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        app(ApproveCashAdvance::class)->execute($record->id, auth()->user());
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->label('Alasan Penolakan'),
                    ])
                    ->action(function ($record, array $data) {
                        app(RejectCashAdvance::class)->execute($record->id, auth()->user(), $data['reason'] ?? null);
                    }),

                Action::make('earlyRepayment')
                    ->label('Pelunasan Awal')
                    ->color('info')
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn ($record) => $record->status === 'approved' && $record->remaining_amount > 0)
                    ->form([
                        Forms\Components\TextInput::make('repayment_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(fn ($record) => $record->remaining_amount)
                            ->required()
                            ->label('Nominal Pelunasan'),

                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Tunai Kas Toko',
                                'bank' => 'Transfer Bank Toko',
                            ])
                            ->default('cash')
                            ->required()
                            ->label('Metode Pembayaran'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Pelunasan'),
                    ])
                    ->action(function ($record, array $data) {
                        app(\App\Actions\CashAdvance\SettleCashAdvanceEarly::class)->execute(
                            $record->id,
                            (float) $data['repayment_amount'],
                            $data['payment_method'] ?? 'cash',
                            $data['notes'] ?? null,
                            auth()->user()
                        );
                    }),

                Action::make('cancel')
                    ->label('Batal')
                    ->color('gray')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        app(\App\Actions\CashAdvance\CancelCashAdvanceRequest::class)->execute($record->id, auth()->user());
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeCashAdvances::route('/'),
        ];
    }
}
