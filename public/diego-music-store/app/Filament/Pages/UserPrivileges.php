<?php

namespace App\Filament\Pages;

use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPrivileges extends Page implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static \UnitEnum|string|null $navigationGroup = 'Kelola User';

    protected static ?string $navigationLabel = 'Setting Hak Akses User';

    protected static ?string $title = 'Setting Hak Akses User (Role & Privileges)';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected string $view = 'filament.pages.user-privileges';

    public static array $defaultPermissionsGrouped = [
        'POS & Penjualan' => [
            'pos.access' => 'Akses Kasir POS',
            'pos.discount' => 'Beri Diskon Khusus',
            'pos.hold' => 'Simpan Transaksi Gantung',
            'pos.void' => 'Batalkan Transaksi (Void)',
        ],
        'Kas & Keuangan' => [
            'cash.session' => 'Kelola Sesi Kasir',
            'daily_cash.view' => 'Lihat Kas Harian',
            'daily_cash.manage' => 'Input Kas Masuk/Keluar',
            'supplier_payments.manage' => 'Pelunasan Hutang Supplier',
        ],
        'Data Master' => [
            'master.customers' => 'Kelola Data Pelanggan',
            'master.users' => 'Kelola Data User',
            'master.units' => 'Kelola Satuan Barang',
            'master.categories' => 'Kelola Kategori Penjualan',
            'master.payment_methods' => 'Kelola Metode Pembayaran',
        ],
        'Utility & Pengaturan' => [
            'utility.privileges' => 'Setting Hak Akses User',
            'utility.store' => 'Register & Profil Toko',
            'utility.receipt' => 'Setting Struk & Invoice',
            'utility.barcode' => 'Cetak Barcode Produk',
            'utility.backup' => 'Backup Database',
        ],
    ];

    public function mount(): void
    {
        $this->ensurePermissionsExist();
    }

    public function ensurePermissionsExist(): void
    {
        foreach (static::$defaultPermissionsGrouped as $group => $permissions) {
            foreach ($permissions as $permName => $label) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->withCount('permissions'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('permissions_count')
                    ->label('Total Hak Akses')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "{$state} Hak Akses Aktif")
                    ->icon('heroicon-o-key'),
            ])
            ->actions([
                Action::make('editPermissions')
                    ->label('Atur Hak Akses')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->modalHeading(fn (Role $record): string => "Hak Akses: {$record->name}")
                    ->modalDescription('Centang modul & kewenangan yang diizinkan untuk peran ini.')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Simpan Hak Akses')
                    ->fillForm(function (Role $record): array {
                        $assigned = $record->permissions->pluck('name')->toArray();

                        $formData = [];
                        foreach (static::$defaultPermissionsGrouped as $group => $permissions) {
                            foreach ($permissions as $permKey => $label) {
                                $formData[$permKey] = in_array($permKey, $assigned);
                            }
                        }

                        return [
                            'permissions' => $formData,
                        ];
                    })
                    ->form(function (): array {
                        $sections = [];

                        foreach (static::$defaultPermissionsGrouped as $groupName => $permissions) {
                            $fields = [];
                            foreach ($permissions as $permKey => $label) {
                                $fields[] = Checkbox::make("permissions.{$permKey}")
                                    ->label("{$label} ({$permKey})");
                            }

                            $sections[] = Section::make($groupName)
                                ->schema($fields)
                                ->columns(1)
                                ->compact();
                        }

                        return [
                            Grid::make(2)
                                ->schema($sections),
                        ];
                    })
                    ->action(function (Role $record, array $data): void {
                        $selectedPermissions = [];
                        if (isset($data['permissions']) && is_array($data['permissions'])) {
                            $flatten = function ($array, $prefix = '') use (&$flatten, &$selectedPermissions) {
                                foreach ($array as $key => $value) {
                                    $fullKey = $prefix === '' ? $key : "{$prefix}.{$key}";
                                    if (is_array($value)) {
                                        $flatten($value, $fullKey);
                                    } elseif ($value) {
                                        $selectedPermissions[] = $fullKey;
                                    }
                                }
                            };
                            $flatten($data['permissions']);
                        }

                        $action = new UpdateRolePermissions();
                        $action->execute($record, [
                            'permissions' => $selectedPermissions,
                        ]);

                        Notification::make()
                            ->title('Hak Akses Berhasil Disimpan')
                            ->body("Hak akses untuk Role {$record->name} telah diperbarui.")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (Role $record) => $record->name !== 'Super Admin'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRole')
                ->label('Tambah Role Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Tambah Role Baru')
                ->modalDescription('Masukkan nama peran/jabatan baru dalam sistem.')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->placeholder('Misal: Kasir Utama, Manager Toko, Admin Gudang')
                        ->required()
                        ->unique('roles', 'name'),
                ])
                ->action(function (array $data): void {
                    $action = new CreateRole();
                    $role = $action->execute([
                        'name' => $data['name'],
                    ]);

                    Notification::make()
                        ->title('Role Berhasil Dibuat')
                        ->body("Role {$role->name} telah ditambahkan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
