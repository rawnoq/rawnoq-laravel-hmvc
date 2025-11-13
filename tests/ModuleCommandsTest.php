<?php

namespace Rawnoq\LaravelHMVC\Tests;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;

class ModuleCommandsTest extends TestCase
{
    protected string $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modulesPath = base_path('modules');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->modulesPath)) {
            File::deleteDirectory($this->modulesPath);
        }

        if (File::exists(storage_path('app/hmvc/modules.php'))) {
            File::delete(storage_path('app/hmvc/modules.php'));
        }

        parent::tearDown();
    }

    #[Test]
    public function it_can_create_a_module()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        $this->assertDirectoryExists($modulePath);

        // Check directories using the same normalization as ModuleManager
        $expectedPaths = [
            str_replace('/', DIRECTORY_SEPARATOR, $modulePath.'/App/Http/Controllers'),
            str_replace('/', DIRECTORY_SEPARATOR, $modulePath.'/Routes'),
            str_replace('/', DIRECTORY_SEPARATOR, $modulePath.'/App/Providers'),
        ];

        foreach ($expectedPaths as $path) {
            $this->assertDirectoryExists($path, "Directory should exist: {$path}");
        }
    }

    #[Test]
    public function it_can_create_a_plain_module()
    {
        $this->artisan('module:make', [
            'name' => 'PlainModule',
            '--plain' => true,
        ])->assertSuccessful();

        $this->assertDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'PlainModule');
        $this->assertFileDoesNotExist($this->modulesPath.DIRECTORY_SEPARATOR.'PlainModule'.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'PlainModuleController.php');
    }

    #[Test]
    public function it_fails_when_module_already_exists()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertFailed();
    }

    #[Test]
    public function it_can_force_create_module()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:make', [
            'name' => 'TestModule',
            '--force' => true,
        ])->assertSuccessful();
    }

    #[Test]
    public function it_can_list_modules()
    {
        $this->artisan('module:make', ['name' => 'ModuleA'])
            ->assertSuccessful();
        $this->artisan('module:make', ['name' => 'ModuleB'])
            ->assertSuccessful();

        $this->artisan('module:list')
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_list_modules_as_json()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:list', ['--json' => true])
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_enable_a_module()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:enable', ['name' => 'TestModule'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_disable_a_module()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:disable', ['name' => 'TestModule'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_fails_to_enable_nonexistent_module()
    {
        $this->artisan('module:enable', ['name' => 'NonExistent'])
            ->assertFailed();
    }

    #[Test]
    public function it_fails_to_disable_nonexistent_module()
    {
        $this->artisan('module:disable', ['name' => 'NonExistent'])
            ->assertFailed();
    }

    #[Test]
    public function it_handles_case_insensitive_module_names()
    {
        $this->artisan('module:make', ['name' => 'TestModule'])
            ->assertSuccessful();

        $this->artisan('module:enable', ['name' => 'testmodule'])
            ->assertSuccessful();

        $this->artisan('module:disable', ['name' => 'TESTMODULE'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_create_module_using_make_module_alias()
    {
        $this->artisan('make:module', ['name' => 'AliasModule'])
            ->assertSuccessful();

        $this->assertDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'AliasModule');
        $this->assertDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'AliasModule'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers');
    }
}
