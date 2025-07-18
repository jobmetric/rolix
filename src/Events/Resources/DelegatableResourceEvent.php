<?php

namespace JobMetric\Rolix\Events\Resources;

class DelegatableResourceEvent
{
    /**
     * The delegatable model instance.
     *
     * @var mixed
     */
    public mixed $delegatable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $delegatable
     */
    public function __construct(mixed $delegatable)
    {
        $this->delegatable = $delegatable;
        $this->resource = null;
    }
}
