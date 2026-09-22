<?php

namespace JobMetric\Rolix;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use JobMetric\EventSystem\Support\EventRegistry;
use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
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
use JobMetric\Rolix\Facades\Permission;
use JobMetric\Rolix\Facades\RoleTypeRegistry as FacadeRoleTypeRegistry;
use JobMetric\Rolix\Facades\RuleEvaluatorRegistry as FacadeRuleEvaluatorRegistry;
use JobMetric\Rolix\Http\Middleware\EnsurePermission;
use JobMetric\Rolix\RuleEvaluators\EnvEvaluator;
use JobMetric\Rolix\RuleEvaluators\IpRangeEvaluator;
use JobMetric\Rolix\RuleEvaluators\TimeEvaluator;
use JobMetric\Rolix\RuleEvaluators\WeekdayEvaluator;
use JobMetric\Rolix\Services\Membership;
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
            ->registerClass('membership', Membership::class, RegisterClassTypeEnum::SINGLETON())
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
                IpRangeEvaluator::class,
                EnvEvaluator::class,
            ] as $evaluator) {
            FacadeRuleEvaluatorRegistry::register($evaluator);
        }
    }

    /**
     * after boot package
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function afterBootPackage(): void
    {
        // Register events in the EventRegistry if it is bound in the application container
        if ($this->app->bound('EventRegistry')) {
            /** @var EventRegistry $registry */
            $registry = $this->app->make('EventRegistry');

            foreach (
                [
                    RoleStoreEvent::class,
                    RoleUpdateEvent::class,
                    RoleDeleteEvent::class,
                    MembershipStoreEvent::class,
                    MembershipUpdateEvent::class,
                    MembershipDeleteEvent::class,
                    MembershipRestoreEvent::class,
                    MembershipForceDeleteEvent::class,
                    RegisterPathPermissionEvent::class,
                    PersonableResourceEvent::class,
                    MemberableResourceEvent::class,
                    ActorResourceEvent::class,
                    TargetResourceEvent::class,
                    ContextResourceEvent::class,
                    SubjectResourceEvent::class,
                ] as $eventClass) {
                $registry->register($eventClass);
            }
        }

        // Register middleware alias for permission checking
        $this->app->make('router')->aliasMiddleware('rolix.permission', EnsurePermission::class);

        // Register a global before callback for all authorization checks
        Gate::before(function ($user, string $ability, array $arguments = []) {
            if ($user === null || ! is_object($user) || ! method_exists($user, 'hasPermission')) {
                return null;
            }

            $context = $arguments[0] ?? null;
            $collection = $arguments[1] ?? null;

            if ($context !== null && ! $context instanceof Model) {
                $context = null;
            }

            if (! is_string($collection)) {
                $collection = null;
            }

            return $user->hasPermission($ability, $context, $collection) ? true : null;
        });

        // Register a custom Blade directive for permission checking
        Blade::if('rolixCan', function (string $permission, $context = null, $collection = null) {
            $user = auth()->user();

            if ($user === null || ! method_exists($user, 'hasPermission')) {
                return false;
            }

            return (bool) $user->hasPermission($permission, $context instanceof Model ? $context : null, is_string($collection) ? $collection : null);
        });

        // Load global permission files after the application has booted
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
