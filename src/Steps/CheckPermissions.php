<?php

namespace RelayerCore\LaravelInstaller\Steps;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class CheckPermissions implements InstallerStep
{
    public function id(): string
    {
        return 'permissions';
    }

    public function label(): string
    {
        return 'Directory Permissions';
    }

    public function view(): string
    {
        return 'installer::steps.permissions';
    }

    public function isSkipped(): bool
    {
        return false; // Could verify if all passed to auto-skip
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
                // Try to create it if missing? For now just fail.
                 $results[$dir] = false;
                 continue;
            }
            $results[$dir] = is_writable($path);
        }
        
        return $results;
    }
}
