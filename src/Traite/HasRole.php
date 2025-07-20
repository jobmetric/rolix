<?php

namespace JobMetric\Rolix\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Representative;
use JobMetric\Rolix\Models\Role;

trait HasRole
{
    public function hasPermission(string $permission, $context = null): bool
    {
        $memberships = $this->validMemberships($context);
        $representatives = $this->validDelegations($context);

        $roles = collect()
            ->merge($this->extractRolesFromMemberships($memberships))
            ->merge($this->extractRolesFromDelegations($representatives))
            ->unique('id');

        $permissions = $this->collectPermissions($memberships, $roles);

        return $this->evaluatePermissions($permissions, $permission);
    }

    public function memberships(): MorphMany
    {
        return $this->morphMany(Membership::class, 'personable');
    }

    protected function validMemberships($context = null): Collection
    {
        return $this->memberships()
            ->when($context, function ($query) use ($context) {
                $query->where('memberable_type', get_class($context))
                    ->where('memberable_id', $context->id);
            })
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->get();
    }

    public function delegations(): MorphMany
    {
        return $this->morphMany(Representative::class, 'to_personable');
    }

    protected function validDelegations($context = null): Collection
    {
        return $this->delegations()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->when($context, function ($query) use ($context) {
                $query->where('delegatable_type', get_class($context))
                    ->where('delegatable_id', $context->id);
            })
            ->get();
    }

    protected function extractRolesFromMemberships(Collection $memberships): Collection
    {
        return Role::whereIn('id', $memberships->pluck('role_id')->filter())->get()
            ->flatMap(function ($role) {
                return $this->getRoleWithAncestorsIfValid($role);
            });
    }

    protected function extractRolesFromDelegations(Collection $reps): Collection
    {
        return Role::whereIn('id', $reps->pluck('role_id')->filter())->get()
            ->flatMap(function ($role) {
                return $this->getRoleWithAncestorsIfValid($role);
            });
    }

    protected function getRoleWithAncestorsIfValid(Role $role): Collection
    {
        $roles = collect([$role])->merge($role->ancestors());

        return $roles->filter(function ($role) {
            return $this->evaluateRoleRules($role);
        });
    }

    protected function evaluateRoleRules(Role $role): bool
    {
        foreach ($role->rules as $rule) {
            $driver = app($rule->driver);
            if (method_exists($driver, 'evaluate') && !$driver->evaluate($this, $rule->payload)) {
                return false;
            }
        }

        return true;
    }

    protected function collectPermissions(Collection $memberships, Collection $roles): array
    {
        $allow = [];
        $deny = [];

        // اول از خود membership ها
        foreach ($memberships as $membership) {
            $allow = array_merge($allow, $membership->allow ?? []);
            $deny = array_merge($deny, $membership->deny ?? []);
        }

        // بعد از خود roleها
        foreach ($roles as $role) {
            $allow = array_merge($allow, $role->allow ?? []);
            $deny = array_merge($deny, $role->deny ?? []);
        }

        return [
            'allow' => array_unique($allow),
            'deny' => array_unique($deny),
        ];
    }

    protected function evaluatePermissions(array $permissions, string $permission): bool
    {
        if (in_array($permission, $permissions['deny'])) return false;
        if (in_array($permission, $permissions['allow'])) return true;

        return false;
    }
}
