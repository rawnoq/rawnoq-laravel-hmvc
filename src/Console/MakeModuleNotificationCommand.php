<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\NotificationMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleNotificationCommand extends NotificationMakeCommand
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
            return rtrim($rootNamespace, '\\').'\\Notifications';
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

            if (Str::startsWith($relative, 'Notifications'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Notifications'.DIRECTORY_SEPARATOR);
            }

            $primary = str_replace('/', DIRECTORY_SEPARATOR, $this->modulePrimaryDirectory($this->moduleName, 'notifications', 'Notifications'));

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        return parent::getPath($name);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the notification in'],
        ]);
    }
}
