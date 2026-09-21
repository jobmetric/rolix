<?php

namespace JobMetric\Rolix\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;

/**
 * Adds role membership and permission evaluation to a personable model.
 *
 * @package JobMetric\Rolix
 */
trait HasRole
{
    /**
     * Check whether the person has a permission, optionally within a memberable context.
     *
     * Without context, only system memberships (null memberable) apply.
     * With context, only memberships for that entity apply.
     *
     * @param string $permission
     * @param Model|null $context
     *
     * @return bool
     */
    public function hasPermission(string $permission, $context = null): bool
    {
        $memberships = $this->validMemberships($context);

        $roles = $this->extractRolesFromMemberships($memberships)->unique('id');

        $permissions = $this->collectPermissions($memberships, $roles);

        return $this->evaluatePermissions($permissions, $permission);
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
     * Load non-expired memberships for system or a specific memberable context.
     *
     * @param Model|null $context
     *
     * @return Collection
     */
    protected function validMemberships($context = null): Collection
    {
        return $this->memberships()->when($context, function ($query) use ($context) {
                $query->where('memberable_type', $context->getMorphClass())->where('memberable_id', $context->getKey());
            }, function ($query) {
                $query->whereNull('memberable_type')->whereNull('memberable_id');
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
        $roles = collect([$role]);

        if (method_exists($role, 'ancestors')) {
            $roles = $roles->merge($role->ancestors());
        }

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
        if (! method_exists($role, 'rules') && ! isset($role->rules)) {
            return true;
        }

        foreach ($role->rules as $rule) {
            $driver = app($rule->driver);
            if (method_exists($driver, 'evaluate') && ! $driver->evaluate($this, $rule->payload)) {
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

        // From memberships first
        foreach ($memberships as $membership) {
            $allow = array_merge($allow, $membership->allow ?? []);
            $deny = array_merge($deny, $membership->deny ?? []);
        }

        // Then from roles
        foreach ($roles as $role) {
            $allow = array_merge($allow, $role->allow ?? []);
            $deny = array_merge($deny, $role->deny ?? []);
        }

        return [
            'allow' => array_unique($allow),
            'deny'  => array_unique($deny),
        ];
    }

    /**
     * Evaluate allow/deny lists for a single permission.
     *
     * @param array{allow: array, deny: array} $permissions
     * @param string $permission
     *
     * @return bool
     */
    protected function evaluatePermissions(array $permissions, string $permission): bool
    {
        if (in_array($permission, $permissions['deny'])) {
            return false;
        }

        if (in_array($permission, $permissions['allow'])) {
            return true;
        }

        return false;
    }
}
