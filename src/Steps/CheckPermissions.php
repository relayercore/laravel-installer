<?php

namespace RelayerCore\LaravelInstaller\Steps;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

/**
 * Validates that critical application directories are writable.
 *
 * Directories are read from config('installer.writable_directories') so each
 * host application can declare which paths need write access.
 */
class CheckPermissions implements InstallerStep
{
    public function id(): string
    {
        return 'permissions';
    }

    public function label(): string
    {
        return __('installer::installer.step_permissions');
    }

    public function view(): string
    {
        return 'installer::steps.permissions';
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function validate(array $data = []): bool
    {
        $permissions = $this->check();
        return !in_array(false, $permissions);
    }

    public function process(array $data = []): void
    {
        // No side effects
    }

    public function check(): array
    {
        $results = [];
        $results['.env'] = is_writable(base_path('.env'));

        foreach (config('installer.writable_directories', []) as $dir) {
            $path = base_path($dir);
            if (!file_exists($path)) {
                $results[$dir] = false;
                continue;
            }
            $results[$dir] = is_writable($path);
        }

        return $results;
    }
}
