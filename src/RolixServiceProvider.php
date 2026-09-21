<?php

namespace JobMetric\Rolix;

use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
use JobMetric\Rolix\Events\RegisterPathPermissionEvent;
use JobMetric\Rolix\Facades\Permission;
use JobMetric\Rolix\Facades\RoleTypeRegistry as FacadeRoleTypeRegistry;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Support\RoleTypeRegistry;

class RolixServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('rolix')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->registerClass('rolix.permission', PermissionManager::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('RoleTypeRegistry', RoleTypeRegistry::class, RegisterClassTypeEnum::SINGLETON());
    }

    /**
     * After register package
     *
     * @return void
     */
    public function afterRegisterPackage(): void
    {
        // Register role types from config
        foreach (config('rolix.types', []) as $type => $options) {
            FacadeRoleTypeRegistry::register($type, is_array($options) ? $options : []);
        }
    }

    /**
     * after boot package
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
        app()->booted(function () {
            $global_permission_path = config_path('permissions');

            $paths = glob($global_permission_path . DIRECTORY_SEPARATOR . '*.php');

            foreach ($paths as $path) {
                $context = basename($path, '.php');

                Permission::addPermissionFile($context, $path);
            }

            $event = new RegisterPathPermissionEvent;
            event($event);

            if (!empty($event->getPaths())) {
                foreach ($event->getPaths() as $context => $path) {
                    Permission::addPermissionFile($context, $path);
                }
            }
        });
    }
}
