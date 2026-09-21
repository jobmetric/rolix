<?php

namespace JobMetric\Rolix\Events\Membership;

use JobMetric\Rolix\Models\Membership;

/**
 * Dispatched after a membership is deleted.
 */
class MembershipDeleteEvent
{
    /**
     * Create a new event instance.
     *
     * @param Membership $membership
     */
    public function __construct(
        public Membership $membership
    ) {
    }
}
