<?php

namespace JobMetric\Rolix\Tests\Unit\RuleEvaluators;

use Carbon\Carbon;
use Illuminate\Http\Request;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\RuleEvaluators\CustomExpressionEvaluator;
use JobMetric\Rolix\RuleEvaluators\EnvEvaluator;
use JobMetric\Rolix\RuleEvaluators\IpRangeEvaluator;
use JobMetric\Rolix\RuleEvaluators\LocationEvaluator;
use JobMetric\Rolix\RuleEvaluators\QuotaEvaluator;
use JobMetric\Rolix\RuleEvaluators\RoleCountEvaluator;
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
            [LocationEvaluator::class],
            [EnvEvaluator::class],
            [RoleCountEvaluator::class],
            [QuotaEvaluator::class],
            [CustomExpressionEvaluator::class],
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
        $person = Person::create(['name' => 'A', 'status' => 'active']);
        $evaluator = new UserStatusEvaluator;

        $this->assertTrue($evaluator->evaluate(['attribute' => 'status', 'expected' => 'active'], $person));
        $this->assertFalse($evaluator->evaluate(['attribute' => 'status', 'expected' => 'blocked'], $person));
    }

    public function test_ip_range_evaluator(): void
    {
        $this->app->instance('request', Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.5']));

        $evaluator = new IpRangeEvaluator;

        $this->assertTrue($evaluator->evaluate(['ranges' => '10.0.0.0/24,127.0.0.1'], null));
        $this->assertFalse($evaluator->evaluate(['ranges' => '192.168.0.0/24'], null));
    }

    public function test_location_evaluator(): void
    {
        $person = Person::create(['name' => 'A', 'country' => 'IR', 'city' => 'Tehran']);
        $evaluator = new LocationEvaluator;

        $this->assertTrue($evaluator->evaluate(['countries' => 'IR,US', 'cities' => 'Tehran'], $person));
        $this->assertFalse($evaluator->evaluate(['countries' => 'US'], $person));
    }

    public function test_env_evaluator(): void
    {
        $evaluator = new EnvEvaluator;

        $this->assertTrue($evaluator->evaluate(['environments' => 'testing,local'], null));
        $this->assertFalse($evaluator->evaluate(['environments' => 'production'], null));
    }

    public function test_role_count_evaluator(): void
    {
        $person = Person::create(['name' => 'A']);
        $role = Role::factory()->setType('system')->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->setExpiredAt(null)
            ->create();

        $evaluator = new RoleCountEvaluator;

        $this->assertTrue($evaluator->evaluate(['min' => 1, 'max' => 5], $person));
        $this->assertFalse($evaluator->evaluate(['min' => 2], $person));
    }

    public function test_quota_evaluator(): void
    {
        $person = Person::create(['name' => 'A']);
        $role = Role::factory()->setType('system')->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->setCollection('seats')
            ->setExpiredAt(null)
            ->create();

        $evaluator = new QuotaEvaluator;

        $this->assertTrue($evaluator->evaluate(['key' => 'seats', 'limit' => 2], $person));
        $this->assertFalse($evaluator->evaluate(['key' => 'seats', 'limit' => 0], $person));
    }

    public function test_custom_expression_evaluator(): void
    {
        $person = Person::create(['name' => 'A', 'status' => 'active']);
        $evaluator = new CustomExpressionEvaluator;

        $this->assertTrue($evaluator->evaluate([
            'left_key' => 'status',
            'operator' => 'eq',
            'right_value' => 'active',
        ], $person));

        $this->assertFalse($evaluator->evaluate([
            'left_key' => 'status',
            'operator' => 'eq',
            'right_value' => 'blocked',
        ], $person));

        $this->assertFalse($evaluator->evaluate([
            'left_key' => 'status;drop',
            'operator' => 'eq',
            'right_value' => 'active',
        ], $person));
    }
}
