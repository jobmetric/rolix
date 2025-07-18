<?php

namespace JobMetric\Rolix\Events\Resources;

class ContextResourceEvent
{
    /**
     * The context model instance.
     *
     * @var mixed
     */
    public mixed $context;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $context
     */
    public function __construct(mixed $context)
    {
        $this->context = $context;
        $this->resource = null;
    }
}
