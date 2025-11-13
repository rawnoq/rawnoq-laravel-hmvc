<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Console\Command;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate {name : The module name}'
        .' {--force : Force the operation to run when in production}'
        .' {--seed : Run the module seeder after migrating}';

    protected $description = 'Run database migrations for a specific HMVC module';

    public function __construct(protected ModuleManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! $this->manager->moduleExists($name)) {
            $this->error("Module [$name] does not exist.");

            return self::FAILURE;
        }

        $directories = $this->manager->existingDirectories($name, 'migrations');

        if (empty($directories)) {
            $this->warn("Module [$name] does not contain migrations.");

            return self::SUCCESS;
        }

        foreach ($directories as $directory) {
            $relative = $this->manager->relativeToBase($directory);

            $parameters = ['--path' => $relative];

            if ($this->option('force')) {
                $parameters['--force'] = true;
            }

            $this->call('migrate', $parameters);
        }

        if ($this->option('seed')) {
            $this->call('module:seed', ['name' => $name]);
        }

        return self::SUCCESS;
    }
}
