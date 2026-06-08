<?php

namespace RelayerCore\LaravelInstaller\Services;

use RelayerCore\LaravelInstaller\Contracts\InstallationStateManager;

class FileInstallationStateManager implements InstallationStateManager
{
    public function isInstalled(): bool
    {
        return file_exists($this->getFile());
    }

    public function markInstalled(): void
    {
        file_put_contents($this->getFile(), now()->toIso8601String());
    }

    protected function getFile(): string
    {
        return config('installer.installed_file') ?? storage_path('installed');
    }
}
