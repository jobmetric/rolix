<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;
use JobMetric\Rolix\Services\PermissionManager;

/**
 * @mixin PermissionManager
 *
 * @method static void addPermissionFile(string $context, string $path)
 * @method static array getPermissions(string $context)
 * @method static array getAllPermissions()
 * @method static bool hasPermission(string $context, string $permission)
 * @method static void clearPermissions()
 * @method static array getContextsWithPermissions()
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
