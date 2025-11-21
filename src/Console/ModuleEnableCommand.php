<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Console\Command;
use Rawnoq\HMVC\Support\ModuleManager;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name : The module name}';

    protected $description = 'Enable a disabled HMVC module';

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

        if ($this->manager->isEnabled($name)) {
            $this->info("Module [$name] is already enabled.");

            return self::SUCCESS;
        }

        $this->manager->enable($name);

        $this->info("Module [$name] enabled successfully.");

        return self::SUCCESS;
    }
}
