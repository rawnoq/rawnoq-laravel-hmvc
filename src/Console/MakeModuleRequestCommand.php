<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleRequestCommand extends RequestMakeCommand
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
            return $this->moduleRootNamespace($this->moduleName);
        }

        return parent::rootNamespace();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->moduleName) {
            return rtrim($rootNamespace, '\\').'\\Http\\Requests';
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

            if (Str::startsWith($relative, 'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR);
            }

            $primary = str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'requests', 'App/Http/Requests'));

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        return parent::getPath($name);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the request in'],
        ]);
    }
}
