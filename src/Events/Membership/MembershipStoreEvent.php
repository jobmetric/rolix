<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Models\Membership;

readonly class MembershipStoreEvent implements DomainEvent
{
    /**
     * @param Membership $membership
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Membership $membership,
        public array $data = []
    ) {
    }

    public static function key(): string
    {
        return 'membership.stored';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.membership.group', 'rolix::base.events.membership.stored.title', 'rolix::base.events.membership.stored.description', 'fas fa-user-plus', [
            'membership',
            'storage',
            'management',
        ]);
    }
}
