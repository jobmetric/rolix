<?php

namespace JobMetric\Rolix\Events\Resources;

class TargetResourceEvent
{
    /**
     * The target model instance.
     *
     * @var mixed
     */
    public mixed $target;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $target
     */
    public function __construct(mixed $target)
    {
        $this->target = $target;
        $this->resource = null;
    }
}
