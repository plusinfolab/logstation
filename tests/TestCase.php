<?php

namespace PlusinfoLab\Logstation\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use PlusinfoLab\Logstation\LogstationServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'PlusinfoLab\\Logstation\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LogstationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure LogStation
        config()->set('logstation.enabled', true);
        config()->set('logstation.driver', 'database');
        config()->set('logstation.database.connection', 'testing');

        // Run migrations
        $migrationsPath = __DIR__.'/../database/migrations';
        foreach (glob($migrationsPath.'/*.php*') as $migrationFile) {
            $migration = include $migrationFile;
            $migration->up();
        }
    }
}
