<?php

namespace JobMetric\Rolix\RuleEvaluators;

use JobMetric\CustomField\CustomFieldBuilder;
use JobMetric\Form\FormBuilder;
use JobMetric\Rolix\Contracts\AbstractRuleEvaluator;
use Throwable;

/**
 * Evaluates whether the request IP is within configured ranges/CIDRs.
 */
class IpRangeEvaluator extends AbstractRuleEvaluator
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'ip_range';
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $rule, mixed $context): bool
    {
        $ranges = $this->list($rule, 'ranges');
        $ip = $this->request()?->ip();

        if ($ip === null || $ranges === []) {
            return false;
        }

        foreach ($ranges as $range) {
            if ($this->ipMatches($ip, $range)) {
                return true;
            }
        }

        return false;
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
                    ->name('ranges')
                    ->label('rolix::base.rule_evaluators.ip_range.ranges.label')
                    ->info('rolix::base.rule_evaluators.ip_range.ranges.info')
                    ->validation('required|string');
            });
        });
    }

    /**
     * Check whether an IP matches an exact address or CIDR.
     *
     * @param string $ip
     * @param string $range
     *
     * @return bool
     */
    protected function ipMatches(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $mask] = explode('/', $range, 2);

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || ! filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $mask = (int) $mask;

        if ($mask < 0 || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
