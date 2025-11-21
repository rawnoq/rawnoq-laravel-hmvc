<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Database\Console\Seeds\SeedCommand as BaseSeedCommand;
use Illuminate\Database\Seeder;
use Rawnoq\HMVC\Support\ModuleManager;
use Symfony\Component\Console\Input\InputOption;

class DatabaseSeedCommand extends BaseSeedCommand
{
    protected $name = 'db:seed';

    protected $description = 'Seed the database with records (including modules)';

    public function handle()
    {
        // Run the parent seeder first
        if (!$this->option('modules-only')) {
            $this->runParentSeeder();
        }

        // Then seed all enabled modules
        $this->seedModules();

        return self::SUCCESS;
    }

    protected function runParentSeeder(): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->components->info('Seeding database.');

        $previousConnection = $this->laravel['config']['database.default'];

        $this->laravel['config']['database.default'] = $this->getDatabase();

        $this->getSeeder()->__invoke();

        if ($previousConnection) {
            $this->laravel['config']['database.default'] = $previousConnection;
        }
    }

    protected function getSeeder()
    {
        $class = $this->input->getOption('class') ?? $this->input->getOption('seeder') ?? 'Database\\Seeders\\DatabaseSeeder';

        if (! str_contains($class, '\\')) {
            $class = 'Database\\Seeders\\'.$class;
        }

        if ($class === 'Database\\Seeders\\DatabaseSeeder' &&
            ! class_exists($class)) {
            $class = 'DatabaseSeeder';
        }

        return $this->laravel->make($class)
                    ->setContainer($this->laravel)
                    ->setCommand($this);
    }

    protected function seedModules(): void
    {
        $manager = $this->laravel->make(ModuleManager::class);
        
        foreach ($manager->enabled() as $module) {
            $moduleName = $module['name'] ?? null;
            
            if (!$moduleName) {
                continue;
            }
            
            $seederClass = $manager->namespace() . '\\' . $moduleName . '\\Database\\Seeders\\DatabaseSeeder';
            
            if (class_exists($seederClass)) {
                $this->components->task("Seeding module: {$moduleName}", function () use ($seederClass) {
                    $seeder = $this->laravel->make($seederClass)
                        ->setContainer($this->laravel)
                        ->setCommand($this);
                    
                    $seeder->__invoke();
                    
                    return true;
                });
            }
        }
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['modules-only', null, InputOption::VALUE_NONE, 'Seed only module seeders'],
        ]);
    }
}
