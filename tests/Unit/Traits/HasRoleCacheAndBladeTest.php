<?php

namespace JobMetric\Rolix\Tests\Unit\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Support\PermissionCache;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Permission cache + Blade @rolixCan coverage.
 */
class HasRoleCacheAndBladeTest extends TestCase
{
    public function test_request_memo_reuses_permissions_within_same_instance(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $person->assignRole($role);

        $this->assertTrue($person->hasPermission('hero'));
        $this->assertTrue($person->hasPermission('hero'));
        $this->assertSame(
            $person->getPermissions(),
            $person->getPermissions()
        );
    }

    public function test_shared_cache_invalidates_after_role_permission_change(): void
    {
        config(['rolix.cache.enabled' => true, 'rolix.cache.ttl' => 60]);
        Cache::flush();

        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $person->assignRole($role);

        $this->assertTrue($person->hasPermission('hero'));

        $role->allow = ['other'];
        $role->save();
        PermissionCache::bumpRoles();

        $fresh = $person->fresh();
        $this->assertFalse($fresh->hasPermission('hero'));
        $this->assertTrue($fresh->hasPermission('other'));
    }

    public function test_blade_rolix_can_directive(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $person->assignRole($role);

        $this->actingAs($person);

        $this->assertTrue(Blade::check('rolixCan', 'hero'));
        $this->assertFalse(Blade::check('rolixCan', 'missing'));
    }
}
