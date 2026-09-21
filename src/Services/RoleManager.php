<?php

namespace JobMetric\Rolix\Services;

use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Models\Role;

/**
 * Manages role creation and related operations.
 *
 * @package JobMetric\Rolix
 */
class RoleManager
{
    /**
     * Create a new role.
     *
     * @param array $data
     *
     * @return Role
     */
    public function create(array $data): Role
    {
        $type = $data['type'] ?? 'system';

        RoleTypeRegistry::ensure($type);

        $role = new Role;

        $role->type = $type;
        $role->parent_id = $data['parent_id'] ?? null;
        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;
        $role->allow = $data['allow'] ?? [];
        $role->deny = $data['deny'] ?? [];
        $role->is_default = $data['is_default'] ?? false;
        $role->ordering = $data['ordering'] ?? 0;

        $role->save();

        // add role path

        return $role;
    }
}
