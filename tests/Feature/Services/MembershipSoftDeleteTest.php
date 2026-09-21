<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use JobMetric\Rolix\Facades\Membership as MembershipFacade;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RoleActivityLog;
use JobMetric\Rolix\Support\ActivityActions;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Soft-delete restore and force-delete coverage for Membership.
 */
class MembershipSoftDeleteTest extends TestCase
{
    public function test_restore_membership_regrants_permission_and_logs(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $membership = $person->assignRole($role);

        MembershipFacade::destroy($membership->id);
        $this->assertFalse($person->fresh()->hasPermission('hero'));

        $response = MembershipFacade::restore($membership->id);

        $this->assertTrue($response->ok);
        $this->assertTrue($person->fresh()->hasPermission('hero'));
        $this->assertTrue(
            RoleActivityLog::query()->where('action', ActivityActions::RESTORE_MEMBERSHIP)->exists()
        );
    }

    public function test_force_delete_removes_membership_permanently(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->setAllow(['hero'])->create();
        $membership = $person->assignRole($role);

        MembershipFacade::destroy($membership->id);
        $response = MembershipFacade::forceDelete($membership->id);

        $this->assertTrue($response->ok);
        $this->assertSame(0, Membership::withTrashed()->count());
        $this->assertFalse($person->fresh()->hasPermission('hero'));
        $this->assertTrue(
            RoleActivityLog::query()->where('action', ActivityActions::FORCE_DELETE_MEMBERSHIP)->exists()
        );
    }
}
