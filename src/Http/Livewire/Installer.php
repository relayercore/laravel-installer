<?php

namespace RelayerCore\LaravelInstaller\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RelayerCore\LaravelInstaller\Contracts\ModuleActivator;
use RelayerCore\LaravelInstaller\Services\StepManager;

/**
 * Main Livewire component that drives the installer wizard UI.
 *
 * Steps are injected via the container-bound StepManager (populated by
 * the ServiceProvider from config). This component handles navigation,
 * validation, processing, and the final installation sequence.
 */
#[Layout('installer::layouts.installer')]
class Installer extends Component
{
    #[Locked]
    public string $currentStepId = '';
    public array $state = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'connection' => 'mysql',
        'database' => '',
        'username' => 'root',
        'password' => '',
    ];
    public array $errorBag = [];
    public bool $loading = false;
    public array $logs = [];
    public ?array $testConnectionResult = null;

    protected StepManager $stepManager;

    public function boot(StepManager $stepManager): void
    {
        $this->stepManager = $stepManager;
    }

    public function mount(): void
    {
        // Auto-init .env if missing (Zero-Config First Load)
        if (!file_exists(base_path('.env')) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), base_path('.env'));

            redirect(request()->url());
            return;
        }

        if ($this->isInstalled()) {
            redirect('/');
            return;
        }

        // Initialize currentStepId to the first unskipped registered step
        $steps = $this->stepManager->getSteps(false);
        if (!empty($steps)) {
            $this->currentStepId = array_key_first($steps);
        }

        // Initialize default state values for any extra environment fields
        foreach (config('installer.environment_fields', []) as $envKey => $fieldConfig) {
            $stateKey = $fieldConfig['state_key'] ?? strtolower($envKey);
            if (!isset($this->state[$stateKey])) {
                $this->state[$stateKey] = $fieldConfig['default'] ?? '';
            }
        }
    }

    public function next(): void
    {
        $step = $this->stepManager->getStep($this->currentStepId);
        Log::info("Installer: Attempting next step from [{$this->currentStepId}]");

        try {
            $validation = $step->validate($this->state);
            Log::info("Installer: Validation result for [{$step->id()}]: " . ($validation ? 'PASS' : 'FAIL'));

            if (!$validation) {
                $this->addError('global', __('installer::installer.error_generic'));
                return;
            }

            $step->process($this->state);

            // Advance
            $next = $this->stepManager->getNextStep($this->currentStepId);
            if ($next) {
                Log::info("Installer: Moving to next step [{$next->id()}]");
                $this->currentStepId = $next->id();
            } else {
                Log::info("Installer: No next step, finishing.");
                $this->finish();
            }

        } catch (Exception $e) {
            Log::error("Installer: Error in next(): " . $e->getMessage());
            $this->addError('global', $e->getMessage());
        }
    }

    public function previous(): void
    {
        $prev = $this->stepManager->getPreviousStep($this->currentStepId);

        if ($prev) {
            $this->currentStepId = $prev->id();
            $this->resetErrorBag();
        }
    }

    public function goToStep(string $stepId): void
    {
        $steps = array_keys($this->stepManager->getSteps(false));
        $currentIndex = array_search($this->currentStepId, $steps);
        $targetIndex = array_search($stepId, $steps);

        // Only allow navigating to past steps
        if ($targetIndex !== false && $targetIndex < $currentIndex) {
            $this->currentStepId = $stepId;
            $this->resetErrorBag();
        }
    }

    public function finish(): void
    {
        // 1. Activate module/vertical if a ModuleActivator is bound and a vertical was selected
        if (
            isset($this->state['vertical']) &&
            $this->state['vertical'] !== 'universal' &&
            interface_exists(ModuleActivator::class) &&
            app()->bound(ModuleActivator::class)
        ) {
            try {
                $activator = app(ModuleActivator::class);
                $activator->activate($this->state['vertical']);
                Log::info("Installer: Activated vertical [{$this->state['vertical']}] via ModuleActivator.");
            } catch (Exception $e) {
                // Don't fail the install, but log it
                Log::error("Installer: Failed to activate vertical: " . $e->getMessage());
            }
        }

        // 2. Run host application's after_install callback
        $afterInstallClass = config('installer.after_install');
        if (is_string($afterInstallClass) && class_exists($afterInstallClass)) {
            $afterInstall = app($afterInstallClass);
            if (is_callable($afterInstall)) {
                $afterInstall();
            }
        }

        // 3. Regenerate the App Key (Security Best Practice)
        Artisan::call('key:generate', ['--force' => true]);

        // 4. Mark installed
        $installedFile = config('installer.installed_file', storage_path('installed'));
        file_put_contents($installedFile, now());

        // 5. Redirect
        $redirect = config('installer.redirect_after_install', '/admin');

        $this->dispatch('installer-finishing');

        redirect($redirect);
    }

    protected function isInstalled(): bool
    {
        return file_exists(config('installer.installed_file', storage_path('installed')));
    }

    public function testDatabase(): void
    {
        $step = $this->stepManager->getStep('environment');
        $this->testConnectionResult = null;

        try {
            if (method_exists($step, 'testConnection')) {
                $step->testConnection($this->state);
                $this->testConnectionResult = [
                    'success' => true,
                    'message' => __('installer::installer.environment_test_success'),
                ];
            }
        } catch (Exception $e) {
            $this->testConnectionResult = ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        $step = $this->stepManager->getStep($this->currentStepId);

        return view('installer::installer', [
            'step' => $step,
            'steps' => $this->stepManager->getSteps(false),
            'isLastStep' => $this->stepManager->isLastStep($this->currentStepId),
            'isFirstStep' => $this->stepManager->isFirstStep($this->currentStepId),
        ]);
    }
}
