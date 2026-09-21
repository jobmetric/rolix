<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;
use JobMetric\Rolix\Services\PermissionManager;

/**
 * @mixin PermissionManager
 *
 * @method static void addPermissionFile(string $context, string $path, string|null $model = null)
 * @method static array getPermissions(string|null $context = null, string $view = 'assoc', string|null $model = null)
 * @method static bool hasPermission(string $context, string $permission, string|null $model = null)
 * @method static array getContextPermission(string|null $model = null)
 * @method static array getFlatPermissions(string|null $context = null, string|null $model = null)
 * @method static array getLangPermissions(string|null $context = null, string|null $model = null)
 * @method static array getFlatLangPermissions(string|null $context = null, string|null $model = null)
 * @method static array getAssocPermissions(string|null $context = null, string|null $model = null)
 */
class Permission extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'rolix.permission';
    }
}
