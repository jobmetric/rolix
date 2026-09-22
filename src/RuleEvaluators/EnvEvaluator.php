<?php

namespace JobMetric\Rolix\RuleEvaluators;

use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates whether the application environment is allowed.
 */
class EnvEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'env';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $environments = array_map('strtolower', $this->list($rule, 'environments'));

        if ($environments === []) {
            return false;
        }

        return in_array(strtolower((string) app()->environment()), $environments, true);
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function form(): FormBuilder
    {
        return $this->settingsForm(function ($tab) {
            $tab->customField(function (CustomFieldBuilder $field) {
                $field::select()
                    ->name('environments')
                    ->label('rolix::base.rule_evaluators.env.environments.label')
                    ->info('rolix::base.rule_evaluators.env.environments.info')
                    ->validation('required')
                    ->multiple()
                    ->options([
                        ['label' => 'rolix::base.rule_evaluators.env.options.local', 'value' => 'local'],
                        ['label' => 'rolix::base.rule_evaluators.env.options.development', 'value' => 'development'],
                        ['label' => 'rolix::base.rule_evaluators.env.options.testing', 'value' => 'testing'],
                        ['label' => 'rolix::base.rule_evaluators.env.options.staging', 'value' => 'staging'],
                        ['label' => 'rolix::base.rule_evaluators.env.options.production', 'value' => 'production'],
                    ]);
            });
        });
    }
}
