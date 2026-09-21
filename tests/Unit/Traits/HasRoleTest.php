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

    /**
     * Super membership grants any permission regardless of context.
     */
    public function test_super_membership_grants_all_permissions(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'Alice']);
        $tenant = Tenant::create(['name' => 'Acme']);
        $super = Role::factory()->super()->setName('Super')->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($super->id)
            ->create();

        $this->assertTrue($person->hasPermission('anything.at.all'));
        $this->assertTrue($person->hasPermission('tenant.view', $tenant));
    }

    /**
     * Deny takes precedence over allow.
     */
    public function test_deny_takes_precedence_over_allow(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->setDeny(['hero'])->create();

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->create();

        $this->assertFalse($person->hasPermission('hero'));
    }

    /**
     * Role rules resolved by short driver name can block permissions.
     */
    public function test_role_rule_short_name_blocks_permission_when_failing(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();

        $role->rules()->create([
            'driver' => 'env',
            'payload' => ['environments' => 'production'],
        ]);

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->setExpiredAt(null)
            ->create();

        $this->assertFalse($person->hasPermission('hero'));
    }

    /**
     * Role rules resolved by short driver name allow permissions when passing.
     */
    public function test_role_rule_short_name_allows_permission_when_passing(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();

        $role->rules()->create([
            'driver' => 'env',
            'payload' => ['environments' => 'testing'],
        ]);

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->setExpiredAt(null)
            ->create();

        $this->assertTrue($person->hasPermission('hero'));
    }
}
