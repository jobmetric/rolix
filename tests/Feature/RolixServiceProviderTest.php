<?php

namespace JobMetric\Rolix\Tests\Feature;

use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Services\Role;
use JobMetric\Rolix\Support\RoleTypeRegistry as RoleTypeRegistryService;
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
