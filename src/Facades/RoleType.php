<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Rolix\RoleType
 *
 * @method static \JobMetric\Rolix\RoleType define(string $type)
 * @method static \JobMetric\Rolix\RoleType type(string $type)
 * @method static array get()
 * @method static array getTypes()
 * @method static bool hasType(string $type)
 * @method static void ensureTypeExists(string $type)
 */
class RoleType extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'RoleType';
    }
}
