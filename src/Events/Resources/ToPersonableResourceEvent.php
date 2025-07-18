<?php

namespace JobMetric\Rolix\Events\Resources;

class ToPersonableResourceEvent
{
    /**
     * The to_personable model instance.
     *
     * @var mixed
     */
    public mixed $to_personable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $to_personable
     */
    public function __construct(mixed $to_personable)
    {
        $this->to_personable = $to_personable;
        $this->resource = null;
    }
}
