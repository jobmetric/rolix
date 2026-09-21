<?php

namespace JobMetric\Rolix\Tests\Unit\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Http\Middleware\EnsurePermission;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Permission evaluation extensions: owner, collection, wildcard, Gate, middleware.
 */
class HasRolePermissionExtensionsTest extends TestCase
{
    public function test_owner_grants_all_permissions_in_context_only(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'P']);
        $tenant = Tenant::create(['name' => 'T']);
        $role = Role::factory()->setType('tenant')->setAllow([])->create();

        $person->assignRole($role, $tenant, ['is_owner' => true]);

        $this->assertTrue($person->hasPermission('anything', $tenant));
        $this->assertFalse($person->hasPermission('anything'));
    }

    public function test_collection_filters_memberships(): void
    {
        $person = Person::create(['name' => 'P']);
        $roleA = Role::factory()->setType('system')->setAllow(['a.view'])->create();
        $roleB = Role::factory()->setType('system')->setAllow(['b.view'])->create();

        $person->assignRole($roleA, null, ['collection' => 'alpha']);
        $person->assignRole($roleB, null, ['collection' => 'beta']);

        $this->assertTrue($person->hasPermission('a.view', null, 'alpha'));
        $this->assertFalse($person->hasPermission('b.view', null, 'alpha'));
        $this->assertTrue($person->hasPermission('a.view'));
        $this->assertTrue($person->hasPermission('b.view'));
    }

    public function test_wildcard_allow_and_deny_precedence(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero.*'])->setDeny(['hero.secret'])->create();
        $person->assignRole($role);

        $this->assertTrue($person->hasPermission('hero.view'));
        $this->assertFalse($person->hasPermission('hero.secret'));
        $this->assertFalse($person->hasPermission('other.view'));
    }

    public function test_gate_before_uses_has_permission(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $person->assignRole($role);

        $this->actingAs($person);

        $this->assertTrue(Gate::forUser($person)->allows('hero'));
        $this->assertFalse(Gate::forUser($person)->allows('missing'));
    }

    public function test_middleware_aborts_without_permission(): void
    {
        $person = Person::create(['name' => 'P']);
        $this->actingAs($person);

        $middleware = new EnsurePermission;
        $request = Request::create('/');
        $request->setUserResolver(fn () => $person);

        $this->expectException(HttpException::class);
        $middleware->handle($request, fn () => response('ok'), 'hero');
    }

    public function test_has_members_trait_assign_and_check(): void
    {
        RoleTypeRegistry::register('tenant', ['model' => Tenant::class]);

        $person = Person::create(['name' => 'P']);
        $tenant = Tenant::create(['name' => 'T']);
        $role = Role::factory()->setType('tenant')->setAllow(['tenant.view'])->create();

        $tenant->assignMember($person, $role);

        $this->assertTrue($tenant->hasMember($person, $role));
        $this->assertTrue($person->hasPermission('tenant.view', $tenant));
    }

    public function test_helper_rolix_has_permission(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $person->assignRole($role);

        $this->assertTrue(rolix_has_permission($person, 'hero'));
        $this->assertFalse(rolix_has_permission(null, 'hero'));
    }
}
