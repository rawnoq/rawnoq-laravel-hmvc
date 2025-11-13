<?php

namespace Rawnoq\LaravelHMVC\Tests;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Rawnoq\LaravelHMVC\Support\ModuleManager;

class ModuleManagerTest extends TestCase
{
    protected ModuleManager $manager;

    protected string $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modulesPath = base_path('modules');
        $this->manager = new ModuleManager([
            'namespace' => 'Modules',
            'modules_path' => $this->modulesPath,
            'status_file' => storage_path('app/hmvc/modules.php'),
            'directories' => [
                'controllers' => ['App/Http/Controllers'],
                'models' => ['App/Models'],
                'views' => ['Resources/Views'],
                'migrations' => ['Database/Migrations'],
                'providers' => ['App/Providers'],
            ],
            'routes' => [
                [
                    'name' => 'web',
                    'path' => 'Routes/web.php',
                    'middleware' => ['web'],
                    'enabled' => true,
                ],
            ],
            'stubs' => [
                'path' => dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'module',
            ],
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test modules
        if (File::exists($this->modulesPath)) {
            File::deleteDirectory($this->modulesPath);
        }

        if (File::exists(storage_path('app/hmvc/modules.php'))) {
            File::delete(storage_path('app/hmvc/modules.php'));
        }

        parent::tearDown();
    }

    #[Test]
    public function it_can_get_modules_path()
    {
        $this->assertEquals($this->modulesPath, $this->manager->modulesPath());
    }

    #[Test]
    public function it_can_get_namespace()
    {
        $this->assertEquals('Modules', $this->manager->namespace());
    }

    #[Test]
    public function it_can_check_if_module_exists()
    {
        // Create a test module
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);

        $this->assertTrue($this->manager->moduleExists('TestModule'));
        $this->assertTrue($this->manager->moduleExists('testmodule')); // Case insensitive
        $this->assertFalse($this->manager->moduleExists('NonExistentModule'));
    }

    #[Test]
    public function it_can_get_module_path()
    {
        $expectedPath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        $this->assertEquals($expectedPath, $this->manager->modulePath('TestModule'));
        // Str::studly converts 'testmodule' to 'Testmodule', not 'TestModule'
        $this->assertEquals($this->modulesPath.DIRECTORY_SEPARATOR.'Testmodule', $this->manager->modulePath('testmodule'));
    }

    #[Test]
    public function it_can_list_all_modules()
    {
        // Create test modules
        File::ensureDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'ModuleA');
        File::ensureDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'ModuleB');

        $modules = $this->manager->allModules();

        $this->assertCount(2, $modules);
        $this->assertTrue($modules->contains('ModuleA'));
        $this->assertTrue($modules->contains('ModuleB'));
    }

    #[Test]
    public function it_returns_empty_collection_when_no_modules_exist()
    {
        $modules = $this->manager->allModules();
        $this->assertTrue($modules->isEmpty());
    }

    #[Test]
    public function it_can_enable_and_disable_modules()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);

        // Module is enabled by default
        $this->assertTrue($this->manager->isEnabled('TestModule'));

        // Disable module
        $this->manager->disable('TestModule');
        $this->assertFalse($this->manager->isEnabled('TestModule'));

        // Enable module
        $this->manager->enable('TestModule');
        $this->assertTrue($this->manager->isEnabled('TestModule'));
    }

    #[Test]
    public function it_can_get_enabled_modules()
    {
        // Create test modules
        File::ensureDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'ModuleA');
        File::ensureDirectoryExists($this->modulesPath.DIRECTORY_SEPARATOR.'ModuleB');

        $this->manager->disable('ModuleB');

        $enabled = $this->manager->enabled();

        $this->assertCount(1, $enabled);
        $this->assertEquals('ModuleA', $enabled->first()['name']);
    }

    #[Test]
    public function it_can_get_module_info()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);

        $info = $this->manager->moduleInfo('TestModule');

        $this->assertEquals('TestModule', $info['name']);
        $this->assertEquals($modulePath, $info['path']);
        $this->assertTrue($info['enabled']);
        $this->assertTrue($info['exists']);
        $this->assertIsArray($info['directories']);
    }

    #[Test]
    public function it_can_get_routes()
    {
        $routes = $this->manager->routes();

        $this->assertCount(1, $routes);
        $this->assertEquals('web', $routes->first()['name']);
    }

    #[Test]
    public function it_can_get_configured_directories()
    {
        $directories = $this->manager->configuredDirectories();

        $this->assertArrayHasKey('controllers', $directories);
        $this->assertArrayHasKey('models', $directories);
        $this->assertArrayHasKey('views', $directories);
    }

    #[Test]
    public function it_can_find_existing_directories()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers');
        File::ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Models');

        $controllersDir = $this->manager->firstExistingDirectory('TestModule', 'controllers');
        $expectedControllersPath = $modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers';
        $this->assertEquals($expectedControllersPath, $controllersDir);

        $modelsDir = $this->manager->firstExistingDirectory('TestModule', 'models');
        $this->assertEquals($modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Models', $modelsDir);
    }

    #[Test]
    public function it_returns_null_for_non_existing_directory()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);

        $result = $this->manager->firstExistingDirectory('TestModule', 'nonexistent');
        $this->assertNull($result);
    }

    #[Test]
    public function it_can_get_all_existing_directories()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers');
        File::ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Models');

        $directories = $this->manager->existingDirectories('TestModule', 'controllers');
        $this->assertCount(1, $directories);
        $expectedPath = $modulePath.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers';
        $this->assertContains($expectedPath, $directories);
    }

    #[Test]
    public function it_can_check_if_module_has_routes()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);
        File::ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'Routes');
        File::put($modulePath.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'web.php', '<?php');

        $this->assertTrue($this->manager->moduleHasRoutes('TestModule'));
    }

    #[Test]
    public function it_returns_false_when_module_has_no_routes()
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.'TestModule';
        File::ensureDirectoryExists($modulePath);

        $this->assertFalse($this->manager->moduleHasRoutes('TestModule'));
    }
}
