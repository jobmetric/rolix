<?php

namespace JobMetric\Rolix\Support;

use InvalidArgumentException;
use JobMetric\Rolix\Contracts\RuleEvaluatorContract;

/**
 * Registry for role rule evaluator drivers.
 *
 * @package JobMetric\Rolix
 */
class RuleEvaluatorRegistry
{
    /**
     * Registered evaluators keyed by name.
     *
     * @var array<string, class-string<RuleEvaluatorContract>>
     */
    protected array $evaluators = [];

    /**
     * Register an evaluator class.
     *
     * @param class-string<RuleEvaluatorContract> $class
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function register(string $class): self
    {
        if (! is_subclass_of($class, RuleEvaluatorContract::class)) {
            throw new InvalidArgumentException("Class {$class} must implement RuleEvaluatorContract.");
        }

        /** @var RuleEvaluatorContract $instance */
        $instance = app($class);
        $this->evaluators[$instance->name()] = $class;

        return $this;
    }

    /**
     * Remove an evaluator by name.
     *
     * @param string $name
     *
     * @return self
     */
    public function unregister(string $name): self
    {
        unset($this->evaluators[$name]);

        return $this;
    }

    /**
     * Whether a name or FQCN is registered / resolvable.
     *
     * @param string $nameOrClass
     *
     * @return bool
     */
    public function has(string $nameOrClass): bool
    {
        return $this->resolveClass($nameOrClass) !== null;
    }

    /**
     * Get an evaluator instance by name or FQCN.
     *
     * @param string $nameOrClass
     *
     * @return RuleEvaluatorContract|null
     */
    public function get(string $nameOrClass): ?RuleEvaluatorContract
    {
        $class = $this->resolveClass($nameOrClass);

        return $class ? app($class) : null;
    }

    /**
     * Resolve evaluator FQCN by name or class string.
     *
     * @param string $nameOrClass
     *
     * @return class-string<RuleEvaluatorContract>|null
     */
    public function resolveClass(string $nameOrClass): ?string
    {
        if (isset($this->evaluators[$nameOrClass])) {
            return $this->evaluators[$nameOrClass];
        }

        if (class_exists($nameOrClass) && is_subclass_of($nameOrClass, RuleEvaluatorContract::class)) {
            return $nameOrClass;
        }

        return null;
    }

    /**
     * All registered name => class map.
     *
     * @return array<string, class-string<RuleEvaluatorContract>>
     */
    public function all(): array
    {
        return $this->evaluators;
    }

    /**
     * Registered evaluator names.
     *
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_keys($this->evaluators);
    }

    /**
     * UI payload for one evaluator form.
     *
     * @param string $nameOrClass
     *
     * @return array{driver: class-string, name: string, form: array}|null
     */
    public function formFor(string $nameOrClass): ?array
    {
        $evaluator = $this->get($nameOrClass);

        if ($evaluator === null) {
            return null;
        }

        return [
            'driver' => $evaluator::class,
            'name'   => $evaluator->name(),
            'form'   => $evaluator->fields(),
        ];
    }

    /**
     * Clear all registrations.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->evaluators = [];

        return $this;
    }
}
