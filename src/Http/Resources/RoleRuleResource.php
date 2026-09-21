<?php

namespace JobMetric\Rolix\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Rolix\Models\Role;

/**
 * Transforms the RoleRule model into a structured JSON resource.
 *
 * @property int $id
 * @property int $role_id
 * @property string $driver
 * @property array|null $payload
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role $role
 */
class RoleRuleResource extends JsonResource
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
        return [
            'id'         => $this->id,
            'role_id'    => $this->role_id,
            'driver'     => $this->driver,
            'payload'    => $this->payload,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
