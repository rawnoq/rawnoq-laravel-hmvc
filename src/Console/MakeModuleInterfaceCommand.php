<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Foundation\Console\InterfaceMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleInterfaceCommand extends InterfaceMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

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
            $contractsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Contracts';
            $interfacesPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Interfaces';

            return match (true) {
                is_dir($contractsPath) => rtrim($rootNamespace, '\\').'\\Contracts',
                is_dir($interfacesPath) => rtrim($rootNamespace, '\\').'\\Interfaces',
                default => rtrim($rootNamespace, '\\'),
            };
        }

        return parent::getDefaultNamespace($rootNamespace);
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

            $contractsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Contracts';
            $interfacesPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Interfaces';

            $primary = match (true) {
                is_dir($contractsPath) => str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'interfaces', 'Contracts')),
                is_dir($interfacesPath) => str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'interfaces', 'Interfaces')),
                default => '',
            };

            $path = $this->moduleBasePath($this->moduleName);
            if ($primary) {
                $path .= DIRECTORY_SEPARATOR.$primary;
            }
            $path .= DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $relative);

            return $path;
        }

        return parent::getPath($name);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the interface in'],
        ]);
    }
}
