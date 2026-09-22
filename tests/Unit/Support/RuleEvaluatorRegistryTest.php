<?php

namespace JobMetric\Rolix\Tests\Unit\Support;

use JobMetric\Rolix\Facades\RuleEvaluatorRegistry as RuleEvaluatorRegistryFacade;
use JobMetric\Rolix\RuleEvaluators\TimeEvaluator;
use JobMetric\Rolix\Support\RuleEvaluatorRegistry;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Tests for RuleEvaluatorRegistry.
 */
class RuleEvaluatorRegistryTest extends TestCase
{
    public function test_builtin_drivers_are_registered(): void
    {
        $names = RuleEvaluatorRegistryFacade::values();

        $this->assertContains('time', $names);
        $this->assertContains('weekday', $names);
        $this->assertContains('ip_range', $names);
        $this->assertContains('env', $names);
        $this->assertCount(4, $names);
    }

    public function test_resolve_by_name_and_fqcn(): void
    {
        $byName = RuleEvaluatorRegistryFacade::get('time');
        $byClass = RuleEvaluatorRegistryFacade::get(TimeEvaluator::class);

        $this->assertInstanceOf(TimeEvaluator::class, $byName);
        $this->assertInstanceOf(TimeEvaluator::class, $byClass);
        $this->assertTrue(RuleEvaluatorRegistryFacade::has('time'));
        $this->assertTrue(RuleEvaluatorRegistryFacade::has(TimeEvaluator::class));
    }

    public function test_form_for_returns_ui_payload(): void
    {
        $payload = RuleEvaluatorRegistryFacade::formFor('time');

        $this->assertIsArray($payload);
        $this->assertSame(TimeEvaluator::class, $payload['driver']);
        $this->assertSame('time', $payload['name']);
        $this->assertNotEmpty($payload['form']);
    }

    public function test_register_and_clear(): void
    {
        $registry = new RuleEvaluatorRegistry;
        $registry->register(TimeEvaluator::class);

        $this->assertTrue($registry->has('time'));
        $registry->clear();
        $this->assertFalse($registry->has('time'));
    }
}
