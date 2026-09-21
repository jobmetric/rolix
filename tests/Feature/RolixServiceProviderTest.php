<?php

namespace JobMetric\Rolix\Tests\Feature;

use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Services\Role;
use JobMetric\Rolix\Support\RoleTypeRegistry as RoleTypeRegistryService;
use JobMetric\Rolix\Support\RuleEvaluatorRegistry as RuleEvaluatorRegistryService;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for RolixServiceProvider bindings.
 */
class RolixServiceProviderTest extends TestCase
{
    /**
     * PermissionManager is resolvable from container.
     */
    public function test_permission_manager_is_resolvable(): void
    {
        $this->assertInstanceOf(PermissionManager::class, $this->app->make('rolix.permission'));
    }

    /**
     * RoleTypeRegistry is resolvable from container.
     */
    public function test_role_type_registry_is_resolvable(): void
    {
        $this->assertInstanceOf(RoleTypeRegistryService::class, $this->app->make('RoleTypeRegistry'));
    }

    /**
     * RuleEvaluatorRegistry is resolvable and has built-in drivers.
     */
    public function test_rule_evaluator_registry_is_resolvable(): void
    {
        $this->assertInstanceOf(RuleEvaluatorRegistryService::class, $this->app->make('RuleEvaluatorRegistry'));
        $this->assertTrue(RuleEvaluatorRegistry::has('time'));
        $this->assertCount(9, RuleEvaluatorRegistry::values());
    }

    /**
     * Role service is resolvable from container.
     */
    public function test_role_service_is_resolvable(): void
    {
        $this->assertInstanceOf(Role::class, $this->app->make('role'));
    }

    /**
     * Default system type is registered from config.
     */
    public function test_default_system_type_is_registered(): void
    {
        $this->assertTrue(RoleTypeRegistry::has('system'));
        $this->assertTrue(RoleTypeRegistry::isSystem('system'));
        $this->assertNull(RoleTypeRegistry::getModel('system'));
        $this->assertTrue(RoleTypeRegistry::getOption('system', 'hierarchical'));
    }
}
