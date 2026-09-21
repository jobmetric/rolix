<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Rolix\Facades\Role as RoleFacade;
use JobMetric\Rolix\Facades\RoleTypeRegistry;
use JobMetric\Rolix\Models\Role;
use JobMetric\Rolix\Models\RolePath;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Services\Role as RoleService;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for role_paths hierarchy listing and rebuild.
 */
class RolePathTest extends TestCase
{
    private string $permissionFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permissionFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rolix_path_perm_' . uniqid('', true) . '.php';
        file_put_contents($this->permissionFile, "<?php\nreturn ['hero' => 'permissions/hero'];\n");
        $this->app->make(PermissionManager::class)->addPermissionFile('hero', $this->permissionFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->permissionFile);
        parent::tearDown();
    }

    public function test_store_response_includes_paths_when_loaded(): void
    {
        $response = RoleFacade::store([
            'name'  => 'Root',
            'allow' => ['hero'],
        ], ['paths']);

        $this->assertTrue($response->ok);
        $payload = $response->data->resolve();
        $this->assertArrayHasKey('paths', $payload);
        $this->assertNotEmpty($payload['paths']);
        $this->assertSame(0, $payload['paths'][0]['level']);
    }

    public function test_three_level_tree_paths_and_flat_tree(): void
    {
        $a = RoleFacade::store(['name' => 'A', 'allow' => ['hero']])->data;
        $b = RoleFacade::store(['name' => 'B', 'parent_id' => $a->id, 'allow' => ['hero']])->data;
        $c = RoleFacade::store(['name' => 'C', 'parent_id' => $b->id, 'allow' => ['hero']])->data;

        $this->assertSame(3, RolePath::query()->where('role_id', $c->id)->count());
        $this->assertTrue(RolePath::query()->where('role_id', $c->id)->where('path_id', $a->id)->where('level', 2)->exists());

        $flat = app(RoleService::class)->flatTree('system');
        $labels = $flat->pluck('path_label', 'name');
        $this->assertSame('A', $labels['A']);
        $this->assertSame('A > B', $labels['B']);
        $this->assertSame('A > B > C', $labels['C']);
        $this->assertSame(2, (int) $flat->firstWhere('name', 'C')->depth);

        $nested = app(RoleService::class)->nestedTree('system');
        $this->assertSame('A', $nested[0]['name']);
        $this->assertSame('B', $nested[0]['children'][0]['name']);
        $this->assertSame('C', $nested[0]['children'][0]['children'][0]['name']);

        $subtree = Role::query()->inSubtree($b->id)->pluck('name')->sort()->values()->all();
        $this->assertSame(['B', 'C'], $subtree);

        $this->assertCount(2, Role::query()->find($a->id)->descendants());
    }

    public function test_destroy_parent_rebuilds_child_paths(): void
    {
        $parent = RoleFacade::store(['name' => 'P', 'allow' => ['hero']])->data;
        $child = RoleFacade::store(['name' => 'C', 'parent_id' => $parent->id, 'allow' => ['hero']])->data;

        RoleFacade::destroy($parent->id);

        $child = Role::query()->find($child->id);
        $this->assertNull($child->parent_id);
        $this->assertSame(1, RolePath::query()->where('role_id', $child->id)->count());
        $this->assertTrue(RolePath::query()->where('role_id', $child->id)->where('level', 0)->exists());
    }

    public function test_hierarchical_false_rejects_parent(): void
    {
        RoleTypeRegistry::register('flat', ['hierarchical' => false]);

        $parent = Role::factory()->setType('flat')->setName('P')->create();
        app(RoleService::class)->rebuildPaths($parent);

        $this->expectException(ValidationException::class);

        RoleFacade::store([
            'type'      => 'flat',
            'name'      => 'Child',
            'parent_id' => $parent->id,
            'allow'     => [],
        ]);
    }
}
