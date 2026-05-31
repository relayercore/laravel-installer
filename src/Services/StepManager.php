<?php

namespace RelayerCore\LaravelInstaller\Services;

use RelayerCore\LaravelInstaller\Contracts\InstallerStep;

/**
 * Manages the ordered collection of installation steps.
 *
 * Acts as the central registry for the installer wizard's step pipeline.
 * Steps are registered during boot (by the ServiceProvider) and consumed
 * by the Livewire Installer component to drive navigation and execution.
 */
class StepManager
{
    /** @var array<string, InstallerStep> */
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
     *
     * @param bool $includeSkipped Whether to include steps that are marked as skipped.
     * @return array<string, InstallerStep>
     */
    public function getSteps(bool $includeSkipped = true): array
    {
        if ($includeSkipped) {
            return $this->steps;
        }

        return array_filter($this->steps, fn(InstallerStep $step) => !$step->isSkipped());
    }

    /**
     * Get a specific step by ID.
     */
    public function getStep(string $id): ?InstallerStep
    {
        return $this->steps[$id] ?? null;
    }

    /**
     * Get the next unskipped step after the given ID.
     */
    public function getNextStep(string $currentId): ?InstallerStep
    {
        $keys = array_keys($this->steps);
        $position = array_search($currentId, $keys);

        if ($position === false) {
            return null;
        }

        for ($i = $position + 1; $i < count($keys); $i++) {
            $step = $this->steps[$keys[$i]];
            if (!$step->isSkipped()) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Get the previous unskipped step before the given ID.
     */
    public function getPreviousStep(string $currentId): ?InstallerStep
    {
        $keys = array_keys($this->steps);
        $position = array_search($currentId, $keys);

        if ($position === false || $position === 0) {
            return null;
        }

        for ($i = $position - 1; $i >= 0; $i--) {
            $step = $this->steps[$keys[$i]];
            if (!$step->isSkipped()) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Check whether the given step is the last unskipped step in the pipeline.
     */
    public function isLastStep(string $currentId): bool
    {
        $visibleSteps = $this->getSteps(false);
        $keys = array_keys($visibleSteps);

        return !empty($keys) && end($keys) === $currentId;
    }

    /**
     * Check whether the given step is the first unskipped step in the pipeline.
     */
    public function isFirstStep(string $currentId): bool
    {
        $visibleSteps = $this->getSteps(false);
        $keys = array_keys($visibleSteps);

        return !empty($keys) && reset($keys) === $currentId;
    }
}
