<?php

namespace JobMetric\Rolix\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JobMetric\PackageCore\Services\AbstractCrudService;
use JobMetric\Rolix\Events\Role\RoleDeleteEvent;
use JobMetric\Rolix\Events\Role\RoleStoreEvent;
use JobMetric\Rolix\Events\Role\RoleUpdateEvent;
use JobMetric\Rolix\Exceptions\RoleHierarchyException;
use JobMetric\Rolix\Exceptions\RoleIsSuperException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry;
use JobMetric\Rolix\Http\Requests\Role\StoreRoleRequest;
use JobMetric\Rolix\Http\Requests\Role\UpdateRoleRequest;
use JobMetric\Rolix\Http\Resources\RoleResource;
use JobMetric\Rolix\Models\Role as RoleModel;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Models\RoleRule;
use JobMetric\Rolix\Support\ActivityLogger;
use Throwable;

/**
 * CRUD and management service for Role entities.
 *
 * @package JobMetric\Rolix
 */
class Role extends AbstractCrudService
{
    /**
     * @var string
     */
    protected string $entityName = 'rolix::base.entity_names.role';

    /**
     * @var class-string
     */
    protected static string $modelClass = RoleModel::class;

    /**
     * @var class-string
     */
    protected static string $resourceClass = RoleResource::class;

    /**
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
     * Children captured before destroy for path rebuild.
     *
     * @var array<int, int>
     */
    protected array $pendingPathRebuildChildIds = [];

    /**
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
        $this->validateRulesPayload($data['rules'] ?? []);
    }

    /**
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     * @throws Throwable
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

        if (array_key_exists('rules', $data)) {
            $this->validateRulesPayload($data['rules'] ?? []);
        }
    }

    /**
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

        $this->pendingPathRebuildChildIds = $model->children()->pluck('id')->all();

        RoleModel::query()->where('parent_id', $model->id)->update(['parent_id' => null]);
    }

    /**
     * @param Model $model
     *
     * @return void
     */
    protected function afterDestroy(Model $model): void
    {
        foreach ($this->pendingPathRebuildChildIds as $childId) {
            $child = RoleModel::query()->find($childId);

            if ($child !== null) {
                $this->rebuildPaths($child);
            }
        }

        $this->pendingPathRebuildChildIds = [];
    }

    /**
     * @param string $operation
     * @param Model $model
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function afterCommon(string $operation, Model $model, array $data = []): void
    {
        if (in_array($operation, ['store', 'update', 'destroy'], true)) {
            ActivityLogger::log($operation, $model, $data);
        }
    }

    /**
     * OpenCart-style flat hierarchical list with depth and path_label.
     *
     * @param string $type
     * @param int|null $rootId
     *
     * @return Collection<int, RoleModel>
     */
    public function flatTree(string $type, ?int $rootId = null): Collection
    {
        RoleTypeRegistry::ensure($type);

        $query = RoleModel::query()->ofType($type);

        if ($rootId !== null) {
            $query->inSubtree($rootId);
        }

        $roles = $query->orderBy('ordering')->orderBy('id')->get()->keyBy('id');

        if ($roles->isEmpty()) {
            return collect();
        }

        $paths = RolePath::query()
            ->whereIn('role_id', $roles->keys()->all())
            ->where('level', '>', 0)
            ->orderBy('level')
            ->get()
            ->groupBy('role_id');

        foreach ($roles as $role) {
            $ancestorIds = ($paths->get($role->id) ?? collect())->pluck('path_id');
            $names = $ancestorIds->reverse()->map(fn ($id) => $roles->get($id)?->name ?? RoleModel::query()
                ->find($id)?->name)->filter()->values()->all();
            $names[] = $role->name;

            $role->setAttribute('depth', $ancestorIds->count());
            $role->setAttribute('path_label', implode(' > ', $names));
        }

        return $this->orderPreOrder($roles, $rootId);
    }

    /**
     * Nested tree built from a single-query flat load.
     *
     * @param string $type
     * @param int|null $rootId
     *
     * @return array<int, array<string, mixed>>
     */
    public function nestedTree(string $type, ?int $rootId = null): array
    {
        $flat = $this->flatTree($type, $rootId);
        $nodes = [];

        foreach ($flat as $role) {
            $nodes[$role->id] = array_merge($role->toArray(), [
                'depth'      => (int) $role->getAttribute('depth'),
                'path_label' => (string) $role->getAttribute('path_label'),
                'children'   => [],
            ]);
        }

        $roots = [];

        foreach ($nodes as $id => &$node) {
            $parentId = $node['parent_id'] ?? null;

            if ($parentId !== null && isset($nodes[$parentId])) {
                $nodes[$parentId]['children'][] = &$node;
            }
            else {
                $roots[] = &$node;
            }
        }
        unset($node);

        return $this->detachNestedReferences($roots);
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

        $this->validateRulesPayload($rules);

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

    /**
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

        if (RoleTypeRegistry::getOption($type, 'hierarchical', true) === false) {
            throw new RoleHierarchyException('role_hierarchy_not_allowed');
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
     * Validate each rule payload against the evaluator form field validation rules.
     *
     * @param array<int, mixed> $rules
     *
     * @return void
     * @throws ValidationException
     */
    protected function validateRulesPayload(array $rules): void
    {
        foreach ($rules as $index => $rule) {
            if (! is_array($rule) || empty($rule['driver'])) {
                continue;
            }

            $evaluator = RuleEvaluatorRegistry::get($rule['driver']);

            if ($evaluator === null) {
                continue;
            }

            $formRules = $this->extractFormValidationRules($evaluator->fields());
            $payload = is_array($rule['payload'] ?? null) ? $rule['payload'] : [];

            if ($formRules === []) {
                continue;
            }

            $validator = Validator::make($payload, $formRules);

            if ($validator->fails()) {
                throw ValidationException::withMessages(collect($validator->errors()->toArray())->mapWithKeys(fn (
                    $messages,
                    $key
                ) => ["rules.$index.payload.$key" => $messages])->all());
            }
        }
    }

    /**
     * Walk form toArray structure and collect name => validation rule strings.
     *
     * @param array<string, mixed> $form
     *
     * @return array<string, string>
     */
    protected function extractFormValidationRules(array $form): array
    {
        $rules = [];

        $walk = function ($node) use (&$walk, &$rules): void {
            if (! is_array($node)) {
                return;
            }

            if (isset($node['name'], $node['validation']) && is_string($node['name']) && is_string($node['validation']) && $node['validation'] !== '') {
                $rules[$node['name']] = $node['validation'];
            }

            foreach ($node as $value) {
                if (is_array($value)) {
                    $walk($value);
                }
            }
        };

        $walk($form);

        return $rules;
    }

    /**
     * Pre-order DFS so children appear under their parent in flat lists.
     *
     * @param Collection<int, RoleModel> $roles
     * @param int|null $rootId
     *
     * @return Collection<int, RoleModel>
     */
    protected function orderPreOrder(Collection $roles, ?int $rootId = null): Collection
    {
        $byParent = $roles->groupBy(fn (RoleModel $role) => $role->parent_id ?? 0);
        $ordered = collect();

        $visit = function (?int $parentId) use (&$visit, $byParent, &$ordered): void {
            $key = $parentId ?? 0;
            $children = ($byParent->get($key) ?? collect())->sortBy([['ordering', 'asc'], ['id', 'asc']]);

            foreach ($children as $child) {
                $ordered->push($child);
                $visit($child->id);
            }
        };

        if ($rootId !== null && $roles->has($rootId)) {
            $root = $roles->get($rootId);
            $ordered->push($root);
            $visit($rootId);
        }
        else {
            $visit(null);
        }

        return $ordered->values();
    }

    /**
     * @param array $nodes
     *
     * @return array
     */
    protected function detachNestedReferences(array $nodes): array
    {
        $result = [];

        foreach ($nodes as $node) {
            $children = $node['children'] ?? [];
            unset($node['children']);
            $node['children'] = $this->detachNestedReferences($children);
            $result[] = $node;
        }

        return $result;
    }
}
