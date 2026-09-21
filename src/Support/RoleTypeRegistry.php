<?php

namespace JobMetric\Rolix\Support;

use Illuminate\Support\Arr;
use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;

/**
 * Registry for role types. Holds a dynamic list that can be extended at
 * runtime via register() or via config (rolix.types). Used to validate and
 * list allowed role types.
 *
 * @package JobMetric\Rolix
 *
 * @property-read array<string, array> $types Map of type name => options (internal state)
 */
class RoleTypeRegistry
{
    /**
     * Registered role types: type name => options array.
     *
     * @var array<string, array>
     */
    protected array $types = [];

    /**
     * Register a role type, or merge options for an existing type.
     *
     * @param string $type   Role type name (e.g. department, tenant).
     * @param array $options Optional options (e.g. label, description, hierarchical).
     *
     * @return self
     */
    public function register(string $type, array $options = []): self
    {
        $this->types[$type] = array_merge($this->types[$type] ?? [], $options);

        return $this;
    }

    /**
     * Remove a role type from the registry.
     *
     * @param string $type Role type name to remove.
     *
     * @return self
     */
    public function unregister(string $type): self
    {
        unset($this->types[$type]);

        return $this;
    }

    /**
     * Check whether a role type is registered.
     *
     * @param string $type Role type name to check.
     *
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /**
     * Get options for a registered role type.
     *
     * @param string $type Role type name.
     *
     * @return array|null Options array, or null if type is not registered.
     */
    public function get(string $type): ?array
    {
        return $this->types[$type] ?? null;
    }

    /**
     * Get all registered types and their options.
     *
     * @return array<string, array> Map of type name => options.
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * Get the list of registered role type names (keys only).
     *
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_keys($this->types);
    }

    /**
     * Get a single option for a registered type.
     *
     * @param string $type   Role type name.
     * @param string $key    Option key (e.g. label, hierarchical).
     * @param mixed $default Value when key is missing.
     *
     * @return mixed
     */
    public function getOption(string $type, string $key, mixed $default = null): mixed
    {
        return Arr::get($this->types[$type] ?? [], $key, $default);
    }

    /**
     * Throw if the role type is not registered.
     *
     * @param string $type Role type name to validate.
     *
     * @return self
     * @throws RoleTypeNotFoundException When the type is not registered.
     */
    public function ensure(string $type): self
    {
        if (! $this->has($type)) {
            throw new RoleTypeNotFoundException($type);
        }

        return $this;
    }

    /**
     * Remove all registered role types.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->types = [];

        return $this;
    }
}
