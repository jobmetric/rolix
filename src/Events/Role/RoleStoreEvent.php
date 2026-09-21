<?php

namespace JobMetric\Rolix\Events\Role;

use JobMetric\Rolix\Models\Role;

/**
 * Dispatched after a role is stored.
 */
class RoleStoreEvent
{
    /**
     * Create a new event instance.
     *
     * @param Role $role
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Role $role,
        public array $data = []
    ) {
    }
}
