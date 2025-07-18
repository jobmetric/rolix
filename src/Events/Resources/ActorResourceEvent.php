<?php

namespace JobMetric\Rolix\Events\Resources;

class ActorResourceEvent
{
    /**
     * The actor model instance.
     *
     * @var mixed
     */
    public mixed $actor;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $actor
     */
    public function __construct(mixed $actor)
    {
        $this->actor = $actor;
        $this->resource = null;
    }
}
