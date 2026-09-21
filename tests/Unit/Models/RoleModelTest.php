<?php

namespace JobMetric\Rolix\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Models\RoleRule;
use JobMetric\Rolix\Services\Role as RoleService;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Unit tests for Role model.
 */
class RoleModelTest extends TestCase
{
    /**
     * getTable returns config table name.
     */
    public function test_getTable_returns_config_value(): void
    {
        $this->assertSame('roles', (new Role)->getTable());
    }

    /**
     * fillable contains is_super.
     */
    public function test_fillable_contains_is_super(): void
    {
        $this->assertContains('is_super', (new Role)->getFillable());
    }

    /**
     * parent, children, rules, paths relations.
     */
    public function test_relations_are_defined(): void
    {
        $role = new Role;

        $this->assertInstanceOf(BelongsTo::class, $role->parent());
        $this->assertInstanceOf(HasMany::class, $role->children());
        $this->assertInstanceOf(HasMany::class, $role->rules());
        $this->assertInstanceOf(HasMany::class, $role->paths());
        $this->assertSame(RoleRule::class, $role->rules()->getRelated()::class);
    }

    /**
     * ancestors returns parent roles from path table.
     */
    public function test_ancestors_returns_parent_chain(): void
    {
        $service = app(RoleService::class);

        $grand = Role::factory()->setName('Grand')->create();
        $service->rebuildPaths($grand);

        $parent = Role::factory()->setName('Parent')->setParent($grand->id)->create();
        $service->rebuildPaths($parent);

        $child = Role::factory()->setName('Child')->setParent($parent->id)->create();
        $service->rebuildPaths($child);

        $ancestors = $child->ancestors();

        $this->assertCount(2, $ancestors);
        $this->assertSame($parent->id, $ancestors[0]->id);
        $this->assertSame($grand->id, $ancestors[1]->id);
        $this->assertTrue($child->paths()->where('level', 0)->where('path_id', $child->id)->exists());
        $this->assertSame(3, RolePath::query()->where('role_id', $child->id)->count());
    }

    /**
     * isSuper helper reflects is_super flag.
     */
    public function test_isSuper_helper(): void
    {
        $role = Role::factory()->super()->create();

        $this->assertTrue($role->isSuper());
    }
}
