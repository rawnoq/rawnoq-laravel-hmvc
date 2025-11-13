<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Console\Command;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name : The module name}';

    protected $description = 'Disable an HMVC module';

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

        if (! $this->manager->isEnabled($name)) {
            $this->info("Module [$name] is already disabled.");

            return self::SUCCESS;
        }

        $this->manager->disable($name);

        $this->info("Module [$name] disabled successfully.");

        return self::SUCCESS;
    }
}
