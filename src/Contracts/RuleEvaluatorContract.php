<?php

namespace JobMetric\Rolix\Contracts;

/**
 * Interface RuleEvaluatorContract
 *
 * This contract must be implemented by all custom rule evaluators.
 * Evaluators are responsible for determining whether a given rule passes
 * based on provided logic and the current context.
 *
 * Example usage:
 * A time-based rule evaluator might check if the current time falls between two values.
 */
interface RuleEvaluatorContract
{
    /**
     * Evaluate the rule based on the provided context.
     *
     * @param array $rule The rule definition array.
     *                    Example:
     *                    [
     *                        'field' => 'time',
     *                        'operator' => 'between',
     *                        'value' => ['08:00', '18:00']
     *                    ]
     *
     * @param mixed $context Contextual data required to evaluate the rule.
     *                       Can be an object, array, or any data structure
     *                       providing necessary info (e.g., user, request, etc.).
     *
     * @return bool Returns true if the rule evaluation passes, false otherwise.
     */
    public function evaluate(array $rule, mixed $context): bool;

    /**
     * Get the unique name of the evaluator.
     *
     * This name is used internally to identify the evaluator and associate it
     * with specific rules.
     *
     * Example: "time", "request_method", "user_attribute"
     *
     * @return string The unique evaluator name.
     */
    public function name(): string;

    /**
     * Get a list of configurable fields that this evaluator supports.
     *
     * Each field returned should describe what data or configuration is required
     * for this evaluator. This information can be used to build dynamic UIs
     * or rule editors.
     *
     * Example return value:
     * [
     *     'field' => ['type' => 'string', 'required' => true],
     *     'operator' => ['type' => 'string', 'required' => true],
     *     'value' => ['type' => 'mixed', 'required' => true],
     * ]
     *
     * @return array Associative array describing supported fields and their metadata.
     */
    public function fields(): array;
}
