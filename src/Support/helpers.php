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

if (!function_exists('register_class_aliases')) {
    /**
     * Register multiple class aliases from an array.
     *
     * This helper function allows you to register multiple class aliases at once.
     * The array should be in the format: ['alias_name' => 'actual_class', ...]
     *
     * @param array<string, string|null> $aliases Array of aliases where key is alias name and value is actual class
     * @return array<string, bool> Array of results where key is alias name and value is registration success status
     */
    function register_class_aliases(array $aliases): array
    {
        $results = [];

        foreach ($aliases as $aliasName => $actualClass) {
            $results[$aliasName] = register_class_alias($aliasName, $actualClass);
        }

        return $results;
    }
}

if (!function_exists('registerClassAliases')) {
    /**
     * Register multiple class aliases from an array (camelCase alias).
     *
     * This is an alias for register_class_aliases() for those who prefer camelCase naming.
     *
     * @param array<string, string|null> $aliases Array of aliases where key is alias name and value is actual class
     * @return array<string, bool> Array of results where key is alias name and value is registration success status
     */
    function registerClassAliases(array $aliases): array
    {
        return register_class_aliases($aliases);
    }
}

