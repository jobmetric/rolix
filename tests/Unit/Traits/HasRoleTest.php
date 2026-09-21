<?php

namespace JobMetric\Rolix\Tests\Unit\Traits;

use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for HasRole trait.
 */
class HasRoleTest extends TestCase
{
    /**
     * Without context only system membership permissions apply.
     */
    public function test_without_context_uses_system_membership_only(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'Alice']);
        $tenant = Tenant::create(['name' => 'Acme']);

        $systemRole = Role::factory()->setType('system')->setAllow(['system.view'])->create();
        $tenantRole = Role::factory()->setType('tenant')->setAllow(['tenant.view'])->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($systemRole->id)
            ->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->setMemberable(Tenant::class, $tenant->id)
            ->setRoleId($tenantRole->id)
            ->create();

        $this->assertTrue($person->hasPermission('system.view'));
        $this->assertFalse($person->hasPermission('tenant.view'));
    }

    /**
     * With context only that entity's membership permissions apply.
     */
    public function test_with_context_uses_scoped_membership_only(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'Alice']);
        $tenant = Tenant::create(['name' => 'Acme']);

        $systemRole = Role::factory()->setType('system')->setAllow(['system.view'])->create();
        $tenantRole = Role::factory()->setType('tenant')->setAllow(['tenant.view'])->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($systemRole->id)
            ->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->setMemberable(Tenant::class, $tenant->id)
            ->setRoleId($tenantRole->id)
            ->create();

        $this->assertTrue($person->hasPermission('tenant.view', $tenant));
        $this->assertFalse($person->hasPermission('system.view', $tenant));
    }

    /**
     * Membership allow overrides are included in evaluation.
     */
    public function test_membership_allow_grants_permission(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('system')->setAllow([])->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->setAllow(['custom.action'])
            ->create();

        $this->assertTrue($person->hasPermission('custom.action'));
    }
}
