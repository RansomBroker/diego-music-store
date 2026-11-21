<?php

namespace App\Filament\Pages;

use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use App\Helpers\PermissionHelper;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UserPrivileges extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static \UnitEnum|string|null $navigationGroup = 'Kelola User';

    protected static ?string $navigationLabel = 'Setting Hak Akses User';

    protected static ?string $title = 'Setting Hak Akses User';

    protected static ?string $slug = 'user-privileges';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.user-privileges';

    /**
     * Determine whether the authenticated user can view the privileges page.
     */
    public static function canAccess(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.user_privileges.view');
    }

    /**
     * Authorize access and synchronize canonical permissions on page mount.
     */
    public function mount(): void
    {
        abort_unless(PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.user_privileges.view'), 403);
        $this->ensurePermissionsExist();
    }

    /**
     * Synchronize all permissions defined in config/permissions.php into the database.
     */
    public function ensurePermissionsExist(): void
    {
        PermissionHelper::ensureAllPermissionsExist();
    }

    /**
     * Check whether the current user may create, clone, edit, or delete roles.
     */
    protected function canManagePrivileges(): bool
    {
        return PermissionHelper::can(auth()->user(), 'bo.user_privileges.manage');
    }

    /**
     * Return a Filament rule-factory closure that forbids reserved Owner role names.
     */
    protected function protectedRoleNameRule(): Closure
    {
        return fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
            if (PermissionHelper::isProtectedRoleName((string) $value)) {
                $fail('Nama role Owner dilindungi sistem dan tidak dapat digunakan.');
            }
        };
    }

    /**
     * Define page header actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRole')
                ->label('Tambah Role Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->visible(fn (): bool => $this->canManagePrivileges())
                ->modalHeading('Buat Role Pengguna Baru')
                ->modalDescription('Tentukan nama role serta panel utama yang diizinkan, lalu atur izin fitur lebih detail lewat tombol Atur Hak Akses.')
                ->modalWidth('lg')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->placeholder('contoh: Kepala Gudang, Kasir Senior')
                        ->required()
                        ->maxLength(64)
                        ->unique(table: Role::class, column: 'name')
                        ->rules([$this->protectedRoleNameRule()]),
                    Grid::make(2)->schema([
                        Toggle::make('access_fo')
                            ->label('Akses Front Office (FO)')
                            ->helperText('Izinkan login & akses panel Front Office (/pos).')
                            ->default(true),
                        Toggle::make('access_bo')
                            ->label('Akses Backoffice (BO)')
                            ->helperText('Izinkan akses panel Backoffice (/backoffice).')
                            ->default(false),
                    ]),
                ])
                ->action(function (array $data, CreateRole $createRole): void {
                    abort_unless($this->canManagePrivileges(), 403);
                    $name = trim((string) ($data['name'] ?? ''));
                    abort_if(PermissionHelper::isProtectedRoleName($name), 403);

                    $permissions = array_values(array_filter([
                        ! empty($data['access_fo']) ? 'fo.access' : null,
                        ! empty($data['access_bo']) ? 'bo.access' : null,
                    ]));

                    $role = $createRole->execute([
                        'name' => $name,
                        'permissions' => $permissions,
                    ]);

                    Notification::make()
                        ->title("Role '{$role->name}' berhasil dibuat")
                        ->body('Silakan klik "Atur Hak Akses" untuk menentukan menu dan fitur yang aktif.')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Configure the role privileges Filament table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->with('permissions')->withCount('users')->orderByRaw("CASE WHEN LOWER(name) = 'owner' THEN 0 ELSE 1 END, name ASC"))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Role $record): string => PermissionHelper::isProtectedRoleName($record->name) ? 'danger' : 'primary')
                    ->description(fn (Role $record): string => PermissionHelper::isProtectedRoleName($record->name)
                        ? 'Owner sistem — akses penuh seluruh Backoffice & Front Office'
                        : 'Role berbasis allow-list (tercentang = aktif & tampil)'),

                TextColumn::make('panel_access')
                    ->label('Akses Panel')
                    ->state(fn (Role $record): string => PermissionHelper::resolveRoleAccessSummary($record)['access_label'])
                    ->badge()
                    ->color(fn (Role $record): string => PermissionHelper::resolveRoleAccessSummary($record)['access_color']),

                TextColumn::make('fo_count')
                    ->label('Fitur FO Aktif')
                    ->state(function (Role $record): string {
                        $summary = PermissionHelper::resolveRoleAccessSummary($record);
                        $total = count(PermissionHelper::getSectionPermissionNames('frontoffice', false));

                        return "{$summary['fo_count']} / {$total}";
                    })
                    ->badge()
                    ->color(fn (Role $record): string => PermissionHelper::resolveRoleAccessSummary($record)['has_fo'] ? 'info' : 'gray'),

                TextColumn::make('bo_count')
                    ->label('Fitur BO Aktif')
                    ->state(function (Role $record): string {
                        $summary = PermissionHelper::resolveRoleAccessSummary($record);
                        $total = count(PermissionHelper::getSectionPermissionNames('backoffice', false));

                        return "{$summary['bo_count']} / {$total}";
                    })
                    ->badge()
                    ->color(fn (Role $record): string => PermissionHelper::resolveRoleAccessSummary($record)['has_bo'] ? 'warning' : 'gray'),

                TextColumn::make('users_count')
                    ->label('Total User')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->actions([
                Action::make('editPermissions')
                    ->label('Atur Hak Akses')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (Role $record): bool => $this->canManagePrivileges() && ! PermissionHelper::isProtectedRoleName($record->name))
                    ->modalHeading(fn (Role $record): string => "Pengaturan Hak Akses: {$record->name}")
                    ->modalDescription('Centang untuk mengaktifkan menu & fitur. Item yang tidak dicentang akan disembunyikan dan diblokir (403).')
                    ->modalWidth('7xl')
                    ->fillForm(fn (Role $record): array => $this->buildRoleFormData($record))
                    ->form(fn (Role $record): array => $this->buildPermissionFormSchema($record))
                    ->action(function (array $data, Role $record, UpdateRolePermissions $updateAction): void {
                        abort_unless($this->canManagePrivileges(), 403);
                        abort_if(PermissionHelper::isProtectedRoleName($record->name), 403);

                        $permissions = $this->extractPermissionsFromFormData($data);
                        $newName = trim((string) ($data['role_name'] ?? $record->name));
                        abort_if(PermissionHelper::isProtectedRoleName($newName), 403);

                        $updateAction->execute($record, [
                            'name' => $newName,
                            'permissions' => $permissions,
                        ]);

                        Notification::make()
                            ->title("Hak akses role '{$newName}' diperbarui")
                            ->body(count($permissions) . ' izin aktif tersimpan.')
                            ->success()
                            ->send();
                    }),

                Action::make('cloneRole')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(fn (Role $record): bool => $this->canManagePrivileges()
                        && (! PermissionHelper::isProtectedRoleName($record->name) || PermissionHelper::isOwner()))
                    ->modalHeading(fn (Role $record): string => "Duplikat Role '{$record->name}'")
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Role Baru')
                            ->placeholder('contoh: Kasir Malam')
                            ->required()
                            ->maxLength(64)
                            ->unique(table: Role::class, column: 'name')
                            ->rules([$this->protectedRoleNameRule()]),
                    ])
                    ->action(function (array $data, Role $record, CreateRole $createRole): void {
                        abort_unless($this->canManagePrivileges(), 403);
                        if (PermissionHelper::isProtectedRoleName($record->name) && ! PermissionHelper::isOwner()) {
                            abort(403);
                        }

                        $newName = trim((string) ($data['name'] ?? ''));
                        abort_if(PermissionHelper::isProtectedRoleName($newName), 403);

                        $role = $createRole->execute([
                            'name' => $newName,
                            'permissions' => $record->permissions()->pluck('name')->all(),
                        ]);

                        Notification::make()
                            ->title("Role '{$role->name}' berhasil diduplikat dari '{$record->name}'")
                            ->success()
                            ->send();
                    }),

                Action::make('deleteRole')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Role $record): string => "Hapus Role '{$record->name}'?")
                    ->modalDescription('Role hanya dapat dihapus jika tidak lagi terpasang ke user manapun.')
                    ->visible(fn (Role $record): bool => $this->canManagePrivileges()
                        && ! PermissionHelper::isProtectedRoleName($record->name)
                        && (int) $record->users_count === 0)
                    ->action(function (Role $record): void {
                        abort_unless($this->canManagePrivileges(), 403);
                        abort_if(PermissionHelper::isProtectedRoleName($record->name), 403);

                        if ($record->users()->exists()) {
                            Notification::make()
                                ->title('Role gagal dihapus')
                                ->body('Role masih dipakai oleh user aktif. Lepaskan role dari user terlebih dahulu.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $deletedName = $record->name;
                        $record->delete();

                        Notification::make()
                            ->title("Role '{$deletedName}' berhasil dihapus")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Build initial form state for the editPermissions modal.
     */
    protected function buildRoleFormData(Role $record): array
    {
        $assigned = $record->permissions()->pluck('name')->all();
        $permGroups = [];

        foreach (PermissionHelper::getSections() as $sectionKey => $section) {
            foreach (($section['groups'] ?? []) as $groupKey => $group) {
                foreach (($group['features'] ?? []) as $featureKey => $feature) {
                    $fieldKey = "{$sectionKey}__{$groupKey}__{$featureKey}";
                    $featurePerms = array_keys($feature['permissions'] ?? []);
                    $permGroups[$fieldKey] = array_values(array_intersect($featurePerms, $assigned));
                }
            }
        }

        return [
            'role_name' => $record->name,
            'access_fo' => in_array('fo.access', $assigned, true),
            'access_bo' => in_array('bo.access', $assigned, true),
            'perm_groups' => $permGroups,
        ];
    }

    /**
     * Build the two-section (Front Office & Backoffice) form schema.
     */
    protected function buildPermissionFormSchema(Role $record): array
    {
        $tabs = [];

        foreach (PermissionHelper::getSections() as $sectionKey => $section) {
            $sectionFeatureTotal = count(PermissionHelper::getSectionPermissionNames($sectionKey, false));

            $groupSections = [];
            foreach (($section['groups'] ?? []) as $groupKey => $group) {
                $featureCheckboxes = [];
                foreach (($group['features'] ?? []) as $featureKey => $feature) {
                    $fieldKey = "{$sectionKey}__{$groupKey}__{$featureKey}";
                    $featureCheckboxes[] = CheckboxList::make("perm_groups.{$fieldKey}")
                        ->label($feature['label'])
                        ->options($feature['permissions'] ?? [])
                        ->bulkToggleable()
                        ->columns(1);
                }

                $groupSections[] = Section::make($group['label'])
                    ->collapsible()
                    ->compact()
                    ->headerActions([
                        Action::make("select_group_{$sectionKey}_{$groupKey}")
                            ->label('Pilih Grup')
                            ->size('xs')
                            ->color('primary')
                            ->action(fn (Set $set) => $this->setGroupCheckboxes($set, $sectionKey, $groupKey, true)),
                        Action::make("deselect_group_{$sectionKey}_{$groupKey}")
                            ->label('Kosongkan')
                            ->size('xs')
                            ->color('gray')
                            ->action(fn (Set $set) => $this->setGroupCheckboxes($set, $sectionKey, $groupKey, false)),
                    ])
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema($featureCheckboxes),
                    ]);
            }

            $tabs[] = Tab::make($sectionKey)
                ->label($section['label'])
                ->badge("{$sectionFeatureTotal} izin")
                ->schema([
                    Section::make($section['access_label'] ?? $section['label'])
                        ->description($section['description'] ?? null)
                        ->compact()
                        ->headerActions([
                            Action::make("select_all_{$sectionKey}")
                                ->label("Pilih Semua {$section['label']}")
                                ->size('xs')
                                ->color('success')
                                ->action(fn (Set $set) => $this->setSectionCheckboxes($set, $sectionKey, true)),
                            Action::make("deselect_all_{$sectionKey}")
                                ->label("Kosongkan {$section['label']}")
                                ->size('xs')
                                ->color('danger')
                                ->action(fn (Set $set) => $this->setSectionCheckboxes($set, $sectionKey, false)),
                        ])
                        ->schema($groupSections),
                ]);
        }

        return [
            Section::make('Identitas Role & Akses Panel')
                ->compact()
                ->schema([
                    TextInput::make('role_name')
                        ->label('Nama Role')
                        ->required()
                        ->maxLength(64)
                        ->rules([
                            $this->protectedRoleNameRule(),
                            fn () => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                $exists = Role::query()
                                    ->where('name', trim((string) $value))
                                    ->where('id', '!=', $record->id)
                                    ->exists();
                                if ($exists) {
                                    $fail('Nama role sudah dipakai role lain.');
                                }
                            },
                        ]),
                    Grid::make(2)->schema([
                        Toggle::make('access_fo')
                            ->label('Akses Front Office (FO)')
                            ->helperText('Centang agar role dapat mengakses panel Front Office (/pos).'),
                        Toggle::make('access_bo')
                            ->label('Akses Backoffice (BO)')
                            ->helperText('Centang agar role dapat mengakses panel Backoffice (/backoffice).'),
                    ]),
                ]),
            Tabs::make('PermissionSections')->tabs($tabs),
        ];
    }

    /**
     * Select or clear all feature CheckboxLists within a section.
     */
    protected function setSectionCheckboxes(Set $set, string $sectionKey, bool $checked): void
    {
        $section = PermissionHelper::getSections()[$sectionKey] ?? null;
        if (! $section) {
            return;
        }

        $panelToggle = $sectionKey === 'frontoffice' ? 'access_fo' : 'access_bo';
        if ($checked) {
            $set($panelToggle, true);
        }

        foreach (($section['groups'] ?? []) as $groupKey => $group) {
            $this->setGroupCheckboxes($set, $sectionKey, $groupKey, $checked);
        }
    }

    /**
     * Select or clear all feature CheckboxLists within one group.
     */
    protected function setGroupCheckboxes(Set $set, string $sectionKey, string $groupKey, bool $checked): void
    {
        $group = PermissionHelper::getSections()[$sectionKey]['groups'][$groupKey] ?? null;
        if (! $group) {
            return;
        }

        foreach (($group['features'] ?? []) as $featureKey => $feature) {
            $fieldPath = "perm_groups.{$sectionKey}__{$groupKey}__{$featureKey}";
            $set($fieldPath, $checked ? array_keys($feature['permissions'] ?? []) : []);
        }
    }

    /**
     * Flatten panel toggles and per-feature checkbox groups into canonical permission strings.
     */
    protected function extractPermissionsFromFormData(array $data): array
    {
        $permissions = [];

        if (! empty($data['access_fo'])) {
            $permissions[] = 'fo.access';
        }

        if (! empty($data['access_bo'])) {
            $permissions[] = 'bo.access';
        }

        foreach (($data['perm_groups'] ?? []) as $selected) {
            if (is_array($selected)) {
                foreach ($selected as $permName) {
                    if (is_string($permName) && $permName !== '') {
                        $permissions[] = $permName;
                    }
                }
            }
        }

        return array_values(array_unique($permissions));
    }
}
