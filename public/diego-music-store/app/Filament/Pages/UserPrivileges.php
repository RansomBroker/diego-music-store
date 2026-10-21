<?php

namespace App\Filament\Pages;

use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPrivileges extends Page implements HasActions, HasForms, HasTable
{
    public function boot(): void
    {
        // Only the primary owner may change global role permissions.
        abort_unless(auth()->user()?->hasRole(['owner', 'Owner']), 403);
    }

    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static \UnitEnum|string|null $navigationGroup = 'Kelola User';

    protected static ?string $navigationLabel = 'Setting Hak Akses User';

    protected static ?string $title = 'Setting Hak Akses User (Role & Privileges)';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected string $view = 'filament.pages.user-privileges';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole(['owner', 'Owner', 'super_admin', 'Super Admin']);
    }

    /**
     * Cache static default permissions untuk kompatibilitas ke belakang.
     */
    public static array $defaultPermissionsGrouped = [];

    /**
     * Ambil pemetaan modul & hak akses dari konfigurasi terpusat config/permissions.php
     *
     * @return array<string, array<string, string>>
     */
    public static function getPermissionsGrouped(): array
    {
        if (empty(static::$defaultPermissionsGrouped)) {
            static::$defaultPermissionsGrouped = config('permissions.modules', []);
        }

        return static::$defaultPermissionsGrouped;
    }

    public function mount(): void
    {
        $this->ensurePermissionsExist();
    }

    public function ensurePermissionsExist(): void
    {
        foreach (static::getPermissionsGrouped() as $group => $permissions) {
            foreach ($permissions as $permName => $label) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->withCount(['permissions', 'users']))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Role $record): ?string => in_array(strtolower($record->name), ['owner', 'super_admin', 'super admin'], true) ? 'Role Bawaan Sistem (Akses Penuh)' : 'Role Kustom Toko'),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('users_count')
                    ->label('Jumlah Pengguna')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => "{$state} User")
                    ->icon('heroicon-o-users'),

                TextColumn::make('permissions_count')
                    ->label('Total Hak Akses')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "{$state} Hak Akses")
                    ->icon('heroicon-o-key'),
            ])
            ->actions([
                Action::make('editPermissions')
                    ->visible(fn (Role $record): bool => ! in_array(strtolower($record->name), ['owner', 'super_admin', 'super admin'], true))
                    ->label('Atur Hak Akses')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->modalHeading(fn (Role $record): string => "Konfigurasi Hak Akses: {$record->name}")
                    ->modalDescription('Pilih modul dan kewenangan yang diizinkan untuk peran ini. Anda dapat berpindah tab modul dan menggunakan tombol "Pilih Semua" per modul untuk mempercepat konfigurasi.')
                    ->modalWidth('6xl')
                    ->modalSubmitActionLabel('Simpan Hak Akses')
                    ->fillForm(function (Role $record): array {
                        $assigned = $record->permissions->pluck('name')->toArray();

                        $formData = [];
                        foreach (static::getPermissionsGrouped() as $group => $permissions) {
                            foreach ($permissions as $permKey => $label) {
                                data_set($formData, "permissions.{$permKey}", in_array($permKey, $assigned));
                            }
                        }

                        return $formData;
                    })
                    ->form(function (): array {
                        $grouped = static::getPermissionsGrouped();
                        $tabDefinitions = [
                            [
                                'label' => 'Kasir & Penjualan B2B',
                                'icon' => 'heroicon-o-shopping-cart',
                                'groups' => [
                                    'POS & Kasir Toko' => $grouped['POS & Kasir Toko'] ?? [],
                                    'Penjualan & Piutang (Sales & AR)' => $grouped['Penjualan & Piutang (Sales & AR)'] ?? [],
                                ],
                            ],
                            [
                                'label' => 'Pengadaan & Stok Gudang',
                                'icon' => 'heroicon-o-archive-box',
                                'groups' => [
                                    'Pembelian & Hutang (Purchasing & AP)' => $grouped['Pembelian & Hutang (Purchasing & AP)'] ?? [],
                                    'Persediaan & Pergudangan (Inventory)' => $grouped['Persediaan & Pergudangan (Inventory)'] ?? [],
                                ],
                            ],
                            [
                                'label' => 'Akuntansi & SDM',
                                'icon' => 'heroicon-o-banknotes',
                                'groups' => [
                                    'Akuntansi & Keuangan (GL & Finance)' => $grouped['Akuntansi & Keuangan (GL & Finance)'] ?? [],
                                    'SDM & Penggajian (HRM & Payroll)' => $grouped['SDM & Penggajian (HRM & Payroll)'] ?? [],
                                ],
                            ],
                            [
                                'label' => 'Laporan & Pengaturan',
                                'icon' => 'heroicon-o-document-chart-bar',
                                'groups' => [
                                    'Laporan Finansial & Bisnis (Reports)' => $grouped['Laporan Finansial & Bisnis (Reports)'] ?? [],
                                    'Data Master & Pengaturan Sistem' => $grouped['Data Master & Pengaturan Sistem'] ?? [],
                                ],
                            ],
                        ];

                        $tabs = [];
                        $idx = 0;

                        foreach ($tabDefinitions as $tabDef) {
                            $tabSections = [];

                            foreach ($tabDef['groups'] as $groupName => $permissions) {
                                $idx++;
                                $fields = [];
                                foreach ($permissions as $permKey => $label) {
                                    $fields[] = Checkbox::make("permissions.{$permKey}")
                                        ->label($label)
                                        ->helperText("ID: {$permKey}");
                                }

                                $currentPerms = $permissions;
                                $tabSections[] = Section::make($groupName)
                                    ->headerActions([
                                        Action::make("selectAll_{$idx}")
                                            ->label('Pilih Semua')
                                            ->icon('heroicon-o-check')
                                            ->color('primary')
                                            ->size('xs')
                                            ->action(function (Set $set) use ($currentPerms) {
                                                foreach ($currentPerms as $permKey => $label) {
                                                    $set("permissions.{$permKey}", true);
                                                }
                                            }),
                                        Action::make("deselectAll_{$idx}")
                                            ->label('Batal Semua')
                                            ->icon('heroicon-o-x-mark')
                                            ->color('gray')
                                            ->size('xs')
                                            ->action(function (Set $set) use ($currentPerms) {
                                                foreach ($currentPerms as $permKey => $label) {
                                                    $set("permissions.{$permKey}", false);
                                                }
                                            }),
                                    ])
                                    ->schema($fields)
                                    ->columns(2)
                                    ->compact();
                            }

                            $tabs[] = Tab::make($tabDef['label'])
                                ->icon($tabDef['icon'])
                                ->schema($tabSections);
                        }

                        return [
                            Actions::make([
                                Action::make('checkAllGlobal')
                                    ->label('Pilih Semua Hak Akses (Akses Penuh)')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success')
                                    ->size('sm')
                                    ->action(function (Set $set) {
                                        foreach (static::getPermissionsGrouped() as $group => $perms) {
                                            foreach ($perms as $permKey => $label) {
                                                $set("permissions.{$permKey}", true);
                                            }
                                        }
                                    }),
                                Action::make('uncheckAllGlobal')
                                    ->label('Kosongkan Semua Hak Akses')
                                    ->icon('heroicon-o-x-circle')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Set $set) {
                                        foreach (static::getPermissionsGrouped() as $group => $perms) {
                                            foreach ($perms as $permKey => $label) {
                                                $set("permissions.{$permKey}", false);
                                            }
                                        }
                                    }),
                            ])->alignment(Alignment::End),

                            Tabs::make('PrivilegeTabs')
                                ->tabs($tabs)
                                ->contained(false),
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

                Action::make('cloneRole')
                    ->visible(fn (Role $record): bool => ! in_array(strtolower($record->name), ['owner', 'super_admin', 'super admin'], true))
                    ->label('Duplikasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->modalHeading(fn (Role $record): string => "Duplikasi Role: {$record->name}")
                    ->modalDescription('Buat peran baru dengan menyalin seluruh hak akses peran ini.')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Role Baru')
                            ->placeholder('Misal: Supervisor Kasir, Staf Audit')
                            ->required()
                            ->unique('roles', 'name')
                            ->rules(['not_in:owner,Owner,OWNER,super_admin,Super Admin,super admin,Super_Admin,admin,Admin,ADMIN']),
                    ])
                    ->action(function (Role $record, array $data): void {
                        $action = new CreateRole();
                        $newRole = $action->execute([
                            'name' => $data['name'],
                            'permissions' => $record->permissions->pluck('name')->toArray(),
                        ]);

                        Notification::make()
                            ->title('Role Berhasil Diduplikasi')
                            ->body("Role {$newRole->name} telah dibuat dengan {$record->permissions->count()} hak akses.")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (Role $record): bool => ! in_array(strtolower($record->name), ['owner', 'super_admin', 'super admin'], true)),
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
                        ->unique('roles', 'name')
                        ->rules(['not_in:owner,Owner,OWNER,super_admin,Super Admin,super admin,Super_Admin,admin,Admin,ADMIN']),
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
