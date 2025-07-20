<?php

namespace JobMetric\Rolix\RuleEvaluators;

use JobMetric\Rolix\Contracts\RuleEvaluatorContract;
use Carbon\Carbon;

/**
 * Class TimeRangeEvaluator
 *
 * Evaluates whether the current time is within a specified range.
 * Useful for time-based access rules, such as allowing actions only
 * during business hours.
 */
class TimeRangeEvaluator implements RuleEvaluatorContract
{
    /**
     * Evaluate the rule based on the current time and provided range.
     *
     * Expected format of $rule:
     * [
     *     'field' => 'time',
     *     'operator' => 'between',
     *     'value' => '["08:00", "18:00"]'
     * ]
     *
     * @param array $rule    The rule definition.
     * @param mixed $context Context is ignored in this evaluator.
     *
     * @return bool          True if current time is within range, false otherwise.
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        if (!isset($rule['value'])) {
            return false;
        }

        $range = json_decode($rule['value'], true);

        if (!is_array($range) || count($range) !== 2) {
            return false;
        }

        [$start, $end] = $range;

        $now = Carbon::now()->format('H:i');

        return $now >= $start && $now <= $end;
    }

    /**
     * Get the unique name of this evaluator.
     *
     * This name is used to reference the evaluator in rule definitions.
     *
     * @return string
     */
    public function name(): string
    {
        return 'time_range';
    }

    /**
     * Get the required fields for this evaluator.
     *
     * Useful for UI generation or validation when defining rules.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            [
                'name' => 'value',
                'type' => 'time_range',
                'label' => 'Time Range',
                'description' => 'Select a valid time range in format ["HH:MM", "HH:MM"]'
            ]
        ];
    }
}
