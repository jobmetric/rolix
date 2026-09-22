<?php

namespace JobMetric\Rolix\Contracts;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JobMetric\Form\FormBuilder;
use Throwable;

/**
 * Base class for rule evaluators with FormBuilder settings.
 *
 * @package JobMetric\Rolix
 */
abstract class AbstractRuleEvaluator implements RuleEvaluatorContract
{
    /**
     * Unique evaluator name.
     *
     * @return string
     */
    abstract public function name(): string;

    /**
     * Evaluate the rule.
     *
     * @param array $rule
     * @param mixed $context
     *
     * @return bool
     */
    abstract public function evaluate(array $rule, mixed $context): bool;

    /**
     * FormBuilder for evaluator settings.
     *
     * @return FormBuilder
     */
    abstract public function form(): FormBuilder;

    /**
     * Built form definition for UI.
     *
     * @return array
     * @throws Throwable
     */
    public function fields(): array
    {
        return $this->form()->build()->toArray();
    }

    /**
     * Read a string value from the rule payload.
     *
     * @param array $rule
     * @param string $key
     * @param string|null $default
     *
     * @return string|null
     */
    protected function string(array $rule, string $key, ?string $default = null): ?string
    {
        $value = Arr::get($rule, $key, $default);

        if ($value === null) {
            return null;
        }

        return is_string($value) ? trim($value) : (string) $value;
    }

    /**
     * Read an integer value from the rule payload.
     *
     * @param array $rule
     * @param string $key
     * @param int|null $default
     *
     * @return int|null
     */
    protected function int(array $rule, string $key, ?int $default = null): ?int
    {
        $value = Arr::get($rule, $key, $default);

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Read a list from a CSV string or array payload value.
     *
     * @param array $rule
     * @param string $key
     *
     * @return array<int, string>
     */
    protected function list(array $rule, string $key): array
    {
        $value = Arr::get($rule, $key);

        if (is_array($value)) {
            return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $value), static fn (
                $item
            ) => $item !== ''));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $value) ?: []), static fn ($item
        ) => $item !== ''));
    }

    /**
     * Build timezone select options with their current UTC offsets.
     *
     * @return array<int, array{label: string, value: string}>
     */
    protected function timezoneOptions(): array
    {
        $now = new DateTimeImmutable;

        return array_map(static function (string $name) use ($now): array {
            $timezone = new DateTimeZone($name);
            $seconds = $timezone->getOffset($now);
            $sign = $seconds < 0 ? '-' : '+';
            $minutes = (int) abs($seconds / 60);
            $offset = sprintf('%s%02d:%02d', $sign, intdiv($minutes, 60), $minutes % 60);

            return ['label' => "(UTC{$offset}) {$name}", 'value' => $name];
        }, DateTimeZone::listIdentifiers());
    }

    /**
     * Current HTTP request when available.
     *
     * @return Request|null
     */
    protected function request(): ?Request
    {
        try {
            return app('request');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Resolve an attribute from a model or array context.
     *
     * @param mixed $context
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function contextValue(mixed $context, string $key, mixed $default = null): mixed
    {
        if ($context instanceof Model) {
            return $context->getAttribute($key) ?? $default;
        }

        if (is_array($context)) {
            return Arr::get($context, $key, $default);
        }

        if (is_object($context) && isset($context->{$key})) {
            return $context->{$key};
        }

        return $default;
    }

    /**
     * Build a single-tab settings form with the given field callbacks.
     *
     * @param callable $fieldsCallback Receives the tab builder.
     *
     * @return FormBuilder
     * @throws Throwable
     */
    protected function settingsForm(callable $fieldsCallback): FormBuilder
    {
        return (new FormBuilder)->name($this->name())->tab(function ($tab) use ($fieldsCallback) {
            $tab->id('settings')->label(trans('rolix::base.rule_evaluators.common.settings_tab'))->startPosition();

            $fieldsCallback($tab);
        });
    }
}
