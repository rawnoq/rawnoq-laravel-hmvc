<?php

namespace Rawnoq\HMVC\Providers;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\DatabaseSeedCommand;
use Rawnoq\HMVC\Console\MakeModuleActionCommand;
use Rawnoq\HMVC\Console\MakeModuleCastCommand;
use Rawnoq\HMVC\Console\MakeModuleChannelCommand;
use Rawnoq\HMVC\Console\MakeModuleClassCommand;
use Rawnoq\HMVC\Console\MakeModuleCommand;
use Rawnoq\HMVC\Console\MakeModuleDtoCommand;
use Rawnoq\HMVC\Console\MakeModuleCommandCommand;
use Rawnoq\HMVC\Console\MakeModuleComponentCommand;
use Rawnoq\HMVC\Console\MakeModuleConfigCommand;
use Rawnoq\HMVC\Console\MakeModuleControllerCommand;
use Rawnoq\HMVC\Console\MakeModuleEnumCommand;
use Rawnoq\HMVC\Console\MakeModuleEventCommand;
use Rawnoq\HMVC\Console\MakeModuleExceptionCommand;
use Rawnoq\HMVC\Console\MakeModuleFactoryCommand;
use Rawnoq\HMVC\Console\MakeModuleInterfaceCommand;
use Rawnoq\HMVC\Console\MakeModuleJobCommand;
use Rawnoq\HMVC\Console\MakeModuleJobMiddlewareCommand;
use Rawnoq\HMVC\Console\MakeModuleListenerCommand;
use Rawnoq\HMVC\Console\MakeModuleMailCommand;
use Rawnoq\HMVC\Console\MakeModuleMiddlewareCommand;
use Rawnoq\HMVC\Console\MakeModuleMigrationCommand;
use Rawnoq\HMVC\Console\MakeModuleModelCommand;
use Rawnoq\HMVC\Console\MakeModuleNotificationCommand;
use Rawnoq\HMVC\Console\MakeModuleObserverCommand;
use Rawnoq\HMVC\Console\MakeModulePolicyCommand;
use Rawnoq\HMVC\Console\MakeModuleProviderCommand;
use Rawnoq\HMVC\Console\MakeModuleRepositoryCommand;
use Rawnoq\HMVC\Console\MakeModuleRequestCommand;
use Rawnoq\HMVC\Console\MakeModuleResourceCommand;
use Rawnoq\HMVC\Console\MakeModuleRuleCommand;
use Rawnoq\HMVC\Console\MakeModuleScopeCommand;
use Rawnoq\HMVC\Console\MakeModuleSeederCommand;
use Rawnoq\HMVC\Console\MakeModuleServiceCommand;
use Rawnoq\HMVC\Console\MakeModuleStrategyCommand;
use Rawnoq\HMVC\Console\MakeModuleStrategyContextCommand;
use Rawnoq\HMVC\Console\MakeModuleStrategyInterfaceCommand;
use Rawnoq\HMVC\Console\MakeModuleTestCommand;
use Rawnoq\HMVC\Console\MakeModuleTraitCommand;
use Rawnoq\HMVC\Console\MakeModuleViewCommand;
use Rawnoq\HMVC\Console\ModuleDisableCommand;
use Rawnoq\HMVC\Console\ModuleEnableCommand;
use Rawnoq\HMVC\Console\ModuleListCommand;
use Rawnoq\HMVC\Console\ModuleMakeCommand;

use Rawnoq\HMVC\Support\ModuleManager;

class HMVCServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/hmvc.php', 'hmvc');

        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager($app['config']->get('hmvc', []));
        });

        $this->registerModuleAutoloading();

        $this->app->alias('migration.creator', MigrationCreator::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseSeedCommand::class,
                ModuleMakeCommand::class,
                MakeModuleCommand::class,
                ModuleListCommand::class,
                ModuleEnableCommand::class,
                ModuleDisableCommand::class,

                MakeModuleActionCommand::class,
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
                MakeModuleDtoCommand::class,
                MakeModuleStrategyInterfaceCommand::class,
                MakeModuleStrategyCommand::class,
                MakeModuleStrategyContextCommand::class,
                MakeModuleConfigCommand::class,
                MakeModuleProviderCommand::class,
                MakeModuleJobMiddlewareCommand::class,
                MakeModuleServiceCommand::class,
                MakeModuleRepositoryCommand::class,
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

    protected function registerModuleAutoloading(): void
    {
        $modules = $this->app->make(ModuleManager::class);
        $namespace = $modules->namespace();
        $modulesPath = $modules->modulesPath();

        // Normalize path for cross-platform compatibility (Linux/Windows)
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $modulesPath);

        // Ensure the path ends with directory separator
        if (! str_ends_with($normalizedPath, DIRECTORY_SEPARATOR)) {
            $normalizedPath .= DIRECTORY_SEPARATOR;
        }

        // Register PSR-4 autoloading for modules
        $loader = require base_path('vendor/autoload.php');
        $loader->setPsr4($namespace.'\\', $normalizedPath);
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

        foreach (File::allFiles($directory) as $file) {
            $key = $file->getFilenameWithoutExtension();
            $path = $file->getPathname();
            
            // Manually merge or set to ensure it works
            $config = config($key, []);
            $newConfig = require $path;
            
            if (is_array($config) && is_array($newConfig)) {
                config()->set($key, array_replace_recursive($config, $newConfig));
            } else {
                config()->set($key, $newConfig);
            }
        }
    }

}
