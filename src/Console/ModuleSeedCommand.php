<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed {name? : The module name (optional, seeds all modules if not provided)}'
        .' {--class= : The seeder class to run}'
        .' {--force : Force the operation to run when in production}';

    protected $description = 'Run the database seeder for HMVC module(s)';

    public function __construct(protected ModuleManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        if ($name) {
            return $this->seedModule(Str::studly($name));
        }

        // Seed all enabled modules
        $modules = $this->manager->enabled();
        $modulesCount = $modules->count();

        if ($modulesCount === 0) {
            $this->warn('No enabled modules found.');

            return self::SUCCESS;
        }

        $this->info("Seeding {$modulesCount} module(s)...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($modules as $module) {
            $moduleName = $module['name'] ?? null;

            if (! $moduleName) {
                continue;
            }

            $this->line("Seeding module: <comment>{$moduleName}</comment>");

            $result = $this->seedModule($moduleName);

            if ($result === self::SUCCESS) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        $this->newLine();

        if ($failureCount > 0) {
            $this->warn("Completed: {$successCount} succeeded, {$failureCount} failed.");

            return self::FAILURE;
        }

        $this->info("Successfully seeded {$successCount} module(s).");

        return self::SUCCESS;
    }

    protected function seedModule(string $name): int
    {
        if (! $this->manager->moduleExists($name)) {
            $this->error("Module [{$name}] does not exist.");

            return self::FAILURE;
        }

        $class = $this->option('class') ?: $this->resolveDefaultSeeder($name);

        if (! class_exists($class)) {
            $this->error("Seeder class [{$class}] could not be found.");

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
