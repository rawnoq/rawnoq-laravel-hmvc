<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleSeederCommand extends SeederMakeCommand
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

        parent::handle();

        $this->resetModuleState();

        return self::SUCCESS;
    }

    protected function resetModuleState(): void
    {
        $this->moduleName = null;
        $this->rawModuleOption = null;
    }

    protected function rootNamespace()
    {
        if ($this->moduleName) {
            return $this->moduleRootNamespace($this->moduleName).'Database\\Seeders\\';
        }

        return parent::rootNamespace();
    }

    protected function getPath($name)
    {
        if ($this->moduleName) {
            $name = str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $name));

            $relative = $this->modulePrimaryDirectory($this->moduleName, 'seeders', 'Database/Seeders');

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$relative
                .DIRECTORY_SEPARATOR.$name.'.php';
        }

        return parent::getPath($name);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the seeder in'],
        ]);
    }
}
