<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Rolix\Services\Role
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response paginate(int $pageLimit = 15, array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response all(array $filters = [], array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [], string|null $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response store(array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response update(int $id, array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response destroy(int $id, array $with = [])
 * @method static void rebuildPaths(\JobMetric\Rolix\Models\Role $role)
 * @method static void syncRules(\JobMetric\Rolix\Models\Role $role, array|null $rules)
 */
class Role extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'role';
    }
}
