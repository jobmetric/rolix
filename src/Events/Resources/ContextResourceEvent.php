<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class ContextResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $context;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $context
     */
    public function __construct(mixed $context)
    {
        $this->context = $context;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.context';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.context.title', 'rolix::base.events.resource.context.description', 'fas fa-sitemap', [
            'resource',
            'context',
            'api',
        ]);
    }
}
