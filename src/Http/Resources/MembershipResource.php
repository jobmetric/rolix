<?php

namespace JobMetric\Rolix\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;

/**
 * Transforms the Membership model into a structured JSON resource.
 *
 * @property int $id
 * @property string $personable_type
 * @property int $personable_id
 * @property string|null $memberable_type
 * @property int|null $memberable_id
 * @property int|null $role_id
 * @property string|null $collection
 * @property bool $is_owner
 * @property Carbon|null $expired_at
 * @property array|null $allow
 * @property array|null $deny
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role|null $role
 */
class MembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Membership $membership */
        $membership = $this->resource;

        return [
            'id'                  => $this->id,
            'personable_type'     => $this->personable_type,
            'personable_id'       => $this->personable_id,
            'memberable_type'     => $this->memberable_type,
            'memberable_id'       => $this->memberable_id,
            'role_id'             => $this->role_id,
            'collection'          => $this->collection,
            'is_owner'            => (bool) $this->is_owner,
            'expired_at'          => $this->expired_at?->toISOString(),
            'allow'               => $this->allow ?? [],
            'deny'                => $this->deny ?? [],
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
            'deleted_at'          => $this->deleted_at?->toISOString(),
            'personable_resource' => $membership->personable_resource,
            'memberable_resource' => $membership->memberable_resource,
            'role'                => $this->whenLoaded('role', function () {
                return RoleResource::make($this->role);
            }),
        ];
    }
}
