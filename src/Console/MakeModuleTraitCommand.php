<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Foundation\Console\TraitMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleTraitCommand extends TraitMakeCommand
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
            $concernsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Concerns';
            $traitsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Traits';

            return match (true) {
                is_dir($concernsPath) => rtrim($rootNamespace, '\\').'\\Concerns',
                is_dir($traitsPath) => rtrim($rootNamespace, '\\').'\\Traits',
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

            $concernsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Concerns';
            $traitsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'Traits';

            $primary = match (true) {
                is_dir($concernsPath) => str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'traits', 'Concerns')),
                is_dir($traitsPath) => str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'traits', 'Traits')),
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
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the trait in'],
        ]);
    }
}
