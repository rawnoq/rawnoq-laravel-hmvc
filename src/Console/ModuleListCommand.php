<?php

namespace Rawnoq\LaravelHMVC\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list {--json : Output the list as JSON} {--only= : Filter by status (enabled|disabled)}';

    protected $description = 'List all registered HMVC modules';

    public function __construct(protected ModuleManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $modules = $this->manager->all();

        $only = $this->option('only');

        if ($only) {
            $modules = $modules->filter(function (array $module) use ($only) {
                return match ($only) {
                    'enabled' => $module['enabled'],
                    'disabled' => ! $module['enabled'],
                    default => true,
                };
            });
        }

        if ($this->option('json')) {
            $this->line($modules->values()->toJson(JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($modules->isEmpty()) {
            $this->warn('No modules found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Module', 'Status', 'Routes', 'Controllers', 'Providers', 'Migrations', 'Views'],
            $modules->map(function (array $module) {
                $directories = Arr::get($module, 'directories', []);

                return [
                    $module['name'],
                    $module['enabled'] ? 'Enabled' : 'Disabled',
                    $this->mark($directories['routes'] ?? false),
                    $this->mark($directories['controllers'] ?? false),
                    $this->mark($directories['providers'] ?? false),
                    $this->mark($directories['migrations'] ?? false),
                    $this->mark($directories['views'] ?? false),
                ];
            })->toArray()
        );

        return self::SUCCESS;
    }

    protected function mark(bool $condition): string
    {
        return $condition ? '✓' : '✗';
    }
}
