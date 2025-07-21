<?php

namespace JobMetric\Rolix\Events;

use InvalidArgumentException;

class RegisterPathPermissionEvent
{
    /**
     * The paths to register permissions.
     *
     * @var mixed|null
     */
    private array $paths;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->paths = [];
    }

    /**
     * Add a path to the event.
     *
     * @param string $context
     * @param string $path
     *
     * @return void
     */
    public function addPath(string $context, string $path): void
    {
        if (!isset($this->paths[$context])) {
            $this->paths[$context] = $path;
        } else {
            throw new InvalidArgumentException("Path for context '{$context}' is already registered.");
        }
    }

    /**
     * Get the paths registered in the event.
     *
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
