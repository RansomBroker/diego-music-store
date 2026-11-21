<?php

namespace App\Filament\Concerns;

use App\Helpers\PermissionHelper;
use Illuminate\Database\Eloquent\Model;

trait HasResourcePermissions
{
    protected static function getViewPermission(): string
    {
        return static::$permissionPrefix . '.view';
    }

    protected static function getCreatePermission(): ?string
    {
        if (property_exists(static::class, 'hasCreatePermission') && ! static::$hasCreatePermission) {
            return null;
        }

        return static::$permissionPrefix . '.create';
    }

    protected static function getEditPermission(): ?string
    {
        if (property_exists(static::class, 'hasEditPermission') && ! static::$hasEditPermission) {
            return null;
        }

        return static::$permissionPrefix . '.edit';
    }

    protected static function getDeletePermission(): ?string
    {
        if (property_exists(static::class, 'hasDeletePermission') && ! static::$hasDeletePermission) {
            return null;
        }

        return static::$permissionPrefix . '.delete';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), static::getViewPermission());
    }

    public static function canViewAny(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), static::getViewPermission());
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        $permission = static::getCreatePermission();

        return $permission !== null
            && PermissionHelper::canAccessBackofficeFeature(auth()->user(), $permission);
    }

    public static function canEdit(Model $record): bool
    {
        $permission = static::getEditPermission();

        return $permission !== null
            && PermissionHelper::canAccessBackofficeFeature(auth()->user(), $permission);
    }

    public static function canDelete(Model $record): bool
    {
        $permission = static::getDeletePermission();

        return $permission !== null
            && PermissionHelper::canAccessBackofficeFeature(auth()->user(), $permission);
    }

    public static function canDeleteAny(): bool
    {
        $permission = static::getDeletePermission();

        return $permission !== null
            && PermissionHelper::canAccessBackofficeFeature(auth()->user(), $permission);
    }
}

