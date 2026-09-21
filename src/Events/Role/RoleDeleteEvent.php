<?php

namespace JobMetric\Rolix\Events\Role;

use JobMetric\Rolix\Models\Role;

/**
 * Dispatched after a role is deleted.
 */
class RoleDeleteEvent
{
    /**
     * Create a new event instance.
     *
     * @param Role $role
     */
    public function __construct(
        public Role $role
    ) {
    }
}
