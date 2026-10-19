<?php

namespace App\Filament\Resources\StockOpnamePeriods\Schemas;

use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockOpnamePeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Periode Stock Opname')->schema([
                Select::make('branch_id')
                    ->label('Cabang')
                    ->options(function () {
                        $user = auth()->user();
                        $query = Branch::query()->where('is_active', true)->orderBy('name');
                        if ($user && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
                            $query->whereIn('id', $user->branches()->pluck('branches.id'));
                        } else {
                            $activeBranchId = BranchHelper::getActiveBranchId();
                            if ($activeBranchId) {
                                $query->where('id', $activeBranchId);
                            }
                        }
                        return $query->pluck('name', 'id')->all();
                    })
                    ->default(fn () => BranchHelper::getActiveBranchId())
                    ->required()
                    ->searchable()
                    ->live()
                    ->disabled(fn ($record) => $record !== null)
                    ->rules(['exists:branches,id']),

                DatePicker::make('period')
                    ->label('Bulan Periode')
                    ->displayFormat('F Y')
                    ->default(now()->startOfMonth())
                    ->required()
                    ->native(false)
                    ->disabled(fn ($record) => $record !== null)
                    ->helperText('Tanggal akan dinormalisasi ke hari pertama pada bulan yang dipilih.'),

                TextInput::make('name')
                    ->label('Nama Periode')
                    ->placeholder('Contoh: SO Oktober 2026')
                    ->maxLength(150)
                    ->required()
                    ->default(fn () => 'SO ' . now()->translatedFormat('F Y'))
                    ->disabled(fn ($record) => $record !== null && $record->status !== StockOpnamePeriod::STATUS_DRAFT),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        StockOpnamePeriod::STATUS_DRAFT => 'Draft',
                        StockOpnamePeriod::STATUS_ACTIVE => 'Aktif',
                        StockOpnamePeriod::STATUS_REVIEW => 'Review',
                        StockOpnamePeriod::STATUS_COMPLETED => 'Selesai',
                        StockOpnamePeriod::STATUS_CANCELLED => 'Dibatalkan',
                    ])
                    ->default(StockOpnamePeriod::STATUS_DRAFT)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Status akan dikendalikan oleh alur penugasan dan review.'),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->maxLength(2000),
            ]),

            Section::make('Penugasan Lokasi dan Karyawan')
                ->description('Satu preset lokasi hanya dapat ditugaskan satu kali dalam periode yang sama. Penugasan dapat dirotasi pada periode berikutnya.')
                ->schema([
                    Repeater::make('assignments')
                        ->label('Daftar Penugasan')
                        ->relationship('assignments')
                        ->schema([
                            Select::make('stock_opname_preset_id')
                                ->label('Lokasi')
                                ->options(fn ($get) => StockOpnamePreset::query()
                                    ->where('is_active', true)
                                    ->when($get('../../branch_id'), fn ($query, $branchId) => $query->where('branch_id', $branchId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->required()
                                ->searchable()
                                ->live(),

                            Select::make('assigned_user_id')
                                ->label('Karyawan')
                                ->options(fn () => User::query()
                                    ->where('is_active', true)
                                    ->whereHas('employee', fn ($query) => $query->where('is_active', true))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->required()
                                ->searchable(),

                            Textarea::make('review_notes')
                                ->label('Catatan Penugasan')
                                ->rows(1)
                                ->maxLength(1000),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Penugasan')
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => isset($state['stock_opname_preset_id'])
                            ? (StockOpnamePreset::find($state['stock_opname_preset_id'])?->name ?? 'Penugasan baru')
                            : 'Penugasan baru')
                        ->disabled(fn ($record) => $record !== null && $record->status !== StockOpnamePeriod::STATUS_DRAFT)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }
}
