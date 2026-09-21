<?php

namespace JobMetric\Rolix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Rolix\Support\RuleEvaluatorRegistry
 *
 * @method static \JobMetric\Rolix\Support\RuleEvaluatorRegistry register(string $class)
 * @method static \JobMetric\Rolix\Support\RuleEvaluatorRegistry unregister(string $name)
 * @method static bool has(string $nameOrClass)
 * @method static \JobMetric\Rolix\Contracts\RuleEvaluatorContract|null get(string $nameOrClass)
 * @method static string|null resolveClass(string $nameOrClass)
 * @method static array all()
 * @method static array values()
 * @method static array|null formFor(string $nameOrClass)
 * @method static \JobMetric\Rolix\Support\RuleEvaluatorRegistry clear()
 */
class RuleEvaluatorRegistry extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'RuleEvaluatorRegistry';
    }
}
