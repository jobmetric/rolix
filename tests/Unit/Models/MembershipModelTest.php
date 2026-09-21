<?php

namespace JobMetric\Rolix\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\Rolix\Exceptions\MembershipMemberableMismatchException;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for Membership model.
 */
class MembershipModelTest extends TestCase
{
    /**
     * getTable returns config table name.
     */
    public function test_getTable_returns_config_value(): void
    {
        $this->assertSame('memberships', (new Membership)->getTable());
    }

    /**
     * fillable contains expected attributes.
     */
    public function test_fillable_contains_expected_attributes(): void
    {
        $expected = [
            'personable_type',
            'personable_id',
            'memberable_type',
            'memberable_id',
            'role_id',
            'collection',
            'is_owner',
            'expired_at',
            'allow',
            'deny',
        ];
        $this->assertSame($expected, (new Membership)->getFillable());
    }

    /**
     * personable and memberable relations return MorphTo.
     */
    public function test_morph_relations_return_morph_to(): void
    {
        $membership = new Membership;
        $this->assertInstanceOf(MorphTo::class, $membership->personable());
        $this->assertInstanceOf(MorphTo::class, $membership->memberable());
    }

    /**
     * role relation returns BelongsTo Role.
     */
    public function test_role_relation_returns_belongs_to(): void
    {
        $membership = new Membership;
        $relation = $membership->role();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(Role::class, $relation->getRelated()::class);
    }

    /**
     * System membership persists with null memberable.
     */
    public function test_create_system_membership_with_null_memberable(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('system')->setAllow(['dashboard.view'])->create();

        $membership = Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->system()
            ->setRoleId($role->id)
            ->create();

        $this->assertNull($membership->memberable_type);
        $this->assertNull($membership->memberable_id);
        $this->assertNull($membership->memberable_resource);
    }

    /**
     * system scope returns only null-memberable rows.
     */
    public function test_system_scope_filters_null_memberable(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $tenant = Tenant::create(['name' => 'Acme']);
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $systemRole = Role::factory()->setType('system')->create();
        $tenantRole = Role::factory()->setType('tenant')->create();

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

        $this->assertSame(1, Membership::query()->system()->count());
        $this->assertSame(1, Membership::query()->forMemberable($tenant)->count());
    }

    /**
     * System role with filled memberable throws.
     */
    public function test_system_role_with_memberable_throws(): void
    {
        $person = Person::create(['name' => 'Alice']);
        $tenant = Tenant::create(['name' => 'Acme']);
        $role = Role::factory()->setType('system')->create();

        $this->expectException(MembershipMemberableMismatchException::class);

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->setMemberable(Tenant::class, $tenant->id)
            ->setRoleId($role->id)
            ->create();
    }

    /**
     * Model role with null memberable throws.
     */
    public function test_model_role_with_null_memberable_throws(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'Alice']);
        $role = Role::factory()->setType('tenant')->create();

        $this->expectException(MembershipMemberableMismatchException::class);

        Membership::factory()->setPersonable(Person::class, $person->id)->system()->setRoleId($role->id)->create();
    }

    /**
     * Model role with wrong memberable type throws.
     */
    public function test_model_role_with_wrong_memberable_type_throws(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'Alice']);
        $other = Person::create(['name' => 'Other']);
        $role = Role::factory()->setType('tenant')->create();

        $this->expectException(MembershipMemberableMismatchException::class);

        Membership::factory()
            ->setPersonable(Person::class, $person->id)
            ->setMemberable(Person::class, $other->id)
            ->setRoleId($role->id)
            ->create();
    }
}
