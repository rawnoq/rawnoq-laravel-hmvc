<?php

namespace Rawnoq\LaravelHMVC\Tests;

use PHPUnit\Framework\Attributes\Test;
use Rawnoq\LaravelHMVC\Providers\HMVCServiceProvider;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_service_provider()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(HMVCServiceProvider::class, $providers);
    }

    #[Test]
    public function it_merges_config_correctly()
    {
        $this->assertIsArray(config('hmvc'));
        $this->assertEquals('Modules', config('hmvc.namespace'));
        $this->assertIsArray(config('hmvc.directories'));
    }

    #[Test]
    public function it_registers_module_manager()
    {
        $manager = $this->app->make(ModuleManager::class);

        $this->assertInstanceOf(ModuleManager::class, $manager);
    }

    #[Test]
    public function it_can_publish_config()
    {
        $this->artisan('vendor:publish', [
            '--provider' => HMVCServiceProvider::class,
            '--tag' => 'hmvc-config',
        ])->assertSuccessful();

        $this->assertFileExists(config_path('hmvc.php'));
    }

    #[Test]
    public function it_can_publish_stubs()
    {
        $this->artisan('vendor:publish', [
            '--provider' => HMVCServiceProvider::class,
            '--tag' => 'hmvc-stubs',
        ])->assertSuccessful();

        $this->assertDirectoryExists(base_path('stubs/hmvc/module'));
    }

    #[Test]
    public function it_registers_module_commands()
    {
        $this->artisan('module:list')->assertSuccessful();
    }
}
