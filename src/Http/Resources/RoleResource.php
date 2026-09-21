<?php

namespace JobMetric\Rolix\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Models\RoleRule;

/**
 * Transforms the Role model into a structured JSON resource.
 *
 * @property int $id
 * @property string $type
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $description
 * @property array|null $allow
 * @property array|null $deny
 * @property bool $is_default
 * @property bool $is_super
 * @property int $ordering
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Role|null $parent
 * @property-read Role[] $children
 * @property-read RolePath[] $paths
 * @property-read RoleRule[] $rules
 */
class RoleResource extends JsonResource
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
            'id'          => $this->id,
            'type'        => $this->type,
            'parent_id'   => $this->parent_id,
            'name'        => $this->name,
            'description' => $this->description,
            'allow'       => $this->allow ?? [],
            'deny'        => $this->deny ?? [],
            'is_default'  => (bool) $this->is_default,
            'is_super'    => (bool) $this->is_super,
            'ordering'    => $this->ordering,
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
            'parent'      => $this->whenLoaded('parent', function () {
                return RoleResource::make($this->parent);
            }),
            'children'    => $this->whenLoaded('children', function () {
                return RoleResource::collection($this->children);
            }),
            'rules'       => $this->whenLoaded('rules', function () {
                return RoleRuleResource::collection($this->rules);
            }),
            'paths'       => $this->whenLoaded('paths', function () {
                return RolePathResource::collection($this->paths);
            }),
            'ancestors'   => $this->whenLoaded('paths', function () {
                return $this->paths->where('level', '>', 0)->sortBy('level')->values()->map(function (RolePath $path) {
                    $ancestor = $path->relationLoaded('path') ? $path->path : $path->path()->first();

                    if ($ancestor === null) {
                        return null;
                    }

                    return [
                        'id'    => $ancestor->id,
                        'name'  => $ancestor->name,
                        'type'  => $ancestor->type,
                        'level' => $path->level,
                    ];
                })->filter()->values();
            }),
        ];
    }
}
