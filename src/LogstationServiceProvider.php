<?php

namespace PlusinfoLab\Logstation;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use PlusinfoLab\Logstation\Commands\ClearCommand;
use PlusinfoLab\Logstation\Commands\InstallCommand;
use PlusinfoLab\Logstation\Commands\PruneCommand;
use PlusinfoLab\Logstation\Commands\PublishCommand;
use PlusinfoLab\Logstation\Storage\DatabaseStorage;
use PlusinfoLab\Logstation\Storage\FileStorage;
use PlusinfoLab\Logstation\Storage\StorageDriver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogstationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('logstation')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_logstation_entries_table',
                'create_logstation_tags_table',
                'create_logstation_snippets_table',
            ])
            ->hasCommands([
                InstallCommand::class,
                PruneCommand::class,
                ClearCommand::class,
                PublishCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Bind storage driver
        $this->app->singleton(StorageDriver::class, function ($app) {
            $driver = config('logstation.driver', 'database');

            return match ($driver) {
                'file' => new FileStorage(),
                default => new DatabaseStorage(),
            };
        });

        // Bind main service
        $this->app->singleton('logstation', function ($app) {
            return new Logstation($app->make(StorageDriver::class));
        });


        // Register the facade alias
        $this->app->alias(Logstation::class, 'logstation');
    }

    public function packageBooted(): void
    {
        // Determine if LogStation is enabled (default to enabled in non-production)
        $enabled = config('logstation.enabled');
        if ($enabled === null) {
            $enabled = !app()->environment('production');
        }

        // Register routes
        if ($enabled) {
            $this->registerRoutes();
        }

        // Register gate
        $this->registerGate();

        // Register watchers
        if ($enabled) {
            $this->registerWatchers();
        }

        // Publish assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/logstation'),
            ], 'logstation-assets');

            // Publish config for customization
            $this->publishes([
                __DIR__ . '/../config/logstation.php' => config_path('logstation.php'),
            ], 'logstation-config');
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    /**
     * Register the LogStation gate.
     */
    protected function registerGate(): void
    {
        Gate::define('viewLogstation', function ($user = null) {
            $callback = config('logstation.authorization.callback');

            if (is_callable($callback)) {
                return $callback(request());
            }

            return app()->environment('local');
        });
    }

    /**
     * Register the LogStation watchers.
     */
    protected function registerWatchers(): void
    {
        $watchers = config('logstation.watchers', []);

        foreach ($watchers as $watcherClass => $config) {
            if (is_array($config) && ($config['enabled'] ?? true)) {
                $watcher = new $watcherClass($config);
                $watcher->register();
            }
        }
    }
}
