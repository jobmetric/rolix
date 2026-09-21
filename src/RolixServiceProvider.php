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
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry as FacadeRuleEvaluatorRegistry;
use JobMetric\Rolix\RuleEvaluators\CustomExpressionEvaluator;
use JobMetric\Rolix\RuleEvaluators\EnvEvaluator;
use JobMetric\Rolix\RuleEvaluators\IpRangeEvaluator;
use JobMetric\Rolix\RuleEvaluators\LocationEvaluator;
use JobMetric\Rolix\RuleEvaluators\QuotaEvaluator;
use JobMetric\Rolix\RuleEvaluators\RoleCountEvaluator;
use JobMetric\Rolix\RuleEvaluators\TimeEvaluator;
use JobMetric\Rolix\RuleEvaluators\UserStatusEvaluator;
use JobMetric\Rolix\RuleEvaluators\WeekdayEvaluator;
use JobMetric\Rolix\Services\PermissionManager;
use JobMetric\Rolix\Services\Role;
use JobMetric\Rolix\Support\RoleTypeRegistry;
use JobMetric\Rolix\Support\RuleEvaluatorRegistry;

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
            ->registerClass('role', Role::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('RoleTypeRegistry', RoleTypeRegistry::class, RegisterClassTypeEnum::SINGLETON())
            ->registerClass('RuleEvaluatorRegistry', RuleEvaluatorRegistry::class, RegisterClassTypeEnum::SINGLETON());
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

        foreach (
            [
                TimeEvaluator::class,
                WeekdayEvaluator::class,
                UserStatusEvaluator::class,
                IpRangeEvaluator::class,
                LocationEvaluator::class,
                EnvEvaluator::class,
                RoleCountEvaluator::class,
                QuotaEvaluator::class,
                CustomExpressionEvaluator::class,
            ] as $evaluator) {
            FacadeRuleEvaluatorRegistry::register($evaluator);
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

            foreach ($event->getPaths() as [$context, $path, $model]) {
                Permission::addPermissionFile($context, $path, $model);
            }
        });
    }
}
