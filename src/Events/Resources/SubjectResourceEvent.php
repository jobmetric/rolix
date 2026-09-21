<?php

namespace JobMetric\Rolix\Events\Resources;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

class SubjectResourceEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    public mixed $subject;

    /**
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * @param mixed $subject
     */
    public function __construct(mixed $subject)
    {
        $this->subject = $subject;
        $this->resource = null;
    }

    public static function key(): string
    {
        return 'resource.subject';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.resource.group', 'rolix::base.events.resource.subject.title', 'rolix::base.events.resource.subject.description', 'fas fa-cube', [
            'resource',
            'subject',
            'api',
        ]);
    }
}
