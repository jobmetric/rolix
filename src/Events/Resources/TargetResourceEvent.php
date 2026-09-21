<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class TargetResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $target;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $target
     */
    public function __construct(mixed $target)
    {
        $this->target = $target;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.target';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.target.title', 'rolix::base.events.resource.target.description', 'fas fa-bullseye', [
            'resource',
            'target',
            'api',
        ]);
    }
}
