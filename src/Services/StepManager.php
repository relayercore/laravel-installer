<?php

namespace RelayerCore\LaravelInstaller\Services;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

class StepManager
{
    protected array $steps = [];

    /**
     * Register a new installation step.
     */
    public function register(InstallerStep $step): void
    {
        $this->steps[$step->id()] = $step;
    }

    /**
     * Get all registered steps.
     * @return InstallerStep[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Get a specific step by ID.
     */
    public function getStep(string $id): ?InstallerStep
    {
        return $this->steps[$id] ?? null;
    }

    /**
     * Get the next step after the given ID.
     */
    public function getNextStep(string $currentId): ?InstallerStep
    {
        $keys = array_keys($this->steps);
        $position = array_search($currentId, $keys);

        if ($position === false || not(isset($keys[$position + 1]))) {
            return null;
        }

        return $this->steps[$keys[$position + 1]];
    }
}
