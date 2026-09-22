<?php

namespace JobMetric\Rolix\Tests\Unit\RuleEvaluators;

use Carbon\Carbon;
use Illuminate\Http\Request;
use JobMetric\Rolix\RuleEvaluators\EnvEvaluator;
use JobMetric\Rolix\RuleEvaluators\IpRangeEvaluator;
use JobMetric\Rolix\RuleEvaluators\TimeEvaluator;
use JobMetric\Rolix\RuleEvaluators\UserStatusEvaluator;
use JobMetric\Rolix\RuleEvaluators\WeekdayEvaluator;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for built-in rule evaluators.
 */
class RuleEvaluatorsTest extends TestCase
{
    /**
     * @dataProvider formDriversProvider
     */
    public function test_form_builds_non_empty_array(string $class): void
    {
        $evaluator = app($class);
        $form = $evaluator->fields();

        $this->assertIsArray($form);
        $this->assertNotEmpty($form);
    }

    public static function formDriversProvider(): array
    {
        return [
            [TimeEvaluator::class],
            [WeekdayEvaluator::class],
            [UserStatusEvaluator::class],
            [IpRangeEvaluator::class],
            [EnvEvaluator::class],
        ];
    }

    public function test_time_evaluator_within_and_outside_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00', 'UTC'));

        $evaluator = new TimeEvaluator;

        $this->assertTrue($evaluator->evaluate([
            'from' => '08:00',
            'to' => '18:00',
            'timezone' => 'UTC',
        ], null));

        $this->assertFalse($evaluator->evaluate([
            'from' => '11:00',
            'to' => '18:00',
            'timezone' => 'UTC',
        ], null));

        Carbon::setTestNow();
    }

    public function test_weekday_evaluator(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00', 'UTC')); // Monday

        $evaluator = new WeekdayEvaluator;

        $this->assertTrue($evaluator->evaluate(['days' => 'monday,tuesday', 'timezone' => 'UTC'], null));
        $this->assertFalse($evaluator->evaluate(['days' => 'sunday', 'timezone' => 'UTC'], null));

        Carbon::setTestNow();
    }

    public function test_user_status_evaluator(): void
    {
        $person = Person::create(['name' => 'A', 'status' => true]);
        $evaluator = new UserStatusEvaluator;

        $this->assertTrue($evaluator->evaluate(['expected' => '1'], $person));
        $this->assertFalse($evaluator->evaluate(['expected' => '0'], $person));
    }

    public function test_ip_range_evaluator(): void
    {
        $this->app->instance('request', Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.5']));

        $evaluator = new IpRangeEvaluator;

        $this->assertTrue($evaluator->evaluate(['ranges' => '10.0.0.0/24,127.0.0.1'], null));
        $this->assertFalse($evaluator->evaluate(['ranges' => '192.168.0.0/24'], null));
    }

    public function test_env_evaluator(): void
    {
        $evaluator = new EnvEvaluator;

        $this->assertTrue($evaluator->evaluate(['environments' => ['testing', 'local']], null));
        $this->assertFalse($evaluator->evaluate(['environments' => 'production'], null));
    }
}
