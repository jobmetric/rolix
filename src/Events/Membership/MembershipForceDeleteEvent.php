<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Membership;

readonly class MembershipForceDeleteEvent implements DomainEvent
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
        return 'membership.force_deleted';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.membership.group', 'rolix::base.events.membership.force_deleted.title', 'rolix::base.events.membership.force_deleted.description', 'fas fa-skull', [
            'membership',
            'storage',
            'management',
        ]);
    }
}
