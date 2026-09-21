<?php

namespace JobMetric\Rolix\Events;

use InvalidArgumentException;
use JobMetric\Rolix\Services\PermissionManager;

/**
 * Collects permission file paths keyed by context and optional model.
 *
 * @package JobMetric\Rolix
 */
class RegisterPathPermissionEvent
{
    /**
     * The paths to register permissions: modelKey => context => path.
     *
     * @var array<string, array<string, string>>
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
     * @param string|null $model Fully-qualified model class, or null for system-wide.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function addPath(string $context, string $path, ?string $model = null): void
    {
        $modelKey = $model === null || $model === ''
            ? PermissionManager::SYSTEM_MODEL_KEY
            : $model;

        if (isset($this->paths[$modelKey][$context])) {
            throw new InvalidArgumentException(
                "Path for context '{$context}' and model '{$modelKey}' is already registered."
            );
        }

        $this->paths[$modelKey][$context] = $path;
    }

    /**
     * Get the paths registered in the event.
     *
     * Flat map of entries: each item is [context, path, model|null].
     *
     * @return array<int, array{0: string, 1: string, 2: string|null}>
     */
    public function getPaths(): array
    {
        $flat = [];

        foreach ($this->paths as $modelKey => $contexts) {
            $model = $modelKey === PermissionManager::SYSTEM_MODEL_KEY ? null : $modelKey;

            foreach ($contexts as $context => $path) {
                $flat[] = [$context, $path, $model];
            }
        }

        return $flat;
    }
}
