<?php

namespace JobMetric\Rolix\Tests\Unit\Events;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\EventRegistry;
use JobMetric\Rolix\Events\Membership\MembershipDeleteEvent;
use JobMetric\Rolix\Events\Membership\MembershipForceDeleteEvent;
use JobMetric\Rolix\Events\Membership\MembershipRestoreEvent;
use JobMetric\Rolix\Events\Membership\MembershipStoreEvent;
use JobMetric\Rolix\Events\Membership\MembershipUpdateEvent;
use JobMetric\Rolix\Events\RegisterPathPermissionEvent;
use JobMetric\Rolix\Events\Resources\ActorResourceEvent;
use JobMetric\Rolix\Events\Resources\ContextResourceEvent;
use JobMetric\Rolix\Events\Resources\MemberableResourceEvent;
use JobMetric\Rolix\Events\Resources\PersonableResourceEvent;
use JobMetric\Rolix\Events\Resources\SubjectResourceEvent;
use JobMetric\Rolix\Events\Resources\TargetResourceEvent;
use JobMetric\Rolix\Events\Role\RoleDeleteEvent;
use JobMetric\Rolix\Events\Role\RoleStoreEvent;
use JobMetric\Rolix\Events\Role\RoleUpdateEvent;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Tests\TestCase;

/**
 * DomainEvent contract and EventRegistry coverage for Rolix events.
 */
class DomainEventTest extends TestCase
{
    /**
     * @return array<int, class-string<DomainEvent>>
     */
    public static function domainEventClasses(): array
    {
        return [
            [RoleStoreEvent::class],
            [RoleUpdateEvent::class],
            [RoleDeleteEvent::class],
            [MembershipStoreEvent::class],
            [MembershipUpdateEvent::class],
            [MembershipDeleteEvent::class],
            [MembershipRestoreEvent::class],
            [MembershipForceDeleteEvent::class],
            [RegisterPathPermissionEvent::class],
            [PersonableResourceEvent::class],
            [MemberableResourceEvent::class],
            [ActorResourceEvent::class],
            [TargetResourceEvent::class],
            [ContextResourceEvent::class],
            [SubjectResourceEvent::class],
        ];
    }

    /**
     * @dataProvider domainEventClasses
     */
    public function test_event_implements_domain_event_contract(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, DomainEvent::class));
        $this->assertNotSame('', $class::key());
        $definition = $class::definition();
        $this->assertSame($class::key(), $definition->key);
        $this->assertNotSame('', $definition->group);
        $this->assertNotSame('', $definition->title);
    }

    public function test_events_are_registered_in_event_registry(): void
    {
        /** @var EventRegistry $registry */
        $registry = $this->app->make('EventRegistry');

        $this->assertTrue($registry->has(RoleStoreEvent::key()));
        $this->assertTrue($registry->has(MembershipStoreEvent::key()));
        $this->assertTrue($registry->has(RegisterPathPermissionEvent::key()));
        $this->assertTrue($registry->has(PersonableResourceEvent::key()));
    }

    public function test_role_store_event_can_be_constructed(): void
    {
        $role = Role::factory()->create();
        $event = new RoleStoreEvent($role, ['name' => $role->name]);

        $this->assertSame($role->id, $event->role->id);
        $this->assertSame('role.stored', RoleStoreEvent::key());
    }
}
