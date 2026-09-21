<?php

namespace JobMetric\Rolix\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Closure-table path row resource.
 *
 * @property string $type
 * @property int $role_id
 * @property int $path_id
 * @property int $level
 * @property mixed $path
 */
class RolePathResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type'    => $this->type,
            'role_id' => $this->role_id,
            'path_id' => $this->path_id,
            'level'   => $this->level,
            'path'    => $this->whenLoaded('path', function () {
                return [
                    'id'   => $this->path->id,
                    'name' => $this->path->name,
                    'type' => $this->path->type,
                ];
            }),
        ];
    }
}
