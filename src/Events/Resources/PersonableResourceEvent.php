<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class PersonableResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $personable;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $personable
     */
    public function __construct(mixed $personable)
    {
        $this->personable = $personable;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.personable';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.personable.title', 'rolix::base.events.resource.personable.description', 'fas fa-user', [
            'resource',
            'personable',
            'api',
        ]);
    }
}
