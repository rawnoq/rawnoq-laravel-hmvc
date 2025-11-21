<?php

namespace Rawnoq\HMVC\Console;

use Illuminate\Foundation\Console\ClassMakeCommand;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

class MakeModuleStrategyCommand extends ClassMakeCommand
{
    use ResolvesModules;

    protected ?string $moduleName = null;

    protected $name = 'make:strategy';

    protected $description = 'Create a new strategy class';

    protected $type = 'Strategy';

    protected function getStub()
    {
        $stubPath = __DIR__.'/../../stubs/module/strategy.stub';

        return file_exists($stubPath)
            ? $stubPath
            : $this->resolveStubPath('/stubs/strategy.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../../stubs/module/strategy.stub';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        // Get strategy interface name from option or auto-detect
        $interfaceOption = $this->option('interface');
        $strategyInterfaceName = $interfaceOption 
            ? $this->qualifyInterfaceName($interfaceOption)
            : $this->getStrategyInterfaceName($name);
        
        $strategyInterfaceNamespace = $this->getStrategyInterfaceNamespace($strategyInterfaceName);

        $stub = str_replace('{{ strategyInterfaceNamespace }}', $strategyInterfaceNamespace, $stub);
        $stub = str_replace('{{ strategyInterface }}', $strategyInterfaceName, $stub);

        return $stub;
    }

    protected function qualifyInterfaceName($name): string
    {
        $name = ltrim($name, '\\/');

        if (str_contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return str_contains($name, '\\')
            ? $name
            : $name;
    }

    protected function getStrategyInterfaceName($name): string
    {
        $className = class_basename($name);
        
        // Remove Strategy suffix if present
        if (str_ends_with($className, 'Strategy')) {
            $className = substr($className, 0, -8);
        }
        
        // For specific strategies like PayPalStrategy, StripeStrategy
        // Try to find common base interface (e.g., PaymentStrategyInterface)
        // Common patterns: PayPalStrategy, StripeStrategy -> PaymentStrategyInterface
        // If we can't determine, use the class name + StrategyInterface
        return $className.'StrategyInterface';
    }

    protected function getStrategyInterfaceNamespace(string $interfaceName): string
    {
        // If interface name contains namespace, return as is
        if (str_contains($interfaceName, '\\')) {
            return $interfaceName;
        }

        if ($this->moduleName) {
            $namespace = $this->moduleRootNamespace($this->moduleName);
            return $namespace.'Interfaces\\'.$interfaceName;
        }

        return 'App\\Interfaces\\'.$interfaceName;
    }

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
            return rtrim($rootNamespace, '\\').'\\Strategies';
        }

        // Handle non-module case - check for app/Strategies
        $strategiesPath = base_path('app').DIRECTORY_SEPARATOR.'Strategies';

        return match (true) {
            is_dir($strategiesPath) => rtrim($rootNamespace, '\\').'\\Strategies',
            default => rtrim($rootNamespace, '\\').'\\Strategies',
        };
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

            // Remove Strategies from the beginning of relative path if present
            if (Str::startsWith($relative, 'Strategies'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'Strategies'.DIRECTORY_SEPARATOR);
            }

            $strategiesPath = $this->moduleBasePath($this->moduleName).DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Strategies';

            $primary = match (true) {
                is_dir($strategiesPath) => 'App'.DIRECTORY_SEPARATOR.'Strategies',
                default => 'App'.DIRECTORY_SEPARATOR.'Strategies',
            };

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.$primary
                .DIRECTORY_SEPARATOR.$relative;
        }

        // Handle non-module case - check for app/Strategies
        $parentPath = parent::getPath($name);
        $appPath = base_path('app').DIRECTORY_SEPARATOR;
        $relative = Str::after($parentPath, $appPath);

        if ($relative === $parentPath) {
            $relative = basename($parentPath);
        }

        $relative = ltrim($relative, DIRECTORY_SEPARATOR);

        // Remove Strategies from the beginning of relative path if present
        if (Str::startsWith($relative, 'Strategies'.DIRECTORY_SEPARATOR)) {
            $relative = Str::after($relative, 'Strategies'.DIRECTORY_SEPARATOR);
        }

        $strategiesPath = base_path('app').DIRECTORY_SEPARATOR.'Strategies';

        $primary = match (true) {
            is_dir($strategiesPath) => 'Strategies',
            default => 'Strategies',
        };

        return base_path('app')
            .DIRECTORY_SEPARATOR.$primary
            .DIRECTORY_SEPARATOR.$relative;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to create the strategy in'],
            ['interface', null, InputOption::VALUE_OPTIONAL, 'The strategy interface to implement'],
        ]);
    }
}

