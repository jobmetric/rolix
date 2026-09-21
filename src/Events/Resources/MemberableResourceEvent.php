<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class MemberableResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $memberable;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $memberable
     */
    public function __construct(mixed $memberable)
    {
        $this->memberable = $memberable;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.memberable';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.memberable.title', 'rolix::base.events.resource.memberable.description', 'fas fa-building', [
            'resource',
            'memberable',
            'api',
        ]);
    }
}
