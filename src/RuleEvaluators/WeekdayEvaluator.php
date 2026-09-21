<?php

namespace JobMetric\Rolix\RuleEvaluators;

use Carbon\Carbon;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates whether the current weekday is in the allowed list.
 */
class WeekdayEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'weekday';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $days = array_map('strtolower', $this->list($rule, 'days'));

        if ($days === []) {
            return false;
        }

        $timezone = $this->string($rule, 'timezone') ?: config('app.timezone', 'UTC');
        $current = strtolower(Carbon::now($timezone)->englishDayOfWeek);

        return in_array($current, $days, true) || in_array((string) Carbon::now($timezone)->dayOfWeek, $days, true);
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
                    ->name('days')
                    ->label('rolix::base.rule_evaluators.weekday.days.label')
                    ->info('rolix::base.rule_evaluators.weekday.days.info')
                    ->validation('required')
                    ->multiple()
                    ->options([
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.monday',
                            'value' => 'monday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.tuesday',
                            'value' => 'tuesday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.wednesday',
                            'value' => 'wednesday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.thursday',
                            'value' => 'thursday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.friday',
                            'value' => 'friday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.saturday',
                            'value' => 'saturday',
                        ],
                        [
                            'label' => 'rolix::base.rule_evaluators.weekday.options.sunday',
                            'value' => 'sunday',
                        ],
                    ]);
            })->customField(function (CustomFieldBuilder $field) {
                $field::text()
                    ->name('timezone')
                    ->label('rolix::base.rule_evaluators.weekday.timezone.label')
                    ->validation('nullable|string|timezone');
            });
        });
    }
}
