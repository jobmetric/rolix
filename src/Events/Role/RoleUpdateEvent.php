<?php

namespace JobMetric\Rolix\Events\Role;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Role;

readonly class RoleUpdateEvent implements DomainEvent
{
    /**
     * @param Role $role
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Role $role,
        public array $data = []
    ) {
    }

    public static function key(): string
    {
        return 'role.updated';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.role.group', 'rolix::base.events.role.updated.title', 'rolix::base.events.role.updated.description', 'fas fa-edit', [
            'role',
            'storage',
            'management',
        ]);
    }
}
