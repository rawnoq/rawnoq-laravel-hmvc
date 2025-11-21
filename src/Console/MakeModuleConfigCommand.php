<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\ConfigMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleConfigCommand extends ConfigMakeCommand
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

    protected function getPath($name): string
    {
        if ($this->moduleName) {
            $configName = Str::finish($this->argument('name'), '.php');
            $primary = str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'config', 'Config'));

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$configName;
        }

        return parent::getPath($name);
    }

    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the config in'],
        ]);
    }
}
