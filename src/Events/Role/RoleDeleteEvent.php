<?php

namespace JobMetric\Rolix\Events\Role;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Role;

readonly class RoleDeleteEvent implements DomainEvent
{
    /**
     * @param Role $role
     */
    public function __construct(
        public Role $role
    ) {
    }

    public static function key(): string
    {
        return 'role.deleted';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.role.group', 'rolix::base.events.role.deleted.title', 'rolix::base.events.role.deleted.description', 'fas fa-trash', [
            'role',
            'storage',
            'management',
        ]);
    }
}
