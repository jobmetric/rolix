<?php

namespace JobMetric\Rolix\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Rolix\Facades\Membership as MembershipFacade;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;

/**
 * Adds membership management to a memberable model (tenant, team, …).
 *
 * @package JobMetric\Rolix
 */
trait HasMembers
{
    /**
     * Memberships targeting this memberable.
     *
     * @return MorphMany
     */
    public function memberships(): MorphMany
    {
        return $this->morphMany(Membership::class, 'memberable');
    }

    /**
     * Active (non-expired) memberships for this memberable.
     *
     * @param string|null $collection
     *
     * @return Collection<int, Membership>
     */
    public function members(?string $collection = null): Collection
    {
        return $this->memberships()
            ->when($collection !== null, fn ($q) => $q->where('collection', $collection))
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->get();
    }

    /**
     * Assign a personable to this memberable with a role.
     *
     * @param Model $personable
     * @param Role|int $role
     * @param array<string, mixed> $attributes
     *
     * @return Builder|EloquentCollection|Builder[]|Model
     */
    public function assignMember(Model $personable, Role|int $role, array $attributes = []): Builder|array|EloquentCollection|Model
    {
        $roleId = $role instanceof Role ? $role->id : $role;

        $response = MembershipFacade::store(array_merge([
            'personable_type' => $personable->getMorphClass(),
            'personable_id'   => $personable->getKey(),
            'memberable_type' => $this->getMorphClass(),
            'memberable_id'   => $this->getKey(),
            'role_id'         => $roleId,
        ], $attributes));

        return Membership::query()->findOrFail($response->data->id);
    }

    /**
     * Soft-delete memberships for a personable on this memberable.
     *
     * @param Model $personable
     * @param Role|int|null $role
     * @param string|null $collection
     *
     * @return int
     */
    public function removeMember(Model $personable, Role|int $role = null, ?string $collection = null): int
    {
        $query = $this->memberships()
            ->where('personable_type', $personable->getMorphClass())
            ->where('personable_id', $personable->getKey());

        if ($role !== null) {
            $roleId = $role instanceof Role ? $role->id : $role;
            $query->where('role_id', $roleId);
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

        return $count;
    }

    /**
     * Whether a personable has an active membership on this memberable.
     *
     * @param Model $personable
     * @param Role|int|null $role
     * @param string|null $collection
     *
     * @return bool
     */
    public function hasMember(Model $personable, Role|int $role = null, ?string $collection = null): bool
    {
        $query = $this->memberships()
            ->where('personable_type', $personable->getMorphClass())
            ->where('personable_id', $personable->getKey())
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });

        if ($role !== null) {
            $roleId = $role instanceof Role ? $role->id : $role;
            $query->where('role_id', $roleId);
        }

        if ($collection !== null) {
            $query->where('collection', $collection);
        }

        return $query->exists();
    }
}
