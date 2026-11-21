<?php

namespace App\Livewire;

use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use App\Helpers\PermissionHelper;
use App\Models\Branch;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class PosPrivileges extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $roleName = '';

    public bool $accessFo = false;

    public bool $accessBo = false;

    public array $selectedPermissions = [];

    public string $activeSectionTab = 'frontoffice';

    public string $permissionSearch = '';

    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $deletingName = '';

    /**
     * Authorize page access and ensure canonical permissions exist on mount.
     */
    public function mount(): void
    {
        abort_unless(PermissionHelper::canAccessFrontofficeFeature(Auth::user(), 'fo.privileges.view'), 403);
        PermissionHelper::ensureAllPermissionsExist();
    }

    /**
     * Reset pagination when search query changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Switch the active panel tab in the role permission modal.
     */
    public function setSectionTab(string $section): void
    {
        if (in_array($section, ['frontoffice', 'backoffice'], true)) {
            $this->activeSectionTab = $section;
        }
    }

    /**
     * Open modal for creating a new role.
     */
    public function openCreate(): void
    {
        $this->authorizeManage();
        $this->resetForm();
        $this->isEditing = false;
        $this->accessFo = true;
        $this->accessBo = false;
        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing non-protected role's permissions.
     */
    public function openEdit(int $id): void
    {
        $this->authorizeManage();
        $role = Role::with('permissions')->findOrFail($id);
        abort_if(PermissionHelper::isProtectedRoleName($role->name), 403, 'Role Owner bawaan sistem tidak dapat dimodifikasi.');

        $assigned = $role->permissions->pluck('name')->all();

        $this->resetValidation();
        $this->isEditing = true;
        $this->editingId = $role->id;
        $this->roleName = $role->name;
        $this->accessFo = in_array('fo.access', $assigned, true);
        $this->accessBo = in_array('bo.access', $assigned, true);
        $this->selectedPermissions = array_values(array_diff($assigned, ['fo.access', 'bo.access']));
        $this->activeSectionTab = (! $this->accessFo && $this->accessBo) ? 'backoffice' : 'frontoffice';
        $this->permissionSearch = '';
        $this->showModal = true;
    }

    /**
     * Open create modal pre-filled with another role's permissions.
     */
    public function cloneRole(int $id): void
    {
        $this->authorizeManage();
        $source = Role::with('permissions')->findOrFail($id);
        if (PermissionHelper::isProtectedRoleName($source->name) && ! PermissionHelper::isOwner(Auth::user())) {
            abort(403);
        }

        $assigned = $source->permissions->pluck('name')->all();

        $this->resetValidation();
        $this->isEditing = false;
        $this->editingId = null;
        $this->roleName = $source->name . ' Copy';
        $this->accessFo = in_array('fo.access', $assigned, true);
        $this->accessBo = in_array('bo.access', $assigned, true);
        $this->selectedPermissions = array_values(array_diff($assigned, ['fo.access', 'bo.access']));
        $this->activeSectionTab = 'frontoffice';
        $this->permissionSearch = '';
        $this->showModal = true;
    }

    /**
     * Select all feature permissions in the given section and enable its panel gate.
     */
    public function selectSectionPermissions(string $sectionKey): void
    {
        $this->authorizeManage();
        $perms = PermissionHelper::getSectionPermissionNames($sectionKey, false);
        $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $perms)));

        if ($sectionKey === 'frontoffice') {
            $this->accessFo = true;
        } elseif ($sectionKey === 'backoffice') {
            $this->accessBo = true;
        }
    }

    /**
     * Clear all feature permissions in the given section.
     */
    public function deselectSectionPermissions(string $sectionKey): void
    {
        $this->authorizeManage();
        $perms = PermissionHelper::getSectionPermissionNames($sectionKey, false);
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $perms));
    }

    /**
     * Select all permissions in one sidebar/module group.
     */
    public function selectGroupPermissions(string $sectionKey, string $groupKey): void
    {
        $this->authorizeManage();
        $perms = PermissionHelper::getGroupPermissionNames($sectionKey, $groupKey);
        $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $perms)));
    }

    /**
     * Clear all permissions in one sidebar/module group.
     */
    public function deselectGroupPermissions(string $sectionKey, string $groupKey): void
    {
        $this->authorizeManage();
        $perms = PermissionHelper::getGroupPermissionNames($sectionKey, $groupKey);
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $perms));
    }

    /**
     * Toggle all permissions belonging to a single feature card.
     */
    public function toggleFeaturePermissions(string $sectionKey, string $groupKey, string $featureKey): void
    {
        $this->authorizeManage();
        $perms = PermissionHelper::getFeaturePermissionNames($sectionKey, $groupKey, $featureKey);
        if ($perms === []) {
            return;
        }

        $allSelected = empty(array_diff($perms, $this->selectedPermissions));
        $this->selectedPermissions = $allSelected
            ? array_values(array_diff($this->selectedPermissions, $perms))
            : array_values(array_unique(array_merge($this->selectedPermissions, $perms)));
    }

    /**
     * Persist newly created or updated role and its canonical allow-list permissions.
     */
    public function save(CreateRole $createRole, UpdateRolePermissions $updateRolePermissions): void
    {
        $this->authorizeManage();

        $this->roleName = trim($this->roleName);
        abort_if(PermissionHelper::isProtectedRoleName($this->roleName), 403, 'Nama role Owner dilindungi sistem.');

        $this->validate([
            'roleName' => [
                'required',
                'string',
                'max:64',
                Rule::unique('roles', 'name')->ignore($this->isEditing ? $this->editingId : null),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (PermissionHelper::isProtectedRoleName((string) $value)) {
                        $fail('Nama role Owner dilindungi sistem dan tidak dapat digunakan.');
                    }
                },
            ],
        ], [
            'roleName.required' => 'Nama role wajib diisi.',
            'roleName.unique' => 'Nama role tersebut sudah digunakan.',
        ]);

        $finalPermissions = array_values(array_unique(array_filter(array_merge(
            [$this->accessFo ? 'fo.access' : null, $this->accessBo ? 'bo.access' : null],
            array_diff($this->selectedPermissions, ['fo.access', 'bo.access'])
        ))));

        if ($this->isEditing && $this->editingId) {
            $role = Role::findOrFail($this->editingId);
            abort_if(PermissionHelper::isProtectedRoleName($role->name), 403);

            $updateRolePermissions->execute($role, [
                'name' => $this->roleName,
                'permissions' => $finalPermissions,
            ]);
            $msg = "Hak akses role '{$this->roleName}' berhasil diperbarui.";
        } else {
            $createRole->execute([
                'name' => $this->roleName,
                'permissions' => $finalPermissions,
            ]);
            $msg = "Role baru '{$this->roleName}' berhasil ditambahkan.";
        }

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body($msg)
            ->success()
            ->send();

        $this->dispatch('toast', ['type' => 'success', 'title' => 'Berhasil', 'body' => $msg]);
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Open delete confirmation modal for a non-protected role.
     */
    public function confirmDelete(int $id): void
    {
        $this->authorizeManage();
        $role = Role::withCount('users')->findOrFail($id);
        abort_if(PermissionHelper::isProtectedRoleName($role->name), 403);

        $this->deletingId = $role->id;
        $this->deletingName = $role->name;
        $this->showDeleteModal = true;
    }

    /**
     * Permanently delete the selected role if it has no assigned users.
     */
    public function destroy(): void
    {
        $this->authorizeManage();

        if (! $this->deletingId) {
            return;
        }

        $role = Role::findOrFail($this->deletingId);
        abort_if(PermissionHelper::isProtectedRoleName($role->name), 403);

        if ($role->users()->exists()) {
            Notification::make()
                ->title('Role Tidak Dapat Dihapus')
                ->body('Role masih digunakan oleh user. Ubah role user terkait terlebih dahulu.')
                ->danger()
                ->send();

            $this->showDeleteModal = false;

            return;
        }

        $deletedName = $role->name;
        $role->delete();

        Notification::make()
            ->title('Role Dihapus')
            ->body("Role '{$deletedName}' berhasil dihapus.")
            ->success()
            ->send();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    /**
     * Compute live panel access summary for the currently edited role modal.
     */
    public function getPanelSummaryProperty(): array
    {
        $foSectionPerms = PermissionHelper::getSectionPermissionNames('frontoffice', false);
        $boSectionPerms = PermissionHelper::getSectionPermissionNames('backoffice', false);
        $foCount = count(array_intersect($this->selectedPermissions, $foSectionPerms));
        $boCount = count(array_intersect($this->selectedPermissions, $boSectionPerms));

        $label = match (true) {
            $this->accessFo && $this->accessBo => 'FO & BO',
            $this->accessFo => 'Hanya Front Office (FO)',
            $this->accessBo => 'Hanya Backoffice (BO)',
            default => 'Tidak Ada Akses Panel',
        };

        return [
            'label' => $label,
            'fo_count' => $foCount,
            'bo_count' => $boCount,
            'fo_total' => count($foSectionPerms),
            'bo_total' => count($boSectionPerms),
        ];
    }

    /**
     * Return canonical sections filtered by permissionSearch keyword.
     */
    public function getFilteredSectionsProperty(): array
    {
        $sections = PermissionHelper::getSections();
        $query = mb_strtolower(trim($this->permissionSearch));

        if ($query === '') {
            return $sections;
        }

        $filtered = [];
        foreach ($sections as $sectionKey => $section) {
            $groups = [];
            foreach (($section['groups'] ?? []) as $groupKey => $group) {
                $groupMatches = str_contains(mb_strtolower($group['label'] ?? ''), $query);
                $features = [];
                foreach (($group['features'] ?? []) as $featureKey => $feature) {
                    $featureMatches = $groupMatches || str_contains(mb_strtolower($feature['label'] ?? ''), $query);
                    $matchingPerms = [];
                    foreach (($feature['permissions'] ?? []) as $permKey => $permLabel) {
                        if ($featureMatches
                            || str_contains(mb_strtolower($permLabel), $query)
                            || str_contains(mb_strtolower($permKey), $query)) {
                            $matchingPerms[$permKey] = $permLabel;
                        }
                    }
                    if ($matchingPerms !== []) {
                        $features[$featureKey] = array_merge($feature, ['permissions' => $matchingPerms]);
                    }
                }
                if ($features !== []) {
                    $groups[$groupKey] = array_merge($group, ['features' => $features]);
                }
            }
            $filtered[$sectionKey] = array_merge($section, ['groups' => $groups]);
        }

        return $filtered;
    }

    /**
     * Render paginated role privilege rows.
     */
    public function render()
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->when(trim($this->search) !== '', function ($query): void {
                $query->where('name', 'like', '%' . trim($this->search) . '%');
            })
            ->orderByRaw("CASE WHEN LOWER(name) = 'owner' THEN 0 ELSE 1 END, name ASC")
            ->paginate(15);

        $branchId = session('pos_active_branch_id')
            ?? Auth::user()?->branches()->where('branches.is_active', true)->value('branches.id');
        $branch = $branchId ? Branch::find($branchId) : null;
        $selectedLogoUrl = ($branch && ! empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-privileges', [
            'roles' => $roles,
            'selectedLogoUrl' => $selectedLogoUrl,
            'canManagePrivileges' => PermissionHelper::can(Auth::user(), 'fo.privileges.manage'),
            'filteredSections' => $this->filteredSections,
            'panelSummary' => $this->panelSummary,
        ])->layout('layouts.pos', ['title' => 'Setting Privilege User — POS']);
    }

    /**
     * Ensure current user has permission to mutate roles in Front Office.
     */
    private function authorizeManage(): void
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.privileges.manage'), 403);
    }

    /**
     * Reset role modal properties to initial defaults.
     */
    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->roleName = '';
        $this->accessFo = false;
        $this->accessBo = false;
        $this->selectedPermissions = [];
        $this->activeSectionTab = 'frontoffice';
        $this->permissionSearch = '';
    }
}
