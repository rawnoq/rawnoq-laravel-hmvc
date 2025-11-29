<?php

if (!function_exists('register_class_alias')) {
    /**
     * Register a class alias from configuration.
     *
     * This helper function creates a class alias that allows modules to reference classes
     * by a consistent name while the actual implementation can be swapped via configuration.
     *
     * @param string $aliasName The alias name to create (e.g., 'Modules\Module\App\Models\BaseUser')
     * @param string|null $actualClass The actual class name from configuration (e.g., 'Modules\User\App\Models\User')
     * @return bool True if alias was registered, false otherwise
     */
    function register_class_alias(string $aliasName, ?string $actualClass): bool
    {
        if (!class_exists($aliasName, false)) {
            if ($actualClass && class_exists($actualClass)) {
                return class_alias($actualClass, $aliasName);
            }
        }

        return false;
    }
}

