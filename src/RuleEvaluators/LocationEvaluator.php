<?php

namespace JobMetric\Rolix\RuleEvaluators;

use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates location against allowed countries/cities from context or request headers.
 */
class LocationEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'location';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $countries = array_map('strtoupper', $this->list($rule, 'countries'));
        $cities = array_map('strtolower', $this->list($rule, 'cities'));

        if ($countries === [] && $cities === []) {
            return false;
        }

        $country = strtoupper((string) ($this->contextValue($context, 'country') ?? $this->request()
            ?->header('CF-IPCountry') ?? $this->request()?->header('X-Country') ?? ''));

        $city = strtolower((string) ($this->contextValue($context, 'city') ?? $this->request()
            ?->header('X-City') ?? ''));

        $countryOk = $countries === [] || ($country !== '' && in_array($country, $countries, true));
        $cityOk = $cities === [] || ($city !== '' && in_array($city, $cities, true));

        return $countryOk && $cityOk;
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
                    ->name('countries')
                    ->label('rolix::base.rule_evaluators.location.countries.label')
                    ->info('rolix::base.rule_evaluators.location.countries.info')
                    ->validation('nullable|string');
            })->customField(function (CustomFieldBuilder $field) {
                $field::text()
                    ->name('cities')
                    ->label('rolix::base.rule_evaluators.location.cities.label')
                    ->info('rolix::base.rule_evaluators.location.cities.info')
                    ->validation('nullable|string');
            });
        });
    }
}
