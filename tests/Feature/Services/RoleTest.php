<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Rolix\Exceptions\RoleIsSuperException;
use JobMetric\Rolix\Facades\Role as RoleFacade;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Models\RoleRule;
use JobMetric\Rolix\RuleEvaluators\TimeEvaluator;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Services\Role as RoleService;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for Role CRUD service.
 */
class RoleTest extends TestCase
{
    private string $permissionFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rolix_role_perm_' . uniqid('', true) . '.php';
        file_put_contents($this->permissionFile, "<?php\nreturn [\n    'hero' => 'permissions/hero',\n    'hero.content' => 'permissions/hero.content',\n];\n");

        $this->app->make(PermissionManager::class)->addPermissionFile('hero', $this->permissionFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->permissionFile);

        parent::tearDown();
    }

    /**
     * store creates role with allow/deny and self path.
     */
    public function test_store_creates_role_with_permissions_and_path(): void
    {
        $response = RoleFacade::store([
            'name' => 'Editor',
            'allow' => ['hero', 'hero.content'],
            'deny' => [],
        ], ['rules', 'paths']);

        $this->assertTrue($response->ok);
        $this->assertSame(201, $response->status);

        $role = Role::query()->where('name', 'Editor')->first();
        $this->assertNotNull($role);
        $this->assertSame('system', $role->type);
        $this->assertSame(['hero', 'hero.content'], $role->allow);
        $this->assertSame(1, RolePath::query()->where('role_id', $role->id)->where('level', 0)->count());
    }

    /**
     * store with parent rebuilds hierarchical paths.
     */
    public function test_store_with_parent_rebuilds_hierarchy_paths(): void
    {
        $parent = Role::factory()->setName('Parent')->create();
        app(RoleService::class)->rebuildPaths($parent);

        $response = RoleFacade::store([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'allow' => ['hero'],
        ]);

        $this->assertTrue($response->ok);

        $child = Role::query()->where('name', 'Child')->first();
        $this->assertSame(2, RolePath::query()->where('role_id', $child->id)->count());
        $this->assertTrue(
            RolePath::query()
                ->where('role_id', $child->id)
                ->where('path_id', $parent->id)
                ->where('level', 1)
                ->exists()
        );
    }

    /**
     * store syncs role rules.
     */
    public function test_store_syncs_role_rules(): void
    {
        $response = RoleFacade::store([
            'name' => 'Timed',
            'allow' => ['hero'],
            'rules' => [
                [
                    'driver' => TimeEvaluator::class,
                    'payload' => [
                        'from' => '08:00',
                        'to' => '18:00',
                        'timezone' => 'UTC',
                    ],
                ],
            ],
        ], ['rules']);

        $this->assertTrue($response->ok);

        $role = Role::query()->where('name', 'Timed')->first();
        $this->assertSame(1, RoleRule::query()->where('role_id', $role->id)->count());
        $this->assertSame(TimeEvaluator::class, $role->rules()->first()->driver);
    }

    /**
     * store accepts short rule driver names from the registry.
     */
    public function test_store_accepts_short_rule_driver_name(): void
    {
        $response = RoleFacade::store([
            'name' => 'Env Timed',
            'allow' => ['hero'],
            'rules' => [
                [
                    'driver' => 'env',
                    'payload' => ['environments' => 'testing'],
                ],
            ],
        ], ['rules']);

        $this->assertTrue($response->ok);

        $role = Role::query()->where('name', 'Env Timed')->first();
        $this->assertSame('env', $role->rules()->first()->driver);
    }

    /**
     * super role clears allow/deny and cannot be destroyed.
     */
    public function test_super_role_clears_permissions_and_cannot_be_destroyed(): void
    {
        $response = RoleFacade::store([
            'name' => 'Super Admin',
            'is_super' => true,
            'allow' => ['hero'],
            'deny' => ['hero.content'],
        ]);

        $this->assertTrue($response->ok);

        $role = Role::query()->where('name', 'Super Admin')->first();
        $this->assertTrue($role->is_super);
        $this->assertSame([], $role->allow);
        $this->assertSame([], $role->deny);

        $this->expectException(RoleIsSuperException::class);

        RoleFacade::destroy($role->id);
    }

    /**
     * update cannot demote a super role.
     */
    public function test_update_cannot_demote_super_role(): void
    {
        $role = Role::factory()->super()->setName('Super')->create();
        app(RoleService::class)->rebuildPaths($role);

        $this->expectException(ValidationException::class);

        RoleFacade::update($role->id, [
            'is_super' => false,
            'name' => 'Super',
        ]);
    }

    /**
     * only one is_super role is allowed per type.
     */
    public function test_only_one_is_super_per_type(): void
    {
        RoleFacade::store([
            'name' => 'Super Admin',
            'is_super' => true,
            'allow' => [],
        ]);

        $this->expectException(ValidationException::class);

        RoleFacade::store([
            'name' => 'Another Super',
            'is_super' => true,
            'allow' => [],
        ]);
    }

    /**
     * setting is_default clears previous default of same type.
     */
    public function test_is_default_clears_previous_default(): void
    {
        $first = Role::factory()->setName('First')->setIsDefault(true)->create();
        app(RoleService::class)->rebuildPaths($first);

        RoleFacade::store([
            'name' => 'Second',
            'is_default' => true,
            'allow' => [],
        ]);

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue(Role::query()->where('name', 'Second')->first()->is_default);
    }

    /**
     * unknown type fails validation.
     */
    public function test_store_with_unknown_type_fails(): void
    {
        $this->expectException(ValidationException::class);

        RoleFacade::store([
            'type' => 'unknown-type',
            'name' => 'Broken',
        ]);
    }
}
