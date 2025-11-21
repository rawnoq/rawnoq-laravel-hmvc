<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;

class MakeModuleMigrationCommand extends MigrateMakeCommand
{
    use ResolvesModules;

    protected $signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}
        {--module= : The module to create the migration in}';

    protected ?string $moduleName = null;

    protected ?string $rawModuleOption = null;

    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);
    }

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

    protected function getMigrationPath()
    {
        if ($this->moduleName) {
            $path = $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR
                .$this->modulePrimaryDirectory($this->moduleName, 'migrations', 'Database/Migrations');

            File::ensureDirectoryExists($path);

            return $path;
        }

        return parent::getMigrationPath();
    }
}
