<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\ClassMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleDtoCommand extends ClassMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

    protected $name = 'make:dto';

    protected $description = 'Create a new DTO class';

    protected $type = 'DTO';

    protected function getStub()
    {
        $stubPath = __DIR__.'/../../stubs/module/dto.stub';

        return file_exists($stubPath)
            ? $stubPath
            : $this->resolveStubPath('/stubs/dto.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../../stubs/module/dto.stub';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        // Always use BaseDto from the package
        $baseDtoNamespace = 'Rawnoq\\HMVC\\DTOs\\BaseDto';

        $stub = str_replace('{{ baseDtoNamespace }}', $baseDtoNamespace, $stub);

        return $stub;
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
            return rtrim($rootNamespace, '\\').'\\DTOs';
        }

        // Handle non-module case - check for app/DTOs
        $dtosPath = base_path('app').DIRECTORY_SEPARATOR.'DTOs';

        return match (true) {
            is_dir($dtosPath) => rtrim($rootNamespace, '\\').'\\DTOs',
            default => rtrim($rootNamespace, '\\').'\\DTOs',
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

            // Remove DTOs from the beginning of relative path if present
            if (Str::startsWith($relative, 'DTOs'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'DTOs'.DIRECTORY_SEPARATOR);
            }

            $dtosPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'DTOs';

            $primary = match (true) {
                is_dir($dtosPath) => 'App'.DIRECTORY_SEPARATOR.'DTOs',
                default => 'App'.DIRECTORY_SEPARATOR.'DTOs',
            };

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        // Handle non-module case - check for app/DTOs
        $parentPath = parent::getPath($name);
        $appPath = base_path('app').DIRECTORY_SEPARATOR;
        $relative = Str::after($parentPath, $appPath);

        if ($relative === $parentPath) {
            $relative = basename($parentPath);
        }

        $relative = ltrim($relative, DIRECTORY_SEPARATOR);

        // Remove DTOs from the beginning of relative path if present
        if (Str::startsWith($relative, 'DTOs'.DIRECTORY_SEPARATOR)) {
            $relative = Str::after($relative, 'DTOs'.DIRECTORY_SEPARATOR);
        }

        $dtosPath = base_path('app').DIRECTORY_SEPARATOR.'DTOs';

        $primary = match (true) {
            is_dir($dtosPath) => 'DTOs',
            default => 'DTOs',
        };

        return base_path('app')
            .DIRECTORY_SEPARATOR.$primary
            .DIRECTORY_SEPARATOR.$relative;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the DTO in'],
        ]);
    }
}

