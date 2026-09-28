<?php

namespace JobMetric\Rolix\Events\Role;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Role;

class RoleStoreEvent implements DomainEvent
{
    /**
     * @param Role $role
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly Role $role,
        public readonly array $data = []
    ) {
    }

    public static function key(): string
    {
        return 'role.stored';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.role.group', 'rolix::base.events.role.stored.title', 'rolix::base.events.role.stored.description', 'fas fa-save', [
            'role',
            'storage',
            'management',
        ]);
    }
}
