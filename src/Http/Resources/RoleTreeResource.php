<?php

namespace JobMetric\Rolix\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Flat hierarchical role row (OpenCart-style list item).
 *
 * Expects depth and path_label attributes on the underlying model/array.
 */
class RoleTreeResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = RoleResource::make($this->resource)->toArray($request);

        return array_merge($base, [
            'depth'      => (int) ($this->depth ?? 0),
            'path_label' => (string) ($this->path_label ?? $this->name),
        ]);
    }
}
