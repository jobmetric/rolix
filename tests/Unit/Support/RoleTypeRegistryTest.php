<?php

namespace JobMetric\Rolix\Tests\Unit\Support;

use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Support\RoleTypeRegistry;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Tests for RoleTypeRegistry.
 */
class RoleTypeRegistryTest extends TestCase
{
    /**
     * register adds a type with options and returns self.
     */
    public function test_register_adds_type_with_options_and_returns_self(): void
    {
        $registry = new RoleTypeRegistry;

        $result = $registry->register('tenant', [
            'label' => 'Tenant',
            'model' => Tenant::class,
        ]);

        $this->assertSame($registry, $result);
        $this->assertSame([
            'label' => 'Tenant',
            'model' => Tenant::class,
        ], $registry->get('tenant'));
    }

    /**
     * register with empty options adds type with empty array.
     */
    public function test_register_with_empty_options(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('custom');

        $this->assertTrue($registry->has('custom'));
        $this->assertSame([], $registry->get('custom'));
    }

    /**
     * register merges options when type already exists.
     */
    public function test_register_merges_options_for_existing_type(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('tenant', [
            'label'       => 'Tenant',
            'description' => 'Old',
        ]);
        $registry->register('tenant', [
            'description' => 'New',
            'model'       => Tenant::class,
        ]);

        $this->assertSame([
            'label'       => 'Tenant',
            'description' => 'New',
            'model'       => Tenant::class,
        ], $registry->get('tenant'));
    }

    /**
     * getModel returns null when model option is missing (system type).
     */
    public function test_getModel_returns_null_when_model_missing(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('system');

        $this->assertNull($registry->getModel('system'));
    }

    /**
     * getModel returns the registered model class.
     */
    public function test_getModel_returns_model_when_set(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('tenant', ['model' => Tenant::class]);

        $this->assertSame(Tenant::class, $registry->getModel('tenant'));
    }

    /**
     * isSystem returns true when model is not set.
     */
    public function test_isSystem_returns_true_when_model_missing(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('system');

        $this->assertTrue($registry->isSystem('system'));
    }

    /**
     * isSystem returns false when model is set.
     */
    public function test_isSystem_returns_false_when_model_set(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('tenant', ['model' => Tenant::class]);

        $this->assertFalse($registry->isSystem('tenant'));
    }

    /**
     * ensure throws when type is not registered.
     */
    public function test_ensure_throws_when_type_not_registered(): void
    {
        $registry = new RoleTypeRegistry;

        $this->expectException(RoleTypeNotFoundException::class);

        $registry->ensure('unknown');
    }

    /**
     * ensure returns self when type is registered.
     */
    public function test_ensure_returns_self_when_registered(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('system');

        $this->assertSame($registry, $registry->ensure('system'));
    }

    /**
     * clear removes all types and returns self.
     */
    public function test_clear_removes_all_and_returns_self(): void
    {
        $registry = new RoleTypeRegistry;
        $registry->register('system')->register('tenant');

        $result = $registry->clear();

        $this->assertSame($registry, $result);
        $this->assertSame([], $registry->all());
    }
}
