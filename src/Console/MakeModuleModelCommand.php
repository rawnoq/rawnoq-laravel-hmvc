<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleModelCommand extends ModelMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

    protected ?string $rawModuleOption = null;

    public function handle()
    {
        $moduleOption = $this->option('module');

        if ($moduleOption) {
            $this->rawModuleOption = $moduleOption;
            $this->moduleName = $this->normalizeModule($moduleOption);

            if (! $this->moduleExists($this->moduleName)) {
                $this->components->error("Module [{$this->moduleName}] does not exist.");
                $this->resetModuleState();

                return;
            }
        }

        parent::handle();

        $this->resetModuleState();
    }

    protected function resetModuleState(): void
    {
        $this->moduleName = null;
        $this->rawModuleOption = null;
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
            $modulePath = $this->moduleBasePath($this->moduleName);
            $modelsPath = $modulePath.'/Models';

            return rtrim($rootNamespace, '\\').(is_dir($modelsPath) ? '\\Models' : '');
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

            if (Str::startsWith($relative, 'Models'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Models'.DIRECTORY_SEPARATOR);
            }

            $primary = str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'models', 'Models'));

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        return parent::getPath($name);
    }

    protected function createFactory()
    {
        if ($this->moduleName) {
            $factory = Str::studly($this->argument('name'));

            $this->call('make:factory', array_filter([
                'name' => "{$factory}Factory",
                '--model' => $this->qualifyClass($this->getNameInput()),
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createFactory();
    }

    protected function createMigration()
    {
        if ($this->moduleName) {
            $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

            if ($this->option('pivot')) {
                $table = Str::singular($table);
            }

            $this->call('make:migration', array_filter([
                'name' => "create_{$table}_table",
                '--create' => $table,
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createMigration();
    }

    protected function createSeeder()
    {
        if ($this->moduleName) {
            $seeder = Str::studly(class_basename($this->argument('name')));

            $this->call('make:seeder', array_filter([
                'name' => "{$seeder}Seeder",
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createSeeder();
    }

    protected function createController()
    {
        if ($this->moduleName) {
            $controller = Str::studly(class_basename($this->argument('name')));
            $modelName = $this->qualifyClass($this->getNameInput());

            $this->call('make:controller', array_filter([
                'name' => "{$controller}Controller",
                '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
                '--api' => $this->option('api'),
                '--requests' => $this->option('requests') || $this->option('all'),
                '--test' => $this->option('test'),
                '--pest' => $this->option('pest'),
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createController();
    }

    protected function createFormRequests()
    {
        if ($this->moduleName) {
            $request = Str::studly(class_basename($this->argument('name')));

            $this->call('make:request', array_filter([
                'name' => "Store{$request}Request",
                '--module' => $this->rawModuleOption,
            ]));

            $this->call('make:request', array_filter([
                'name' => "Update{$request}Request",
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createFormRequests();
    }

    protected function createPolicy()
    {
        if ($this->moduleName) {
            $policy = Str::studly(class_basename($this->argument('name')));

            $this->call('make:policy', array_filter([
                'name' => "{$policy}Policy",
                '--model' => $this->qualifyClass($this->getNameInput()),
                '--module' => $this->rawModuleOption,
            ]));

            return;
        }

        parent::createPolicy();
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the model in'],
        ]);
    }
}
