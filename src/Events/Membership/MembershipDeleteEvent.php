<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Membership;

class MembershipDeleteEvent implements DomainEvent
{
    /**
     * @param Membership $membership
     */
    public function __construct(
        public readonly Membership $membership
    ) {
    }

    public static function key(): string
    {
        return 'membership.deleted';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.membership.group', 'rolix::base.events.membership.deleted.title', 'rolix::base.events.membership.deleted.description', 'fas fa-user-minus', [
            'membership',
            'storage',
            'management',
        ]);
    }
}
