<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\InterfaceMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleStrategyInterfaceCommand extends InterfaceMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

    protected $name = 'make:strategy-interface';

    protected $description = 'Create a new strategy interface';

    protected $type = 'Strategy Interface';

    protected function getStub()
    {
        $stubPath = __DIR__.'/../../stubs/module/strategy-interface.stub';

        return file_exists($stubPath)
            ? $stubPath
            : $this->resolveStubPath('/stubs/strategy-interface.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../../stubs/module/strategy-interface.stub';
    }

    public function handle(): int
    {
        $moduleOption = $this->option('module');

        if ($moduleOption) {
            $this->moduleName = $this->normalizeModule($moduleOption);

            if (! $this->moduleExists($this->moduleName)) {
                $this->components->error("Module [{$this->moduleName}] does not exist.");
                $this->moduleName = null;

                return self::FAILURE;
            }
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
            $interfacesPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Interfaces';

            return match (true) {
                is_dir($interfacesPath) => rtrim($rootNamespace, '\\').'\\Interfaces',
                default => rtrim($rootNamespace, '\\').'\\Interfaces',
            };
        }

        // Handle non-module case - check for app/Interfaces
        $interfacesPath = base_path('app').DIRECTORY_SEPARATOR.'Interfaces';

        return match (true) {
            is_dir($interfacesPath) => rtrim($rootNamespace, '\\').'\\Interfaces',
            default => rtrim($rootNamespace, '\\').'\\Interfaces',
        };
    }

    protected function getPath($name)
    {
        if ($this->moduleName) {
            $parentPath = parent::getPath($name);
            $appPath = base_path('app').DIRECTORY_SEPARATOR;
            $relative = Str::after($parentPath, $appPath);

            if ($relative === $parentPath) {
                $relative = basename($parentPath);
            }

            $relative = ltrim($relative, DIRECTORY_SEPARATOR);

            // Remove Interfaces from the beginning of relative path if present
            if (Str::startsWith($relative, 'Interfaces'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Interfaces'.DIRECTORY_SEPARATOR);
            }

            $interfacesPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Interfaces';

            $primary = match (true) {
                is_dir($interfacesPath) => 'App'.DIRECTORY_SEPARATOR.'Interfaces',
                default => 'App'.DIRECTORY_SEPARATOR.'Interfaces',
            };

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        // Handle non-module case - check for app/Interfaces
        $parentPath = parent::getPath($name);
        $appPath = base_path('app').DIRECTORY_SEPARATOR;
        $relative = Str::after($parentPath, $appPath);

        if ($relative === $parentPath) {
            $relative = basename($parentPath);
        }

        $relative = ltrim($relative, DIRECTORY_SEPARATOR);

        // Remove Interfaces from the beginning of relative path if present
        if (Str::startsWith($relative, 'Interfaces'.DIRECTORY_SEPARATOR)) {
            $relative = Str::after($relative, 'Interfaces'.DIRECTORY_SEPARATOR);
        }

        $interfacesPath = base_path('app').DIRECTORY_SEPARATOR.'Interfaces';

        $primary = match (true) {
            is_dir($interfacesPath) => 'Interfaces',
            default => 'Interfaces',
        };

        return base_path('app')
            .DIRECTORY_SEPARATOR.$primary
            .DIRECTORY_SEPARATOR.$relative;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the strategy interface in'],
        ]);
    }
}

