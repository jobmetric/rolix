<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Rolix\Support\RoleTypeRegistry
 *
 * @method static \JobMetric\Rolix\Support\RoleTypeRegistry register(string $type, array $options = [])
 * @method static \JobMetric\Rolix\Support\RoleTypeRegistry unregister(string $type)
 * @method static bool has(string $type)
 * @method static array<string, mixed>|null get(string $type)
 * @method static array<string, mixed> all()
 * @method static array<int, string> values()
 * @method static mixed getOption(string $type, string $key, mixed $default = null)
 * @method static string|null getModel(string $type)
 * @method static bool isSystem(string $type)
 * @method static \JobMetric\Rolix\Support\RoleTypeRegistry ensure(string $type)
 * @method static \JobMetric\Rolix\Support\RoleTypeRegistry clear()
 */
class RoleTypeRegistry extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     *
     * This accessor must match the binding defined in the package service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'RoleTypeRegistry';
    }
}
