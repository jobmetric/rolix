<?php

namespace JobMetric\Rolix\Tests\Unit\Http;

use Illuminate\Http\Request;
use JobMetric\Rolix\Http\Requests\Membership\StoreMembershipRequest;
use JobMetric\Rolix\Http\Requests\Role\StoreRoleRequest;
use JobMetric\Rolix\Http\Resources\MembershipResource;
use JobMetric\Rolix\Http\Resources\RoleResource;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Tests\Stubs\Person;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Smoke tests for FormRequests rules and API Resources.
 */
class RequestAndResourceTest extends TestCase
{
    public function test_store_role_request_requires_name(): void
    {
        $request = new StoreRoleRequest;

        $this->assertArrayHasKey('name', $request->rules());
        $this->assertStringContainsString('required', $request->rules()['name']);
    }

    public function test_store_membership_request_has_personable_rules(): void
    {
        $request = new StoreMembershipRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('personable_type', $rules);
        $this->assertArrayHasKey('personable_id', $rules);
    }

    public function test_role_resource_shape(): void
    {
        $role = Role::factory()->setType('system')->setAllow(['hero'])->setDeny(['secret'])->create();

        $payload = RoleResource::make($role)->toArray(Request::create('/'));

        $this->assertSame($role->id, $payload['id']);
        $this->assertSame('system', $payload['type']);
        $this->assertSame(['hero'], $payload['allow']);
        $this->assertSame(['secret'], $payload['deny']);
        $this->assertArrayHasKey('is_super', $payload);
    }

    public function test_membership_resource_shape(): void
    {
        $person = Person::create(['name' => 'P']);
        $role = Role::factory()->setType('system')->create();
        $membership = $person->assignRole($role);

        $payload = MembershipResource::make($membership->fresh())->toArray(Request::create('/'));

        $this->assertSame($membership->id, $payload['id']);
        $this->assertSame($role->id, $payload['role_id']);
        $this->assertSame($person->getMorphClass(), $payload['personable_type']);
        $this->assertSame($person->id, $payload['personable_id']);
    }
}
