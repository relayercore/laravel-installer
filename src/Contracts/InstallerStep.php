<?php

namespace RelayerCore\LaravelInstaller\Contracts;

interface InstallerStep
{
    /**
     * Get the unique identifier for the step (e.g., 'requirements').
     */
    public function id(): string;

    /**
     * Get the human-readable label for the step wizard.
     */
    public function label(): string;

    /**
     * Get the view name associated with this step.
     */
    public function view(): string;

    /**
     * Determine if the step is skipped (e.g., if conditions are met).
     */
    public function isSkipped(): bool;

    /**
     * Validate the step's data.
     * Throws ValidationException or returns false on failure.
     */
    public function validate(array $data = []): bool;

    /**
     * Execute the step's primary logic (e.g., migrate, create user).
     */
    public function process(array $data = []): void;
}
