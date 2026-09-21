<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use JobMetric\Rolix\Facades\Membership as MembershipFacade;
use JobMetric\Rolix\Facades\Role as RoleFacade;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RoleActivityLog;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for Membership CRUD and soft deletes.
 */
class MembershipTest extends TestCase
{
    private string $permissionFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rolix_mem_perm_' . uniqid('', true) . '.php';
        file_put_contents($this->permissionFile, "<?php\nreturn ['hero' => 'permissions/hero'];\n");
        $this->app->make(PermissionManager::class)->addPermissionFile('hero', $this->permissionFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->permissionFile);
        parent::tearDown();
    }

    public function test_store_assigns_default_role_when_role_id_omitted(): void
    {
        Role::factory()->setType('system')->setIsDefault(true)->setName('Default')->create();

        $person = Person::create(['name' => 'P']);

        $response = MembershipFacade::store([
            'personable_type' => Person::class,
            'personable_id'   => $person->id,
        ]);

        $this->assertTrue($response->ok);
        $this->assertSame(1, Membership::query()->count());
        $this->assertNotNull(Membership::query()->first()->role_id);
    }

    public function test_soft_deleted_membership_is_hidden_from_has_permission(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();

        $membership = $person->assignRole($role);
        $this->assertTrue($person->hasPermission('hero'));

        MembershipFacade::destroy($membership->id);
        $person->unsetRelation('memberships');

        $this->assertFalse($person->fresh()->hasPermission('hero'));
        $this->assertSame(1, Membership::withTrashed()->count());
    }

    public function test_store_writes_activity_log(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->create();

        MembershipFacade::store([
            'personable_type' => Person::class,
            'personable_id'   => $person->id,
            'role_id'         => $role->id,
        ]);

        $this->assertTrue(RoleActivityLog::query()->where('action', 'assign_role')->exists());
    }
}
