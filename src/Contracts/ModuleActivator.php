<?php

namespace RelayerCore\LaravelInstaller\Contracts;

interface ModuleActivator
{
    /**
     * Activate the specified module or vertical.
     *
     * @param string $moduleSlug The slug of the module/vertical to activate.
     */
    public function activate(string $moduleSlug): void;
}
