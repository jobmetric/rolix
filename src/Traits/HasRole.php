<?php

namespace JobMetric\Rolix\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Rolix\Contracts\RuleEvaluatorContract;
use JobMetric\Rolix\Facades\Membership as MembershipFacade;
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Support\PermissionCache;

/**
 * Adds role membership and permission evaluation to a personable model.
 *
 * @package JobMetric\Rolix
 */
trait HasRole
{
    /**
     * Request-scoped memoization for permission evaluation.
     *
     * @var array<string, mixed>
     */
    protected array $rolixMemo = [];

    /**
     * Check whether the person has a permission, optionally within a memberable context.
     *
     * Super membership grants all permissions regardless of context.
     * Owner membership grants all permissions for that memberable context only.
     * Without context, only system memberships (null memberable) apply.
     * With context, only memberships for that entity apply.
     *
     * @param string $permission
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return bool
     */
    public function hasPermission(string $permission, Model $context = null, ?string $collection = null): bool
    {
        $permissions = $this->getPermissions($context, $collection);

        return $this->evaluatePermissions($permissions, $permission);
    }

    /**
     * Effective allow/deny permission lists for the given scope.
     *
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return array{allow: array<int, string>, deny: array<int, string>}
     */
    public function getPermissions(Model $context = null, ?string $collection = null): array
    {
        $memoKey = 'permissions:' . $this->rolixScopeKey($context, $collection);

        if (array_key_exists($memoKey, $this->rolixMemo)) {
            return $this->rolixMemo[$memoKey];
        }

        $resolve = function () use ($context, $collection): array {
            if ($this->hasSuperMembership()) {
                return ['allow' => ['*'], 'deny' => []];
            }

            if ($context !== null && $this->hasOwnerMembership($context, $collection)) {
                return ['allow' => ['*'], 'deny' => []];
            }

            $memberships = $this->validMemberships($context, $collection);
            $roles = $this->extractRolesFromMemberships($memberships)->unique('id');

            return $this->collectPermissions($memberships, $roles);
        };

        $result = PermissionCache::remember($this, $memoKey, $resolve);

        return $this->rolixMemo[$memoKey] = $result;
    }

    /**
     * Whether the person has the given role in scope.
     *
     * @param Role|int|string $role Role model, id, or name
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return bool
     */
    public function hasRole(Role|int|string $role, Model $context = null, ?string $collection = null): bool
    {
        $memberships = $this->validMemberships($context, $collection);
        $roleIds = $memberships->pluck('role_id')->filter()->map(fn ($id) => (int) $id);

        if ($role instanceof Role) {
            return $roleIds->contains((int) $role->id);
        }

        if (is_int($role) || ctype_digit((string) $role)) {
            return $roleIds->contains((int) $role);
        }

        $matched = Role::query()->whereIn('id', $roleIds->all())->where('name', $role)->exists();

        return $matched;
    }

    /**
     * Assign a role membership via the Membership service.
     *
     * @param Role|int $role
     * @param Model|null $context
     * @param array<string, mixed> $attributes
     *
     * @return Membership
     */
    public function assignRole(Role|int $role, Model $context = null, array $attributes = []): Membership
    {
        $roleId = $role instanceof Role ? $role->id : $role;

        $data = array_merge([
            'personable_type' => $this->getMorphClass(),
            'personable_id'   => $this->getKey(),
            'role_id'         => $roleId,
            'memberable_type' => $context?->getMorphClass(),
            'memberable_id'   => $context?->getKey(),
        ], $attributes);

        $response = MembershipFacade::store($data);

        PermissionCache::forget($this);

        return Membership::query()->findOrFail($response->data->id);
    }

    /**
     * Soft-delete memberships matching the role in scope.
     *
     * @param Role|int $role
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return int Number of removed memberships
     */
    public function removeRole(Role|int $role, Model $context = null, ?string $collection = null): int
    {
        $roleId = $role instanceof Role ? $role->id : $role;

        $query = $this->memberships()->where('role_id', $roleId);

        if ($context) {
            $query->where('memberable_type', $context->getMorphClass())->where('memberable_id', $context->getKey());
        }
        else {
            $query->whereNull('memberable_type')->whereNull('memberable_id');
        }

        if ($collection !== null) {
            $query->where('collection', $collection);
        }

        $ids = $query->pluck('id');
        $count = 0;

        foreach ($ids as $id) {
            MembershipFacade::destroy((int) $id);
            $count++;
        }

        PermissionCache::forget($this);

        return $count;
    }

    /**
     * Replace all memberships in scope with the given role ids.
     *
     * @param array<int, Role|int> $roles
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return void
     */
    public function syncRoles(array $roles, Model $context = null, ?string $collection = null): void
    {
        $desired = collect($roles)
            ->map(fn ($role) => $role instanceof Role ? (int) $role->id : (int) $role)
            ->unique()
            ->values();

        $existing = $this->validMemberships($context, $collection);

        foreach ($existing as $membership) {
            if (! $desired->contains((int) $membership->role_id)) {
                MembershipFacade::destroy((int) $membership->id);
            }
        }

        $existingRoleIds = $this->validMemberships($context, $collection)->pluck('role_id')->map(fn ($id) => (int) $id);

        foreach ($desired as $roleId) {
            if ($existingRoleIds->contains($roleId)) {
                continue;
            }

            $this->assignRole($roleId, $context, [
                'collection' => $collection,
            ]);
        }

        PermissionCache::forget($this);
    }

    /**
     * Clear request-scoped permission memoization for this personable.
     *
     * @return void
     */
    public function forgetRolixCache(): void
    {
        $this->rolixMemo = [];
        $this->unsetRelation('memberships');
    }

    /**
     * Memberships relation as personable.
     *
     * @return MorphMany
     */
    public function memberships(): MorphMany
    {
        return $this->morphMany(Membership::class, 'personable');
    }

    /**
     * Whether the person has any active super-role membership.
     *
     * @return bool
     */
    protected function hasSuperMembership(): bool
    {
        $memoKey = 'super';

        if (array_key_exists($memoKey, $this->rolixMemo)) {
            return (bool) $this->rolixMemo[$memoKey];
        }

        return $this->rolixMemo[$memoKey] = $this->memberships()->where(function ($q) {
            $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
        })->whereHas('role', function ($q) {
            $q->where('is_super', true);
        })->exists();
    }

    /**
     * Whether the person owns the given memberable context.
     *
     * @param Model $context
     * @param string|null $collection
     *
     * @return bool
     */
    protected function hasOwnerMembership(Model $context, ?string $collection = null): bool
    {
        $memoKey = 'owner:' . $this->rolixScopeKey($context, $collection);

        if (array_key_exists($memoKey, $this->rolixMemo)) {
            return (bool) $this->rolixMemo[$memoKey];
        }

        $query = $this->memberships()
            ->where('memberable_type', $context->getMorphClass())
            ->where('memberable_id', $context->getKey())
            ->where('is_owner', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });

        if ($collection !== null) {
            $query->where('collection', $collection);
        }

        return $this->rolixMemo[$memoKey] = $query->exists();
    }

    /**
     * Load non-expired memberships for system or a specific memberable context.
     *
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return Collection
     */
    protected function validMemberships(Model $context = null, ?string $collection = null): Collection
    {
        $memoKey = 'memberships:' . $this->rolixScopeKey($context, $collection);

        if (array_key_exists($memoKey, $this->rolixMemo)) {
            return $this->rolixMemo[$memoKey];
        }

        return $this->rolixMemo[$memoKey] = $this->memberships()->when($context, function ($query) use ($context) {
            $query->where('memberable_type', $context->getMorphClass())->where('memberable_id', $context->getKey());
        }, function ($query) {
            $query->whereNull('memberable_type')->whereNull('memberable_id');
        })->when($collection !== null, function ($query) use ($collection) {
            $query->where('collection', $collection);
        })->where(function ($q) {
            $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
        })->get();
    }

    /**
     * Extract roles (and valid ancestors) from memberships.
     *
     * @param Collection $memberships
     *
     * @return Collection
     */
    protected function extractRolesFromMemberships(Collection $memberships): Collection
    {
        return Role::whereIn('id', $memberships->pluck('role_id')->filter())->get()->flatMap(function ($role) {
            return $this->getRoleWithAncestorsIfValid($role);
        });
    }

    /**
     * Get a role with ancestors filtered by rules.
     *
     * @param Role $role
     *
     * @return Collection
     */
    protected function getRoleWithAncestorsIfValid(Role $role): Collection
    {
        $roles = collect([$role])->merge($role->ancestors());

        return $roles->filter(function ($role) {
            return $this->evaluateRoleRules($role);
        });
    }

    /**
     * Evaluate all rules attached to a role.
     *
     * @param Role $role
     *
     * @return bool
     */
    protected function evaluateRoleRules(Role $role): bool
    {
        $role->loadMissing('rules');

        foreach ($role->rules as $rule) {
            if (! is_string($rule->driver)) {
                return false;
            }

            $driver = RuleEvaluatorRegistry::get($rule->driver);

            if (! $driver instanceof RuleEvaluatorContract) {
                return false;
            }

            $payload = is_array($rule->payload) ? $rule->payload : [];

            if (! $driver->evaluate($payload, $this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Collect allow/deny permissions from memberships and roles.
     *
     * @param Collection $memberships
     * @param Collection $roles
     *
     * @return array{allow: array, deny: array}
     */
    protected function collectPermissions(Collection $memberships, Collection $roles): array
    {
        $allow = [];
        $deny = [];

        foreach ($memberships as $membership) {
            $allow = array_merge($allow, $membership->allow ?? []);
            $deny = array_merge($deny, $membership->deny ?? []);
        }

        foreach ($roles as $role) {
            $allow = array_merge($allow, $role->allow ?? []);
            $deny = array_merge($deny, $role->deny ?? []);
        }

        return [
            'allow' => array_values(array_unique($allow)),
            'deny'  => array_values(array_unique($deny)),
        ];
    }

    /**
     * Evaluate allow/deny lists for a single permission (supports trailing wildcards).
     *
     * @param array{allow: array, deny: array} $permissions
     * @param string $permission
     *
     * @return bool
     */
    protected function evaluatePermissions(array $permissions, string $permission): bool
    {
        if ($this->permissionMatchesList($permission, $permissions['deny'])) {
            return false;
        }

        return $this->permissionMatchesList($permission, $permissions['allow']);
    }

    /**
     * Whether a permission matches any entry (exact or foo.* wildcard).
     *
     * @param string $permission
     * @param array<int, string> $list
     *
     * @return bool
     */
    protected function permissionMatchesList(string $permission, array $list): bool
    {
        foreach ($list as $entry) {
            if ($entry === '*' || $entry === $permission) {
                return true;
            }

            if (str_ends_with($entry, '.*')) {
                $prefix = substr($entry, 0, -1);

                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Build a stable memo/cache scope key for context + collection.
     *
     * @param Model|null $context
     * @param string|null $collection
     *
     * @return string
     */
    protected function rolixScopeKey(Model $context = null, ?string $collection = null): string
    {
        $contextKey = $context === null
            ? 'system'
            : $context->getMorphClass() . ':' . $context->getKey();

        return $contextKey . ':' . ($collection ?? '*');
    }
}
