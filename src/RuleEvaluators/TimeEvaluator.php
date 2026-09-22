<?php

namespace JobMetric\Rolix\RuleEvaluators;

use Carbon\Carbon;
use DateTimeZone;
use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates whether the current time is within a configured range.
 */
class TimeEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'time';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $from = $this->string($rule, 'from');
        $to = $this->string($rule, 'to');
        $timezone = $this->string($rule, 'timezone') ?: config('app.timezone', 'UTC');

        if ($from === null || $to === null) {
            return false;
        }

        $now = Carbon::now($timezone)->format('H:i');

        if ($from <= $to) {
            return $now >= $from && $now <= $to;
        }

        return $now >= $from || $now <= $to;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function form(): FormBuilder
    {
        return $this->settingsForm(function ($tab) {
            $tab->customField(function (CustomFieldBuilder $field) {
                $field::time()
                    ->name('from')
                    ->label('rolix::base.rule_evaluators.time.from.label')
                    ->info('rolix::base.rule_evaluators.time.from.info')
                    ->validation('required|date_format:H:i');
            })->customField(function (CustomFieldBuilder $field) {
                $field::time()
                    ->name('to')
                    ->label('rolix::base.rule_evaluators.time.to.label')
                    ->info('rolix::base.rule_evaluators.time.to.info')
                    ->validation('required|date_format:H:i');
            })->customField(function (CustomFieldBuilder $field) {
                $field::select()
                    ->name('timezone')
                    ->label('rolix::base.rule_evaluators.time.timezone.label')
                    ->info('rolix::base.rule_evaluators.time.timezone.info')
                    ->validation('nullable|string|timezone')
                    ->options(array_map(static fn (string $timezone) => ['label' => $timezone, 'value' => $timezone], DateTimeZone::listIdentifiers()));
            });
        });
    }
}
