<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\Rolix\Models\Membership;

/**
 * Dispatched after a membership is stored.
 */
class MembershipStoreEvent
{
    /**
     * Create a new event instance.
     *
     * @param Membership $membership
     * @param array<string, mixed> $data
     */
    public function __construct(
        public Membership $membership,
        public array $data = []
    ) {
    }
}
