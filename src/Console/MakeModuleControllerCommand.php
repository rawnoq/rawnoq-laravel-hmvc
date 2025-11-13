<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleControllerCommand extends ControllerMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

    public function handle(): int
    {
        $moduleOption = $this->option('module');

        if (! $moduleOption) {
            $result = parent::handle();

            $this->moduleName = null;

            return is_int($result) ? $result : self::SUCCESS;
        }

        $this->moduleName = $this->normalizeModule($moduleOption);

        if (! $this->moduleExists($this->moduleName)) {
            $this->components->error("Module [{$this->moduleName}] does not exist.");
            $this->moduleName = null;

            return self::FAILURE;
        }

        $result = parent::handle();

        $this->moduleName = null;

        return is_int($result) ? $result : self::SUCCESS;
    }

    protected function rootNamespace()
    {
        if ($this->moduleName) {
            return $this->moduleRootNamespace($this->moduleName);
        }

        return parent::rootNamespace();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->moduleName) {
            return rtrim($rootNamespace, '\\').'\\Http\\Controllers';
        }

        return parent::getDefaultNamespace($rootNamespace);
    }

    protected function getPath($name)
    {
        if ($this->moduleName) {
            $parentPath = parent::getPath($name);
            $appPath = app_path().DIRECTORY_SEPARATOR;
            $relative = Str::after($parentPath, $appPath);

            if ($relative === $parentPath) {
                $relative = basename($parentPath);
            }

            $relative = ltrim($relative, DIRECTORY_SEPARATOR);

            if (Str::startsWith($relative, 'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR);
            }

            $primary = str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'controllers', 'App/Http/Controllers'));

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        return parent::getPath($name);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the controller in'],
        ]);
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        if ($this->moduleName) {
            if (! Str::contains($class, 'extends Controller')) {
                $class = preg_replace('/class\s+(\w+)/', 'class $1 extends Controller', $class, 1);
            }

            if (! Str::contains($class, 'App\\Http\\Controllers\\Controller')) {
                $class = preg_replace(
                    '/(namespace\s+[^;]+;)/',
                    "$1\n\nuse App\\Http\\Controllers\\Controller;",
                    $class,
                    1
                );
            }
        }

        return $class;
    }

    protected function buildFormRequestReplacements(array $replace, $modelClass)
    {
        if ($this->moduleName && $this->option('requests')) {
            $namespace = $this->moduleRootNamespace($this->moduleName).'Http\Requests';

            [$storeRequestClass, $updateRequestClass] = $this->generateFormRequests(
                $modelClass, 'Request', 'Request'
            );

            $namespacedRequests = $namespace.'\\'.$storeRequestClass.';';

            if ($storeRequestClass !== $updateRequestClass) {
                $namespacedRequests .= PHP_EOL.'use '.$namespace.'\\'.$updateRequestClass.';';
            }

            return array_merge($replace, [
                '{{ storeRequest }}' => $storeRequestClass,
                '{{storeRequest}}' => $storeRequestClass,
                '{{ updateRequest }}' => $updateRequestClass,
                '{{updateRequest}}' => $updateRequestClass,
                '{{ namespacedStoreRequest }}' => $namespace.'\\'.$storeRequestClass,
                '{{namespacedStoreRequest}}' => $namespace.'\\'.$storeRequestClass,
                '{{ namespacedUpdateRequest }}' => $namespace.'\\'.$updateRequestClass,
                '{{namespacedUpdateRequest}}' => $namespace.'\\'.$updateRequestClass,
                '{{ namespacedRequests }}' => $namespacedRequests,
                '{{namespacedRequests}}' => $namespacedRequests,
            ]);
        }

        return parent::buildFormRequestReplacements($replace, $modelClass);
    }

    protected function generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass)
    {
        if ($this->moduleName) {
            $storeRequestClass = 'Store'.class_basename($modelClass).'Request';

            $this->call('make:request', array_filter([
                'name' => $storeRequestClass,
                '--module' => $this->moduleName,
            ]));

            $updateRequestClass = 'Update'.class_basename($modelClass).'Request';

            $this->call('make:request', array_filter([
                'name' => $updateRequestClass,
                '--module' => $this->moduleName,
            ]));

            return [$storeRequestClass, $updateRequestClass];
        }

        return parent::generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass);
    }
}
