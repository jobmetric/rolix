<?php

namespace JobMetric\Rolix\RuleEvaluators;

use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates a status-like attribute on the personable context.
 */
class UserStatusEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'user_status';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $attribute = $this->string($rule, 'attribute') ?: 'status';
        $expected = $this->string($rule, 'expected');

        if ($expected === null) {
            return false;
        }

        $actual = $this->contextValue($context, $attribute);

        if (is_bool($actual)) {
            $actual = $actual ? '1' : '0';
        }

        return (string) $actual === $expected;
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
                    ->name('attribute')
                    ->label('rolix::base.rule_evaluators.user_status.attribute.label')
                    ->info('rolix::base.rule_evaluators.user_status.attribute.info')
                    ->validation('nullable|string|max:255');
            })->customField(function (CustomFieldBuilder $field) {
                $field::text()
                    ->name('expected')
                    ->label('rolix::base.rule_evaluators.user_status.expected.label')
                    ->info('rolix::base.rule_evaluators.user_status.expected.info')
                    ->validation('required|string|max:255');
            });
        });
    }
}
