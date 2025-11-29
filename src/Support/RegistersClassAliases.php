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
}

