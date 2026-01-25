<?php

namespace RelayerCore\LaravelInstaller\Contracts;

interface EnvironmentWriter
{
    /**
     * Get the value of an environment variable.
     */
    public function get(string $key, $default = null): string|null;

    /**
     * Set the value of an environment variable.
     * Should persist to file immediately or on save().
     */
    public function set(string $key, string $value): void;

    /**
     * Set multiple values at once.
     */
    public function fill(array $values): void;

    /**
     * Save changes to the file.
     */
    public function save(): bool;
}
