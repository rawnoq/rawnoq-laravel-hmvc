<?php

namespace Rawnoq\HMVC\Console\Concerns;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rawnoq\HMVC\Support\ModuleManager;

trait ResolvesModules
{
    protected function moduleManager(): ModuleManager
    {
        if (! $this->laravel->bound(ModuleManager::class)) {
            throw new BindingResolutionException('ModuleManager binding not found.');
        }

        return $this->laravel->make(ModuleManager::class);
    }

    protected function normalizeModule(string $module): string
    {
        return Str::studly($module);
    }

    protected function moduleExists(string $module): bool
    {
        return $this->moduleManager()->moduleExists($module);
    }

    protected function moduleRootNamespace(string $module, bool $includeAppSegment = true): string
    {
        $namespace = $this->moduleManager()->namespace().'\\'.$module.'\\';

        if ($includeAppSegment) {
            $namespace .= 'App\\';
        }

        return $namespace;
    }

    protected function moduleBasePath(string $module): string
    {
        return $this->moduleManager()->modulePath($module);
    }

    protected function modulePrimaryDirectory(string $module, string $key, string $default): string
    {
        $directories = $this->moduleManager()->directoriesFor($key);

        return Arr::first($directories) ?: $default;
    }
}
