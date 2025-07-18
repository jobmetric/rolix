<?php

namespace JobMetric\Rolix\Events\Resources;

class SubjectResourceEvent
{
    /**
     * The subject model instance.
     *
     * @var mixed
     */
    public mixed $subject;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $subject
     */
    public function __construct(mixed $subject)
    {
        $this->subject = $subject;
        $this->resource = null;
    }
}
