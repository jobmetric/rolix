<?php

namespace JobMetric\Rolix\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use JobMetric\Rolix\RolixServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Base test case for Rolix package (self-contained like Extension).
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            RolixServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app->booting(function () use ($app): void {
            $migrationsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
            if (is_dir($migrationsPath)) {
                loadMigrationPath($migrationsPath);
            }
        });
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createStubTables();
    }

    /**
     * Create stub tables for personable and memberable test models.
     *
     * @return void
     */
    protected function createStubTables(): void
    {
        if (! Schema::hasTable('rolix_test_persons')) {
            Schema::create('rolix_test_persons', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rolix_test_tenants')) {
            Schema::create('rolix_test_tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }
}
