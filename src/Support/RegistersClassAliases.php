<?php

namespace Rawnoq\HMVC\Support;

/**
 * Trait for registering class aliases in module service providers.
 *
 * This trait provides a helper method to register class aliases from configuration,
 * allowing modules to use interchangeable implementations without modifying core code.
 */
trait RegistersClassAliases
{
    /**
     * Register a class alias from configuration.
     *
     * This method creates a class alias that allows modules to reference classes
     * by a consistent name while the actual implementation can be swapped via configuration.
     *
     * @param string $aliasName The alias name to create (e.g., 'Modules\Module\App\Models\BaseUser')
     * @param string|null $actualClass The actual class name from configuration (e.g., 'Modules\User\App\Models\User')
     * @return bool True if alias was registered, false otherwise
     */
    protected function registerClassAlias(string $aliasName, ?string $actualClass): bool
    {
        return register_class_alias($aliasName, $actualClass);
    }

    /**
     * Register multiple class aliases from an array.
     *
     * This method allows you to register multiple class aliases at once.
     * The array should be in the format: ['alias_name' => 'actual_class', ...]
     *
     * @param array<string, string|null> $aliases Array of aliases where key is alias name and value is actual class
     * @return array<string, bool> Array of results where key is alias name and value is registration success status
     */
    protected function registerClassAliases(array $aliases): array
    {
        return register_class_aliases($aliases);
    }
}

