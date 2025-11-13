<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed {name : The module name}'
        .' {--class= : The seeder class to run}'
        .' {--force : Force the operation to run when in production}';

    protected $description = 'Run the database seeder for a specific HMVC module';

    public function __construct(protected ModuleManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        if (! $this->manager->moduleExists($name)) {
            $this->error("Module [$name] does not exist.");

            return self::FAILURE;
        }

        $class = $this->option('class') ?: $this->resolveDefaultSeeder($name);

        if (! class_exists($class)) {
            $this->error("Seeder class [$class] could not be found.");

            return self::FAILURE;
        }

        $this->call('db:seed', array_filter([
            '--class' => $class,
            '--force' => $this->option('force') ?: null,
        ]));

        return self::SUCCESS;
    }

    protected function resolveDefaultSeeder(string $module): string
    {
        $namespace = rtrim($this->manager->namespace(), '\\');

        return $namespace."\\{$module}\\Database\\Seeders\\DatabaseSeeder";
    }
}
