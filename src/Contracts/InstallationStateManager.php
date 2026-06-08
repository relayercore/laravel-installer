<?php

namespace RelayerCore\LaravelInstaller\Contracts;

interface InstallationStateManager
{
    /**
     * Check if the application is installed.
     */
    public function isInstalled(): bool;

    /**
     * Mark the application as installed.
     */
    public function markInstalled(): void;
}
