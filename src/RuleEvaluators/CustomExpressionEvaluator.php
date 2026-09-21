<?php

namespace JobMetric\Rolix\RuleEvaluators;

use Illuminate\Support\Arr;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Safe comparison evaluator over whitelisted context/request keys (no eval).
 */
class CustomExpressionEvaluator extends AbstractRuleEvaluator
{
    /**
     * Allowed operators.
     *
     * @var array<int, string>
     */
    protected array $operators = [
        'eq',
        'neq',
        'in',
        'not_in',
        'gt',
        'gte',
        'lt',
        'lte',
    ];

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'custom_expression';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $leftKey = $this->string($rule, 'left_key');
        $operator = strtolower((string) $this->string($rule, 'operator'));
        $rightValue = Arr::get($rule, 'right_value');

        if ($leftKey === null || $leftKey === '' || ! in_array($operator, $this->operators, true)) {
            return false;
        }

        if (! $this->isSafeKey($leftKey)) {
            return false;
        }

        $left = $this->resolveLeft($leftKey, $context);

        return match ($operator) {
            'eq' => (string) $left === (string) $rightValue,
            'neq' => (string) $left !== (string) $rightValue,
            'in' => in_array((string) $left, $this->normalizeRightList($rightValue), true),
            'not_in' => ! in_array((string) $left, $this->normalizeRightList($rightValue), true),
            'gt' => is_numeric($left) && is_numeric($rightValue) && (float) $left > (float) $rightValue,
            'gte' => is_numeric($left) && is_numeric($rightValue) && (float) $left >= (float) $rightValue,
            'lt' => is_numeric($left) && is_numeric($rightValue) && (float) $left < (float) $rightValue,
            'lte' => is_numeric($left) && is_numeric($rightValue) && (float) $left <= (float) $rightValue,
            default => false,
        };
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function form(): FormBuilder
    {
        return $this->settingsForm(function ($tab) {
            $tab->customField(function (CustomFieldBuilder $field) {
                $field::text()
                    ->name('left_key')
                    ->label('rolix::base.rule_evaluators.custom_expression.left_key.label')
                    ->info('rolix::base.rule_evaluators.custom_expression.left_key.info')
                    ->validation('required|string|max:255');
            })->customField(function (CustomFieldBuilder $field) {
                $field::select()
                    ->name('operator')
                    ->label('rolix::base.rule_evaluators.custom_expression.operator.label')
                    ->validation('required|string')
                    ->options([
                        [
                            'label' => 'eq',
                            'value' => 'eq',
                        ],
                        [
                            'label' => 'neq',
                            'value' => 'neq',
                        ],
                        [
                            'label' => 'in',
                            'value' => 'in',
                        ],
                        [
                            'label' => 'not_in',
                            'value' => 'not_in',
                        ],
                        [
                            'label' => 'gt',
                            'value' => 'gt',
                        ],
                        [
                            'label' => 'gte',
                            'value' => 'gte',
                        ],
                        [
                            'label' => 'lt',
                            'value' => 'lt',
                        ],
                        [
                            'label' => 'lte',
                            'value' => 'lte',
                        ],
                    ]);
            })->customField(function (CustomFieldBuilder $field) {
                $field::text()
                    ->name('right_value')
                    ->label('rolix::base.rule_evaluators.custom_expression.right_value.label')
                    ->validation('required|string|max:1000');
            });
        });
    }

    /**
     * Only allow simple attribute paths (letters, numbers, underscore, dot).
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isSafeKey(string $key): bool
    {
        return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $key);
    }

    /**
     * Resolve left operand from context or request.
     *
     * @param string $key
     * @param mixed $context
     *
     * @return mixed
     */
    protected function resolveLeft(string $key, mixed $context): mixed
    {
        if ($key === 'request.ip') {
            return $this->request()?->ip();
        }

        if (str_starts_with($key, 'request.')) {
            return data_get($this->request()?->all() ?? [], substr($key, 8));
        }

        return $this->contextValue($context, $key);
    }

    /**
     * Normalize right side list for in/not_in.
     *
     * @param mixed $rightValue
     *
     * @return array<int, string>
     */
    protected function normalizeRightList(mixed $rightValue): array
    {
        if (is_array($rightValue)) {
            return array_map('strval', $rightValue);
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $rightValue) ?: [])));
    }
}
