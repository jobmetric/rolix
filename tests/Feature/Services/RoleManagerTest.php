<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use JobMetric\Rolix\Exceptions\RoleTypeNotFoundException;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Services\RoleManager;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for RoleManager.
 */
class RoleManagerTest extends TestCase
{
    /**
     * create defaults type to system when omitted.
     */
    public function test_create_defaults_type_to_system(): void
    {
        $manager = new RoleManager;

        $role = $manager->create([
            'name'  => 'Admin',
            'allow' => ['dashboard.view'],
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertSame('system', $role->type);
        $this->assertSame('Admin', $role->name);
        $this->assertSame(['dashboard.view'], $role->allow);
    }

    /**
     * create with unknown type throws.
     */
    public function test_create_with_unknown_type_throws(): void
    {
        $manager = new RoleManager;

        $this->expectException(RoleTypeNotFoundException::class);

        $manager->create([
            'type' => 'unknown-type',
            'name' => 'Broken',
        ]);
    }
}
