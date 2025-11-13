<?php

namespace Rawnoq\LaravelHMVC\Providers;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\MakeModuleCastCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleChannelCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleClassCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleCommandCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleComponentCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleConfigCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleControllerCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleEnumCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleEventCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleExceptionCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleFactoryCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleInterfaceCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleJobCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleJobMiddlewareCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleListenerCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleMailCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleMiddlewareCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleMigrationCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleModelCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleNotificationCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleObserverCommand;
use Rawnoq\LaravelHMVC\Console\MakeModulePolicyCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleProviderCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleRequestCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleResourceCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleRuleCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleScopeCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleSeederCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleTestCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleTraitCommand;
use Rawnoq\LaravelHMVC\Console\MakeModuleViewCommand;
use Rawnoq\LaravelHMVC\Console\ModuleDisableCommand;
use Rawnoq\LaravelHMVC\Console\ModuleEnableCommand;
use Rawnoq\LaravelHMVC\Console\ModuleListCommand;
use Rawnoq\LaravelHMVC\Console\ModuleMakeCommand;
use Rawnoq\LaravelHMVC\Console\ModuleMigrateCommand;
use Rawnoq\LaravelHMVC\Console\ModuleSeedCommand;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class HMVCServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/hmvc.php', 'hmvc');

        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager($app['config']->get('hmvc', []));
        });

        $this->app->alias('migration.creator', MigrationCreator::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleMakeCommand::class,
                MakeModuleCommand::class,
                ModuleListCommand::class,
                ModuleEnableCommand::class,
                ModuleDisableCommand::class,
                ModuleMigrateCommand::class,
                ModuleSeedCommand::class,
                MakeModuleControllerCommand::class,
                MakeModuleModelCommand::class,
                MakeModuleRequestCommand::class,
                MakeModuleFactoryCommand::class,
                MakeModuleSeederCommand::class,
                MakeModuleMigrationCommand::class,
                MakeModulePolicyCommand::class,
                MakeModuleMiddlewareCommand::class,
                MakeModuleCommandCommand::class,
                MakeModuleEventCommand::class,
                MakeModuleListenerCommand::class,
                MakeModuleObserverCommand::class,
                MakeModuleResourceCommand::class,
                MakeModuleTestCommand::class,
                MakeModuleNotificationCommand::class,
                MakeModuleMailCommand::class,
                MakeModuleJobCommand::class,
                MakeModuleExceptionCommand::class,
                MakeModuleRuleCommand::class,
                MakeModuleCastCommand::class,
                MakeModuleChannelCommand::class,
                MakeModuleComponentCommand::class,
                MakeModuleEnumCommand::class,
                MakeModuleScopeCommand::class,
                MakeModuleViewCommand::class,
                MakeModuleClassCommand::class,
                MakeModuleInterfaceCommand::class,
                MakeModuleTraitCommand::class,
                MakeModuleConfigCommand::class,
                MakeModuleProviderCommand::class,
                MakeModuleJobMiddlewareCommand::class,
            ]);
        }

        $this->registerModuleProviders($this->app->make(ModuleManager::class));
    }

    public function boot(ModuleManager $modules): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/hmvc.php' => config_path('hmvc.php'),
            ], 'hmvc-config');

            $this->publishes([
                __DIR__.'/../../stubs/module' => base_path('stubs/hmvc/module'),
            ], 'hmvc-stubs');
        }

        foreach ($modules->enabled() as $module) {
            $this->bootModule($modules, Arr::get($module, 'name'));
        }
    }

    protected function registerModuleProviders(ModuleManager $modules): void
    {
        foreach ($modules->enabled() as $module) {
            $name = Arr::get($module, 'name');

            foreach ($modules->providerClasses($name) as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    protected function bootModule(ModuleManager $modules, ?string $module): void
    {
        if (! $module) {
            return;
        }

        $this->bootRoutes($modules, $module);
        $this->bootViews($modules, $module);
        $this->bootTranslations($modules, $module);
        $this->bootMigrations($modules, $module);
        $this->bootConfig($modules, $module);
    }

    protected function bootRoutes(ModuleManager $modules, string $module): void
    {
        foreach ($modules->routes() as $route) {
            if (! Arr::get($route, 'enabled', true)) {
                continue;
            }

            $relative = Arr::get($route, 'path');

            if (! $relative) {
                continue;
            }

            $path = $modules->modulePath($module).DIRECTORY_SEPARATOR.$relative;

            if (! File::exists($path)) {
                continue;
            }

            $middleware = Arr::wrap(Arr::get($route, 'middleware', []));
            $group = Route::middleware($middleware);

            if ($prefix = Arr::get($route, 'prefix')) {
                $group = $group->prefix($prefix);
            }

            if ($namespace = Arr::get($route, 'namespace')) {
                $group = $group->namespace($namespace);
            }

            $group->group(function () use ($path) {
                require $path;
            });
        }
    }

    protected function bootViews(ModuleManager $modules, string $module): void
    {
        $directory = $modules->firstExistingDirectory($module, 'views');

        if (! $directory) {
            return;
        }

        $this->loadViewsFrom($directory, Str::kebab($module));
    }

    protected function bootTranslations(ModuleManager $modules, string $module): void
    {
        $directory = $modules->firstExistingDirectory($module, 'lang');

        if (! $directory) {
            return;
        }

        $this->loadTranslationsFrom($directory, Str::kebab($module));
    }

    protected function bootMigrations(ModuleManager $modules, string $module): void
    {
        foreach ($modules->existingDirectories($module, 'migrations') as $directory) {
            $this->loadMigrationsFrom($directory);
        }
    }

    protected function bootConfig(ModuleManager $modules, string $module): void
    {
        $directory = $modules->firstExistingDirectory($module, 'config');

        if (! $directory) {
            return;
        }

        $configFile = $directory.DIRECTORY_SEPARATOR.'config.php';

        if (! File::exists($configFile)) {
            return;
        }

        $this->mergeConfigFrom($configFile, Str::kebab($module));
    }
}
