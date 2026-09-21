<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Membership;

readonly class MembershipRestoreEvent implements DomainEvent
{
    /**
     * @param Membership $membership
     */
    public function __construct(
        public Membership $membership
    ) {
    }

    public static function key(): string
    {
        return 'membership.restored';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.membership.group', 'rolix::base.events.membership.restored.title', 'rolix::base.events.membership.restored.description', 'fas fa-undo', [
            'membership',
            'storage',
            'management',
        ]);
    }
}
