<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Branch;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Personel')
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK (Nomor Induk Karyawan)')
                            ->placeholder('Opsional (Otomatis jika kosong)')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Lengkap Karyawan'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50)
                            ->label('Nomor Telepon / WA'),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email'),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull()
                            ->label('Alamat Lengkap'),
                    ])
                    ->columnSpan(1),

                Section::make('Kepegawaian & Akses Sistem')
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->default(fn () => \App\Helpers\BranchHelper::getActiveBranchId())
                            ->label('Cabang Utilitas / Home Branch')
                            ->placeholder('Pilih Cabang...')
                            ->nullable(),

                        Select::make('user_id')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query) {
                                    $authUser = auth()->user();
                                    if ($authUser && !$authUser->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin'])) {
                                        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
                                        if ($activeBranchId) {
                                            return $query->whereHas('branches', fn ($q) => $q->where('branches.id', $activeBranchId));
                                        }
                                    }
                                    return $query;
                                }
                            )
                            ->label('Akun Login Sistem (User)')
                            ->placeholder('Pilih Akun User...')
                            ->nullable()
                            ->helperText('Hubungkan karyawan ini ke Akun User jika butuh akses sistem (Kasir, Admin, etc.)'),

                        DatePicker::make('join_date')
                            ->label('Tanggal Bergabung')
                            ->default(now()),

                        TextInput::make('monthly_off_days_quota')
                            ->numeric()
                            ->default(4)
                            ->required()
                            ->label('Kuota Off Day per Bulan')
                            ->helperText('Digunakan oleh Widget Absensi POS Kasir'),

                        TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->label('Gaji Pokok')
                            ->visible(fn () => auth()->check() && (auth()->user()->can('hr.view_salary') || auth()->user()->hasRole(['owner', 'super_admin']))),

                        Toggle::make('is_active')
                            ->label('Status Karyawan Aktif')
                            ->default(true),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
