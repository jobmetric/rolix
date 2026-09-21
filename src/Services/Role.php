<?php

namespace JobMetric\Rolix\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JobMetric\PackageCore\Services\AbstractCrudService;
use JobMetric\Rolix\Events\Role\RoleDeleteEvent;
use JobMetric\Rolix\Events\Role\RoleStoreEvent;
use JobMetric\Rolix\Events\Role\RoleUpdateEvent;
use JobMetric\Rolix\Exceptions\RoleHierarchyException;
use JobMetric\Rolix\Exceptions\RoleIsSuperException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Http\Requests\Role\StoreRoleRequest;
use JobMetric\Rolix\Http\Requests\Role\UpdateRoleRequest;
use JobMetric\Rolix\Http\Resources\RoleResource;
use JobMetric\Rolix\Models\Role as RoleModel;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Models\RoleRule;
use Throwable;

/**
 * CRUD and management service for Role entities.
 *
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Rebuild hierarchical role paths after mutations
 * - Sync conditional role rules
 * - Protect super roles from deletion and demotion
 *
 * @package JobMetric\Rolix
 */
class Role extends AbstractCrudService
{
    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'rolix::base.entity_names.role';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = RoleModel::class;

    /**
     * @var class-string
     */
    protected static string $resourceClass = RoleResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'type',
        'parent_id',
        'name',
        'description',
        'allow',
        'deny',
        'is_default',
        'is_super',
        'ordering',
        'created_at',
        'updated_at',
    ];

    /**
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = RoleStoreEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $updateEventClass = RoleUpdateEvent::class;

    /**
     * @var class-string|null
     */
    protected static ?string $deleteEventClass = RoleDeleteEvent::class;

    /**
     * Mutate/validate payload before create.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldStore(array &$data): void
    {
        $data = dto($data, StoreRoleRequest::class);

        $data['type'] = $data['type'] ?? 'system';
        RoleTypeRegistry::ensure($data['type']);

        $this->normalizePermissions($data);
        $this->assertParentIsValid($data['parent_id'] ?? null, $data['type']);
        $this->assertUniqueSuperPerType($data['type'], ! empty($data['is_super']));
    }

    /**
     * Mutate/validate payload before update.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     * @throws RoleIsSuperException
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var RoleModel $model */
        $data = dto($data, UpdateRoleRequest::class, ['id' => $model->id]);

        if ($model->is_super && array_key_exists('is_super', $data) && ! $data['is_super']) {
            throw new RoleIsSuperException('role_is_super_cannot_demote');
        }

        if (! array_key_exists('type', $data)) {
            $data['type'] = $model->type;
        }

        RoleTypeRegistry::ensure($data['type']);

        if ($model->is_super || ! empty($data['is_super'])) {
            $data['is_super'] = true;
            $data['allow'] = [];
            $data['deny'] = [];
        }
        else {
            $this->normalizePermissions($data);
        }

        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $model->parent_id;
        $this->assertParentIsValid($parentId, $data['type'], $model->id);

        $wantsSuper = $model->is_super || ! empty($data['is_super']);
        if ($wantsSuper && ! $model->is_super) {
            $this->assertUniqueSuperPerType($data['type'], true, $model->id);
        }
    }

    /**
     * After create: paths, rules, default uniqueness.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function afterStore(Model $model, array &$data): void
    {
        /** @var RoleModel $model */
        $this->ensureSingleDefault($model);
        $this->rebuildPaths($model);
        $this->syncRules($model, $data['rules'] ?? []);
    }

    /**
     * After update: paths, rules, default uniqueness.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function afterUpdate(Model $model, array &$data): void
    {
        /** @var RoleModel $model */
        $this->ensureSingleDefault($model);
        $this->rebuildPaths($model);
        $this->syncRules($model, $data['rules'] ?? null);
    }

    /**
     * Prevent deleting a super role.
     *
     * @param Model $model
     *
     * @return void
     * @throws RoleIsSuperException
     */
    protected function beforeDestroy(Model $model): void
    {
        /** @var RoleModel $model */
        if ($model->is_super) {
            throw new RoleIsSuperException('role_is_super_protected');
        }
    }

    /**
     * Normalize allow/deny for super roles and defaults.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function normalizePermissions(array &$data): void
    {
        if (! empty($data['is_super'])) {
            $data['allow'] = [];
            $data['deny'] = [];

            return;
        }

        $data['allow'] = array_values(array_unique($data['allow'] ?? []));
        $data['deny'] = array_values(array_unique($data['deny'] ?? []));
    }

    /**
     * Ensure parent exists, shares type, and does not create a cycle.
     *
     * @param int|null $parentId
     * @param string $type
     * @param int|null $roleId
     *
     * @return void
     * @throws RoleHierarchyException
     */
    protected function assertParentIsValid(?int $parentId, string $type, ?int $roleId = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($roleId !== null && $parentId === $roleId) {
            throw new RoleHierarchyException('role_hierarchy_self_parent');
        }

        $parent = RoleModel::query()->find($parentId);

        if ($parent === null) {
            throw new RoleHierarchyException('role_hierarchy_parent_missing');
        }

        if ($parent->type !== $type) {
            throw new RoleHierarchyException('role_hierarchy_type_mismatch');
        }

        if ($roleId !== null) {
            $createsCycle = RolePath::query()
                ->where('role_id', $parentId)
                ->where('path_id', $roleId)
                ->where('level', '>', 0)
                ->exists();

            if ($createsCycle) {
                throw new RoleHierarchyException('role_hierarchy_cycle');
            }
        }
    }

    /**
     * Keep at most one default role per type.
     *
     * @param RoleModel $role
     *
     * @return void
     */
    protected function ensureSingleDefault(RoleModel $role): void
    {
        if (! $role->is_default) {
            return;
        }

        RoleModel::query()
            ->where('type', $role->type)
            ->where('id', '!=', $role->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    /**
     * Ensure at most one is_super role exists per type.
     *
     * @param string $type
     * @param bool $wantsSuper
     * @param int|null $exceptRoleId
     *
     * @return void
     * @throws RoleIsSuperException
     */
    protected function assertUniqueSuperPerType(string $type, bool $wantsSuper, ?int $exceptRoleId = null): void
    {
        if (! $wantsSuper) {
            return;
        }

        $query = RoleModel::query()->where('type', $type)->where('is_super', true);

        if ($exceptRoleId !== null) {
            $query->where('id', '!=', $exceptRoleId);
        }

        if ($query->exists()) {
            throw new RoleIsSuperException('role_is_super_already_exists');
        }
    }

    /**
     * Rebuild closure-table paths for the role and its descendants.
     *
     * @param RoleModel $role
     *
     * @return void
     */
    public function rebuildPaths(RoleModel $role): void
    {
        RolePath::query()->where('role_id', $role->id)->delete();

        RolePath::query()->create([
            'type'    => $role->type,
            'role_id' => $role->id,
            'path_id' => $role->id,
            'level'   => 0,
        ]);

        if ($role->parent_id) {
            $parentPaths = RolePath::query()->where('role_id', $role->parent_id)->orderBy('level')->get();

            foreach ($parentPaths as $path) {
                RolePath::query()->create([
                    'type'    => $role->type,
                    'role_id' => $role->id,
                    'path_id' => $path->path_id,
                    'level'   => $path->level + 1,
                ]);
            }
        }

        $role->load('children');

        foreach ($role->children as $child) {
            $this->rebuildPaths($child);
        }
    }

    /**
     * Sync role rules. Pass null to leave existing rules unchanged.
     *
     * @param RoleModel $role
     * @param array<int, array{driver: string, payload?: array}|null>|null $rules
     *
     * @return void
     * @throws Throwable
     */
    public function syncRules(RoleModel $role, ?array $rules): void
    {
        if ($rules === null) {
            return;
        }

        DB::transaction(function () use ($role, $rules) {
            RoleRule::query()->where('role_id', $role->id)->delete();

            foreach ($rules as $rule) {
                if (! is_array($rule) || empty($rule['driver'])) {
                    continue;
                }

                RoleRule::query()->create([
                    'role_id' => $role->id,
                    'driver'  => $rule['driver'],
                    'payload' => $rule['payload'] ?? null,
                ]);
            }
        });
    }
}
