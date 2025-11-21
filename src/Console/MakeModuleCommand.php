<?php

namespace Rawnoq\HMVC\Console;

use Rawnoq\HMVC\Support\ModuleManager;

class MakeModuleCommand extends ModuleMakeCommand
{
    protected $signature = 'make:module {name : The module name}'
        .' {--force : Overwrite the module if it already exists}'
        .' {--plain : Generate an empty module scaffold}'
        .' {--api : Include API routing scaffold if available}';

    protected $description = 'Create a new HMVC module scaffold (alias for module:make)';

    public function __construct(ModuleManager $manager)
    {
        parent::__construct($manager);
    }
}
