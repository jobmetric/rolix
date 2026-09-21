<?php

namespace JobMetric\Rolix\Events;

use InvalidArgumentException;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Rolix\Services\PermissionManager;

/**
 * Collects permission file paths keyed by context and optional model.
 *
 * @package JobMetric\Rolix
 */
class RegisterPathPermissionEvent implements DomainEvent
{
    /**
     * The paths to register permissions: modelKey => context => path.
     *
     * @var array<string, array<string, string>>
     */
    private array $paths;

    public function __construct()
    {
        $this->paths = [];
    }

    public static function key(): string
    {
        return 'permission.paths_registering';
    }

    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'rolix::base.events.permission.group', 'rolix::base.events.permission.paths_registering.title', 'rolix::base.events.permission.paths_registering.description', 'fas fa-folder-open', [
            'permission',
            'registration',
            'management',
        ]);
    }

    /**
     * @param string $context
     * @param string $path
     * @param string|null $model
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function addPath(string $context, string $path, ?string $model = null): void
    {
        $modelKey = $model === null || $model === '' ? PermissionManager::SYSTEM_MODEL_KEY : $model;

        if (isset($this->paths[$modelKey][$context])) {
            throw new InvalidArgumentException("Path for context '{$context}' and model '{$modelKey}' is already registered.");
        }

        $this->paths[$modelKey][$context] = $path;
    }

    /**
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
