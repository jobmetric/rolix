<?php

namespace JobMetric\Rolix\Contracts;

use JobMetric\Form\FormBuilder;

/**
 * Contract for role rule evaluators.
 *
 * Evaluators decide whether a role stays active for a given context,
 * and expose a FormBuilder for configuring their payload in the UI.
 *
 * @package JobMetric\Rolix
 */
interface RuleEvaluatorContract
{
    /**
     * Evaluate the rule based on the provided context.
     *
     * @param array $rule    Payload stored on the role rule (e.g. from/to/timezone).
     * @param mixed $context Personable model or other evaluation context.
     *
     * @return bool
     */
    public function evaluate(array $rule, mixed $context): bool;

    /**
     * Unique evaluator name used for registry lookup and UI.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Form definition for configuring this evaluator.
     *
     * @return FormBuilder
     */
    public function form(): FormBuilder;

    /**
     * Built form array for UI consumers.
     *
     * Prefer form(); this remains for backward compatibility.
     *
     * @return array
     */
    public function fields(): array;
}
