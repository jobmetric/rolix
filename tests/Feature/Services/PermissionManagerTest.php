<?php

namespace JobMetric\Rolix\Tests\Feature\Services;

use InvalidArgumentException;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Tests\Stubs\Tenant;
use JobMetric\Rolix\Tests\TestCase;

/**
 * Feature tests for PermissionManager.
 */
class PermissionManagerTest extends TestCase
{
    private string $systemFile;

    private string $tenantFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rolix_perm_system_' . uniqid('', true) . '.php';
        $this->tenantFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rolix_perm_tenant_' . uniqid('', true) . '.php';

        file_put_contents($this->systemFile, "<?php\nreturn ['hero' => 'permissions/hero.hero'];\n");
        file_put_contents($this->tenantFile, "<?php\nreturn ['tenant.manage' => 'permissions/tenant.manage'];\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->systemFile);
        @unlink($this->tenantFile);

        parent::tearDown();
    }

    /**
     * addPermissionFile without model stores under system scope.
     */
    public function test_addPermissionFile_without_model_is_system_scoped(): void
    {
        $manager = new PermissionManager;
        $manager->addPermissionFile('hero', $this->systemFile);

        $this->assertTrue($manager->hasPermission('hero', 'hero'));
        $this->assertFalse($manager->hasPermission('hero', 'hero', Tenant::class));
        $this->assertSame(['hero'], $manager->getContextPermission());
    }

    /**
     * addPermissionFile with model stores under that model scope.
     */
    public function test_addPermissionFile_with_model_is_model_scoped(): void
    {
        $manager = new PermissionManager;
        $manager->addPermissionFile('hero', $this->tenantFile, Tenant::class);

        $this->assertTrue($manager->hasPermission('hero', 'tenant.manage', Tenant::class));
        $this->assertFalse($manager->hasPermission('hero', 'tenant.manage'));
        $this->assertSame(['hero'], $manager->getContextPermission(Tenant::class));
    }

    /**
     * getPermissions assoc view returns permission => lang map.
     */
    public function test_getPermissions_assoc_for_system(): void
    {
        $manager = new PermissionManager;
        $manager->addPermissionFile('hero', $this->systemFile);

        $this->assertSame([
            'hero' => 'permissions/hero.hero',
        ], $manager->getPermissions('hero'));
    }

    /**
     * Missing permission file throws.
     */
    public function test_addPermissionFile_missing_file_throws(): void
    {
        $manager = new PermissionManager;

        $this->expectException(InvalidArgumentException::class);

        $manager->addPermissionFile('hero', '/tmp/does-not-exist-' . uniqid() . '.php');
    }
}
