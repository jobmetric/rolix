<?php

namespace JobMetric\Rolix\Events\Resources;

class RejectByResourceEvent
{
    /**
     * The reject_by model instance.
     *
     * @var mixed
     */
    public mixed $reject_by;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $reject_by
     */
    public function __construct(mixed $reject_by)
    {
        $this->reject_by = $reject_by;
        $this->resource = null;
    }
}
