<?php

namespace Rawnoq\HMVC\Support;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleManager
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected Collection $routes;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->routes = collect($config['routes'] ?? []);
    }

    public function namespace(): string
    {
        return $this->config['namespace'] ?? 'Modules';
    }

    public function modulesPath(): string
    {
        return $this->config['modules_path'] ?? base_path('modules');
    }

    public function statusFile(): string
    {
        return $this->config['status_file'] ?? storage_path('app/hmvc/modules.php');
    }

    public function stubsPath(): string
    {
        // Always use package root to resolve stubs path, regardless of config
        $packageRoot = dirname(__DIR__, 2);
        $defaultPath = $packageRoot.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'module';
        $resolved = realpath($defaultPath);

        if ($resolved !== false) {
            return $resolved;
        }

        // Fallback: try to use config path if provided and valid
        if (isset($this->config['stubs']['path'])) {
            $path = $this->config['stubs']['path'];
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

            // If absolute path, try to resolve it
            if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:/', $path)) {
                $resolved = realpath($path);
                if ($resolved !== false) {
                    return $resolved;
                }
            }
        }

        return $defaultPath;
    }

    public function moduleExists(string $module): bool
    {
        return File::exists($this->modulePath($module));
    }

    public function modulePath(string $module): string
    {
        $module = Str::studly($module);

        return $this->modulesPath().DIRECTORY_SEPARATOR.$module;
    }

    public function moduleInfo(string $module): array
    {
        $module = Str::studly($module);
        $path = $this->modulePath($module);

        return [
            'name' => $module,
            'path' => $path,
            'enabled' => $this->isEnabled($module),
            'exists' => File::exists($path),
            'directories' => $this->configuredDirectories(),
            'routes' => $this->routes->all(),
        ];
    }

    public function allModules(): Collection
    {
        if (! File::exists($this->modulesPath())) {
            return collect();
        }

        return collect(File::directories($this->modulesPath()))
            ->map(fn (string $directory) => basename($directory))
            ->sort()
            ->values();
    }

    public function all(): Collection
    {
        return $this->allModules()->map(fn (string $module) => $this->moduleDetails($module));
    }

    public function enabled(): Collection
    {
        return $this->all()->filter(fn (array $module) => $module['enabled']);
    }

    public function isEnabled(string $module): bool
    {
        $module = Str::studly($module);
        $statuses = $this->readStatuses();

        return $statuses[$module] ?? true;
    }

    public function enable(string $module): void
    {
        $module = Str::studly($module);
        $this->updateStatus($module, true);
    }

    public function disable(string $module): void
    {
        $module = Str::studly($module);
        $this->updateStatus($module, false);
    }

    public function routes(): Collection
    {
        return $this->routes->map(function (array $route) {
            $route['path'] = Arr::get($route, 'path');
            $route['middleware'] = Arr::get($route, 'middleware', []);
            $route['prefix'] = Arr::get($route, 'prefix');
            $route['namespace'] = Arr::get($route, 'namespace');
            $route['enabled'] = Arr::get($route, 'enabled', true);
            $route['make'] = Arr::get($route, 'make', true);
            $route['stub'] = Arr::get($route, 'stub');

            return $route;
        });
    }

    public function configuredDirectories(): array
    {
        return $this->config['directories'] ?? [];
    }

    public function directoriesFor(string $type): array
    {
        $directories = $this->configuredDirectories();

        $values = Arr::get($directories, $type, []);

        return is_array($values) ? $values : [$values];
    }

    public function firstExistingDirectory(string $module, string $type): ?string
    {
        $paths = $this->directoriesFor($type);

        foreach ($paths as $relative) {
            $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $full = $this->modulePath($module).DIRECTORY_SEPARATOR.$normalized;

            if (File::isDirectory($full)) {
                return $full;
            }
        }

        return null;
    }

    public function existingDirectories(string $module, string $type): array
    {
        return collect($this->directoriesFor($type))
            ->map(function (string $relative) use ($module) {
                $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);

                return $this->modulePath($module).DIRECTORY_SEPARATOR.$normalized;
            })
            ->filter(fn (string $path) => File::isDirectory($path))
            ->values()
            ->all();
    }

    public function providerClasses(string $module): array
    {
        $module = Str::studly($module);
        $modulePath = $this->modulePath($module);

        return collect($this->existingDirectories($module, 'providers'))
            ->flatMap(function (string $directory) use ($module, $modulePath) {
                return collect(File::allFiles($directory))
                    ->filter(fn ($file) => $file->getExtension() === 'php')
                    ->map(function ($file) use ($module, $modulePath) {
                        $relativePath = Str::after($file->getPathname(), $modulePath.DIRECTORY_SEPARATOR);
                        $relativeClass = Str::replaceLast('.php', '', $relativePath);
                        $relativeClass = str_replace(DIRECTORY_SEPARATOR, '\\', $relativeClass);

                        return $this->namespace()."\\{$module}\\".$relativeClass;
                    });
            })
            ->filter(fn (string $class) => class_exists($class))
            ->unique()
            ->values()
            ->all();
    }

    public function moduleHasRoutes(string $module): bool
    {
        $module = Str::studly($module);

        return $this->routes()->contains(function (array $route) use ($module) {
            $relative = Arr::get($route, 'path');
            if (! $relative) {
                return false;
            }

            $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $path = $this->modulePath($module).DIRECTORY_SEPARATOR.$normalized;

            return File::exists($path);
        });
    }

    public function relativeToBase(string $path): string
    {
        return Str::after($path, base_path().DIRECTORY_SEPARATOR);
    }

    public function ensureModuleDirectory(string $module, string $relative): void
    {
        $module = Str::studly($module);
        $normalized = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $normalized = ltrim($normalized, DIRECTORY_SEPARATOR);
        $fullPath = $this->modulePath($module).DIRECTORY_SEPARATOR.$normalized;

        // Create the directory recursively using makeDirectory
        // The third parameter (true) ensures parent directories are created
        File::makeDirectory($fullPath, 0755, true, true);
    }

    public function stub(string $relativeStub): string
    {
        $path = $this->stubsPath().DIRECTORY_SEPARATOR.ltrim($relativeStub, DIRECTORY_SEPARATOR);

        if (! File::exists($path)) {
            throw new FileNotFoundException("Stub not found at [$relativeStub].");
        }

        return File::get($path);
    }

    protected function moduleDetails(string $module): array
    {
        $module = Str::studly($module);
        $path = $this->modulePath($module);

        $directories = collect($this->configuredDirectories())
            ->mapWithKeys(function ($values, string $key) use ($module) {
                $values = is_array($values) ? $values : [$values];

                $exists = collect($values)->contains(function ($relative) use ($module) {
                    $full = $this->modulePath($module).DIRECTORY_SEPARATOR.$relative;

                    return File::exists($full) && File::isDirectory($full);
                });

                return [$key => $exists];
            })
            ->put('routes', $this->moduleHasRoutes($module))
            ->put('providers', ! empty($this->providerClasses($module)))
            ->all();

        return [
            'name' => $module,
            'path' => $path,
            'enabled' => $this->isEnabled($module),
            'exists' => File::exists($path),
            'directories' => $directories,
        ];
    }

    protected function updateStatus(string $module, bool $value): void
    {
        $statuses = $this->readStatuses();
        $statuses[$module] = $value;

        File::ensureDirectoryExists(dirname($this->statusFile()));
        File::put($this->statusFile(), '<?php return '.var_export($statuses, true).';'.PHP_EOL);
    }

    protected function readStatuses(): array
    {
        $file = $this->statusFile();

        if (! File::exists($file)) {
            return [];
        }

        $statuses = include $file;

        return is_array($statuses) ? $statuses : [];
    }
}
