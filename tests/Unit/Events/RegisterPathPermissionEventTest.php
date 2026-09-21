<?php

namespace JobMetric\Rolix\Tests\Unit\Events;

use InvalidArgumentException;
use JobMetric\Rolix\Events\RegisterPathPermissionEvent;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for RegisterPathPermissionEvent.
 */
class RegisterPathPermissionEventTest extends TestCase
{
    /**
     * addPath without model registers system-wide path.
     */
    public function test_addPath_without_model_registers_system_path(): void
    {
        $event = new RegisterPathPermissionEvent;
        $event->addPath('hero', '/tmp/hero.php');

        $this->assertSame([
            ['hero', '/tmp/hero.php', null],
        ], $event->getPaths());
    }

    /**
     * addPath with model registers scoped path.
     */
    public function test_addPath_with_model_registers_scoped_path(): void
    {
        $event = new RegisterPathPermissionEvent;
        $event->addPath('hero', '/tmp/hero-tenant.php', Tenant::class);

        $this->assertSame([
            ['hero', '/tmp/hero-tenant.php', Tenant::class],
        ], $event->getPaths());
    }

    /**
     * Same context with different models is allowed.
     */
    public function test_same_context_with_different_models_is_allowed(): void
    {
        $event = new RegisterPathPermissionEvent;
        $event->addPath('hero', '/tmp/hero.php');
        $event->addPath('hero', '/tmp/hero-tenant.php', Tenant::class);

        $this->assertCount(2, $event->getPaths());
    }

    /**
     * Duplicate context and model throws.
     */
    public function test_duplicate_context_and_model_throws(): void
    {
        $event = new RegisterPathPermissionEvent;
        $event->addPath('hero', '/tmp/hero.php');

        $this->expectException(InvalidArgumentException::class);

        $event->addPath('hero', '/tmp/hero-other.php');
    }
}
