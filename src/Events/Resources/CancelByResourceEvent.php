<?php

namespace JobMetric\Rolix\Events\Resources;

class CancelByResourceEvent
{
    /**
     * The cancel_by model instance.
     *
     * @var mixed
     */
    public mixed $cancel_by;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $cancel_by
     */
    public function __construct(mixed $cancel_by)
    {
        $this->cancel_by = $cancel_by;
        $this->resource = null;
    }
}
