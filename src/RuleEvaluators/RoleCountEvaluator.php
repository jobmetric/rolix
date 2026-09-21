<?php

namespace JobMetric\Rolix\RuleEvaluators;

use Illuminate\Database\Eloquent\Model;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use JobMetric\Rolix\Models\Membership;
use Throwable;

/**
 * Evaluates active membership count for the personable against min/max bounds.
 */
class RoleCountEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'role_count';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        if (! $context instanceof Model) {
            return false;
        }

        $min = $this->int($rule, 'min', 0);
        $max = $this->int($rule, 'max');
        $type = $this->string($rule, 'type');

        $query = Membership::query()
            ->where('personable_type', $context->getMorphClass())
            ->where('personable_id', $context->getKey())
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });

        if ($type !== null && $type !== '') {
            $query->whereHas('role', fn ($q) => $q->where('type', $type));
        }

        $count = $query->count();

        if ($count < $min) {
            return false;
        }

        if ($max !== null && $count > $max) {
            return false;
        }

        return true;
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
                    ->name('type')
                    ->label('rolix::base.rule_evaluators.role_count.type.label')
                    ->info('rolix::base.rule_evaluators.role_count.type.info')
                    ->validation('nullable|string|max:255');
            })->customField(function (CustomFieldBuilder $field) {
                $field::number()
                    ->name('min')
                    ->label('rolix::base.rule_evaluators.role_count.min.label')
                    ->validation('nullable|integer|min:0');
            })->customField(function (CustomFieldBuilder $field) {
                $field::number()
                    ->name('max')
                    ->label('rolix::base.rule_evaluators.role_count.max.label')
                    ->validation('nullable|integer|min:0');
            });
        });
    }
}
