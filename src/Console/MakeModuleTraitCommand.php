<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\TraitMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
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
            $traitsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Traits';

            return match (true) {
                is_dir($traitsPath) => rtrim($rootNamespace, '\\').'\\Traits',
                default => rtrim($rootNamespace, '\\').'\\Traits',
            };
        }

        // Handle non-module case - check for app/Traits
        $traitsPath = base_path('app').DIRECTORY_SEPARATOR.'Traits';

        return match (true) {
            is_dir($traitsPath) => rtrim($rootNamespace, '\\').'\\Traits',
            default => rtrim($rootNamespace, '\\').'\\Traits',
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

            // Remove Traits from the beginning of relative path if present
            if (Str::startsWith($relative, 'Traits'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Traits'.DIRECTORY_SEPARATOR);
            }

            $traitsPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Traits';

            $primary = match (true) {
                is_dir($traitsPath) => 'App'.DIRECTORY_SEPARATOR.'Traits',
                default => 'App'.DIRECTORY_SEPARATOR.'Traits',
            };

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        // Handle non-module case - check for app/Traits or app/Concerns
        $parentPath = parent::getPath($name);
        $appPath = base_path('app').DIRECTORY_SEPARATOR;
        $relative = Str::after($parentPath, $appPath);

        if ($relative === $parentPath) {
            $relative = basename($parentPath);
        }

        $relative = ltrim($relative, DIRECTORY_SEPARATOR);

        // Remove Traits from the beginning of relative path if present
        if (Str::startsWith($relative, 'Traits'.DIRECTORY_SEPARATOR)) {
            $relative = Str::after($relative, 'Traits'.DIRECTORY_SEPARATOR);
        }

        $traitsPath = base_path('app').DIRECTORY_SEPARATOR.'Traits';

        $primary = match (true) {
            is_dir($traitsPath) => 'Traits',
            default => 'Traits',
        };

        return base_path('app')
            .DIRECTORY_SEPARATOR.$primary
            .DIRECTORY_SEPARATOR.$relative;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the trait in'],
        ]);
    }
}
