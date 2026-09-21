<?php

namespace JobMetric\Rolix\Tests\Unit\Support;

use Illuminate\Support\Facades\Auth;
use JobMetric\Rolix\Models\Membership;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RoleActivityLog;
use JobMetric\Rolix\Support\ActivityActions;
use JobMetric\Rolix\Support\ActivityLogger;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for ActivityLogger and ActivityActions.
 */
class ActivityLoggerTest extends TestCase
{
    public function test_from_operation_maps_role_and_membership_actions(): void
    {
        $role = Role::factory()->create();
        $membership = new Membership;

        $this->assertSame(ActivityActions::CREATE_ROLE, ActivityActions::fromOperation('store', $role));
        $this->assertSame(ActivityActions::ASSIGN_ROLE, ActivityActions::fromOperation('store', $membership));
        $this->assertSame(ActivityActions::RESTORE_MEMBERSHIP, ActivityActions::fromOperation('restore', $membership));
        $this->assertSame(ActivityActions::FORCE_DELETE_MEMBERSHIP, ActivityActions::fromOperation('forceDelete', $membership));
    }

    public function test_log_creates_activity_row_for_role(): void
    {
        $role = Role::factory()->create();

        ActivityLogger::log('store', $role);

        $row = RoleActivityLog::query()->first();

        $this->assertNotNull($row);
        $this->assertSame(ActivityActions::CREATE_ROLE, $row->action);
        $this->assertSame($role->getMorphClass(), $row->subject_type);
        $this->assertSame($role->id, (int) $row->subject_id);
        $this->assertSame('system', $row->actor_type);
    }

    public function test_log_uses_authenticated_actor_for_membership(): void
    {
        $actor = Person::create(['name' => 'Actor']);
        Auth::login($actor);

        $person = Person::create(['name' => 'Target']);
        $role = Role::factory()->create();
        $membership = $person->assignRole($role);

        RoleActivityLog::query()->delete();

        ActivityLogger::log('destroy', $membership);

        $row = RoleActivityLog::query()->first();

        $this->assertNotNull($row);
        $this->assertSame(ActivityActions::REMOVE_ROLE, $row->action);
        $this->assertSame($actor->getMorphClass(), $row->actor_type);
        $this->assertSame($actor->id, (int) $row->actor_id);
        $this->assertSame($person->getMorphClass(), $row->target_type);
        $this->assertSame($person->id, (int) $row->target_id);
    }
}
