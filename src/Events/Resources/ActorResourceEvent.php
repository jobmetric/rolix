<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class ActorResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $actor;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $actor
     */
    public function __construct(mixed $actor)
    {
        $this->actor = $actor;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.actor';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.actor.title', 'rolix::base.events.resource.actor.description', 'fas fa-user-secret', [
            'resource',
            'actor',
            'api',
        ]);
    }
}
