<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Support\ModuleManager;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make {name : The module name}'
        .' {--force : Overwrite the module if it already exists}'
        .' {--plain : Generate an empty module scaffold}'
        .' {--api : Include API routing scaffold if available}';

    protected $description = 'Create a new HMVC module scaffold';

    public function __construct(protected ModuleManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $modulePath = $this->manager->modulePath($name);

        if ($this->manager->moduleExists($name)) {
            if (! $this->option('force')) {
                $this->error("Module [$name] already exists.");

                return self::FAILURE;
            }

            File::deleteDirectory($modulePath);
        }

        File::ensureDirectoryExists($modulePath);

        $this->createBaseDirectories($name);

        if (! $this->option('plain')) {
            $this->createController($name);
            $this->createRouteFiles($name);
            $this->createSeeder($name);
            $this->createProvider($name);
            $this->createConfig($name);
        }

        $this->manager->enable($name);

        $this->info("Module [$name] created successfully.");

        return self::SUCCESS;
    }

    protected function createBaseDirectories(string $module): void
    {
        // Create only essential directories
        $essentialDirectories = [
            'controllers',
            'routes',
            'providers',
        ];

        foreach ($essentialDirectories as $type) {
            $paths = $this->manager->directoriesFor($type);
            $preferred = Arr::first($paths);

            if ($preferred) {
                $this->manager->ensureModuleDirectory($module, $preferred);
            }
        }
    }

    protected function createController(string $module): void
    {
        $controllerDirectories = $this->manager->directoriesFor('controllers');

        if (empty($controllerDirectories)) {
            return;
        }

        $controller = $module.'Controller';
        $relative = Arr::first($controllerDirectories);
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $path = $this->manager->modulePath($module).DIRECTORY_SEPARATOR.$normalized.DIRECTORY_SEPARATOR.$controller.'.php';

        $this->writeStub('controller.stub', $path, $this->replacements($module, $controller));
    }

    protected function createRouteFiles(string $module): void
    {
        $routes = $this->manager->routes();

        if ($this->option('plain')) {
            return;
        }

        $routes = $routes->filter(function (array $route) {
            if (! Arr::get($route, 'make', true)) {
                if ($this->option('api') && Arr::get($route, 'name') === 'api') {
                    return true;
                }

                return false;
            }

            return true;
        });

        foreach ($routes as $route) {
            $relative = Arr::get($route, 'path');

            if (! $relative) {
                continue;
            }

            $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $path = $this->manager->modulePath($module).DIRECTORY_SEPARATOR.$normalized;
            File::ensureDirectoryExists(dirname($path));

            $stub = Arr::get($route, 'stub');

            if ($stub) {
                try {
                    $this->writeStub($stub, $path, $this->replacements($module));

                    continue;
                } catch (FileNotFoundException $exception) {
                    $this->warn($exception->getMessage());
                }
            }

            File::put($path, '<?php'.PHP_EOL);
        }
    }

    protected function createSeeder(string $module): void
    {
        $seederDirectories = $this->manager->directoriesFor('seeders');

        if (empty($seederDirectories)) {
            return;
        }

        $relative = Arr::first($seederDirectories);
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $path = $this->manager->modulePath($module).DIRECTORY_SEPARATOR.$normalized.DIRECTORY_SEPARATOR.'DatabaseSeeder.php';

        File::ensureDirectoryExists(dirname($path));

        try {
            $this->writeStub('database/seeders/DatabaseSeeder.stub', $path, $this->replacements($module));
        } catch (FileNotFoundException $exception) {
            $this->warn($exception->getMessage());
        }
    }

    protected function createProvider(string $module): void
    {
        $providerDirectories = $this->manager->directoriesFor('providers');

        if (empty($providerDirectories)) {
            return;
        }

        $relative = Arr::first($providerDirectories);
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $path = $this->manager->modulePath($module).DIRECTORY_SEPARATOR.$normalized.DIRECTORY_SEPARATOR.'ServiceProvider.php';

        File::ensureDirectoryExists(dirname($path));

        try {
            $this->writeStub('providers/ModuleServiceProvider.stub', $path, $this->replacements($module));
        } catch (FileNotFoundException $exception) {
            $this->warn($exception->getMessage());
        }
    }

    protected function createConfig(string $module): void
    {
        $configDirectories = $this->manager->directoriesFor('config');

        if (empty($configDirectories)) {
            return;
        }

        $relative = Arr::first($configDirectories);
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $path = $this->manager->modulePath($module).DIRECTORY_SEPARATOR.$normalized.DIRECTORY_SEPARATOR.'config.php';

        File::ensureDirectoryExists(dirname($path));

        try {
            $this->writeStub('config/config.stub', $path, $this->replacements($module));
        } catch (FileNotFoundException $exception) {
            $this->warn($exception->getMessage());
        }
    }

    protected function writeStub(string $stub, string $path, array $replacements): void
    {
        $contents = $this->manager->stub($stub);

        $contents = str_replace(array_keys($replacements), array_values($replacements), $contents);

        // Ensure the directory exists before writing the file
        File::ensureDirectoryExists(dirname($path));

        File::put($path, $contents);
    }

    protected function replacements(string $module, ?string $controller = null): array
    {
        $controller = $controller ?? $module.'Controller';

        $providerClass = $this->manager->namespace()."\\{$module}\\App\\Providers\\ServiceProvider";

        return [
            '{{ namespace }}' => $this->manager->namespace(),
            '{{ module }}' => $module,
            '{{ module_lower }}' => Str::lower($module),
            '{{ module_snake }}' => Str::snake($module),
            '{{ module_kebab }}' => Str::kebab($module),
            '{{ slug }}' => Str::kebab($module),
            '{{ controller }}' => $controller,
            '{{ provider_class }}' => $providerClass,
        ];
    }
}
