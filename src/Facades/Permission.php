<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;
use JobMetric\Rolix\Services\PermissionManager;

/**
 * @mixin PermissionManager
 *
 * @method static void addPermissionFile(string $context, string $path)
 * @method static array getPermissions(string $context = null, string $view = 'assoc')
 * @method static bool hasPermission(string $context, string $permission)
 * @method static array getContextPermission()
 * @method static array getFlatPermissions(string $context = null)
 * @method static array getLangPermissions(string $context = null)
 * @method static array getFlatLangPermissions(string $context = null)
 * @method static array getAssocPermissions(string $context = null)
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
