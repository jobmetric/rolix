<?php

namespace JobMetric\Rolix\RuleEvaluators;

use Illuminate\Database\Eloquent\Model;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use JobMetric\Rolix\Models\Membership;
use Throwable;

/**
 * Evaluates membership quota for a collection key against a limit.
 */
class QuotaEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'quota';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        if (! $context instanceof Model) {
            return false;
        }

        $key = $this->string($rule, 'key');
        $limit = $this->int($rule, 'limit');

        if ($key === null || $key === '' || $limit === null) {
            return false;
        }

        $count = Membership::query()
            ->where('personable_type', $context->getMorphClass())
            ->where('personable_id', $context->getKey())
            ->where('collection', $key)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->count();

        return $count <= $limit;
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
                    ->name('key')
                    ->label('rolix::base.rule_evaluators.quota.key.label')
                    ->info('rolix::base.rule_evaluators.quota.key.info')
                    ->validation('required|string|max:255');
            })->customField(function (CustomFieldBuilder $field) {
                $field::number()
                    ->name('limit')
                    ->label('rolix::base.rule_evaluators.quota.limit.label')
                    ->validation('required|integer|min:0');
            });
        });
    }
}
