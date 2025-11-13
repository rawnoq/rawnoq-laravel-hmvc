<?php

namespace Rawnoq\LaravelHMVC\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rawnoq\LaravelHMVC\Providers\HMVCServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HMVCServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('hmvc', [
            'namespace' => 'Modules',
            'modules_path' => base_path('modules'),
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
                    'stub' => 'routes/web.stub',
                    'middleware' => ['web'],
                    'prefix' => null,
                    'namespace' => null,
                    'enabled' => true,
                    'make' => true,
                ],
                [
                    'name' => 'api',
                    'path' => 'Routes/api.php',
                    'stub' => 'routes/api.stub',
                    'middleware' => ['api'],
                    'prefix' => 'api',
                    'namespace' => null,
                    'enabled' => true,
                    'make' => true,
                ],
            ],
            'stubs' => [
                'path' => realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'module') ?: __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'module',
            ],
        ]);
    }
}
