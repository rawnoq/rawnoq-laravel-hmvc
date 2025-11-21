<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleFactoryCommand extends FactoryMakeCommand
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

                return self::FAILURE;
            }
        }

        $result = parent::handle();

        $this->resetModuleState();

        return is_int($result) ? $result : self::SUCCESS;
    }

    protected function resetModuleState(): void
    {
        $this->moduleName = null;
        $this->rawModuleOption = null;
    }

    protected function rootNamespace()
    {
        if ($this->moduleName) {
            return $this->moduleRootNamespace($this->moduleName, false);
        }

        return parent::rootNamespace();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->moduleName) {
            return rtrim($rootNamespace, '\\').'\\Database\\Factories';
        }

        return parent::getDefaultNamespace($rootNamespace);
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        if ($this->moduleName) {
            $namespace = rtrim($this->moduleRootNamespace($this->moduleName, false), '\\').'\\Database\\Factories';
            $class = preg_replace('/^namespace\s+[^;]+;/m', 'namespace '.$namespace.';', $class);
        }

        return $class;
    }

    protected function getPath($name)
    {
        if ($this->moduleName) {
            $relative = $this->modulePrimaryDirectory($this->moduleName, 'factories', 'Database/Factories');
            $relative = str_replace('/', DIRECTORY_SEPARATOR, $relative);

            $parentPath = parent::getPath($name);
            $baseFactoriesPath = $this->laravel->databasePath().DIRECTORY_SEPARATOR.'factories'.DIRECTORY_SEPARATOR;
            $relativeSuffix = Str::after($parentPath, $baseFactoriesPath);
            $relativeSuffix = ltrim($relativeSuffix, DIRECTORY_SEPARATOR);

            $prefix = 'Database'.DIRECTORY_SEPARATOR.'Factories'.DIRECTORY_SEPARATOR;
            if (Str::startsWith($relativeSuffix, $prefix)) {
                $relativeSuffix = Str::after($relativeSuffix, $prefix);
            }

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$relative
                .DIRECTORY_SEPARATOR.$relativeSuffix;
        }

        return parent::getPath($name);
    }

    protected function qualifyModel($model)
    {
        if ($this->moduleName && ! Str::startsWith($model, '\\')) {
            $model = trim(str_replace('/', '\\', $model), '\\');

            $modelsRelative = trim($this->modulePrimaryDirectory($this->moduleName, 'models', 'Models'), '/');
            $modelsRelative = str_replace('/', '\\', $modelsRelative);

            $namespace = rtrim($this->moduleRootNamespace($this->moduleName), '\\');

            if ($modelsRelative !== '') {
                $namespace .= '\\'.$modelsRelative;
            }

            return $namespace.'\\'.$model;
        }

        return parent::qualifyModel($model);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the factory in'],
        ]);
    }
}
