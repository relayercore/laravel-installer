<?php

declare(strict_types=1);

namespace RelayerCore\LaravelInstaller\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RelayerCore\LaravelInstaller\Contracts\InstallationStateManager;
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
        'database' => 'bookflow',
        'username' => 'root',
        'password' => '',
        'timezone' => 'UTC',
        'currency' => 'USD',
        'load_demo_data' => true,
    ];
    public array $errorBag = [];
    public bool $loading = false;
    public array $logs = [];
    public ?array $testConnectionResult = null;

    protected const SESSION_KEY = 'installer.progress';

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

        // Resume from last completed step on mid-install refresh
        $progress = session()->get(self::SESSION_KEY, []);
        $steps = $this->stepManager->getSteps(false);

        if (!empty($progress)) {
            $allStepIds = array_keys($steps);
            $lastCompleted = $progress[array_key_last($progress)];
            $lastIndex = array_search($lastCompleted, $allStepIds);
            $resumeIndex = $lastIndex !== false ? $lastIndex + 1 : 0;

            if (isset($allStepIds[$resumeIndex])) {
                $this->currentStepId = $allStepIds[$resumeIndex];
            } else {
                // All steps completed somehow but the installed file is missing
                $this->currentStepId = array_key_first($steps);
            }
        } else {
            // Initialize currentStepId to the first unskipped registered step
            if (!empty($steps)) {
                $this->currentStepId = array_key_first($steps);
            }
        }

        // Initialize default state values for any extra environment fields
        foreach (config('installer.environment_fields', []) as $envKey => $fieldConfig) {
            $stateKey = $fieldConfig['state_key'] ?? strtolower($envKey);
            if (!isset($this->state[$stateKey])) {
                if ($fieldConfig['type'] === 'checkbox') {
                    $this->state[$stateKey] = (bool) ($fieldConfig['default'] ?? false);
                } elseif ($fieldConfig['type'] === 'select') {
                    $this->state[$stateKey] = $fieldConfig['default'] ?? array_key_first($fieldConfig['options'] ?? []);
                } else {
                    $this->state[$stateKey] = $fieldConfig['default'] ?? '';
                }
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

            // Persist completed step to session for mid-install refresh recovery
            $progress = session()->get(self::SESSION_KEY, []);
            $progress[] = $this->currentStepId;
            session()->put(self::SESSION_KEY, $progress);

            // Advance
            $next = $this->stepManager->getNextStep($this->currentStepId);
            if ($next) {
                Log::info("Installer: Moving to next step [{$next->id()}]");
                $this->currentStepId = $next->id();
                $this->dispatch('step-changed');
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
            $this->dispatch('step-changed');
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
            $this->dispatch('step-changed');
            $this->resetErrorBag();
        }
    }

    public function finish(): void
    {
        // Clear previous progress file
        $progressFile = storage_path('framework/installer-progress.json');
        if (file_exists($progressFile)) {
            @unlink($progressFile);
        }

        // Bind streaming callback — writes to a file for Alpine polling
        // instead of using Livewire's chunked stream() which causes
        // Apache/Nginx buffering issues on some environments (like Laragon).
        app()->instance('installer.stream', function(string $message) use ($progressFile) {
            // Clean up raw console output into user-friendly messages
            $clean = preg_replace(
                ['/^(INFO|WARN|ERROR|DEBUG)\s+/i', '/\s{2,}/', '/^\.+$/'],
                ['', ' ', ''],
                trim($message)
            );
            if (empty($clean)) return;

            $data = ['messages' => [], 'timestamp' => time()];
            if (file_exists($progressFile)) {
                $existing = json_decode(file_get_contents($progressFile), true);
                $data['messages'] = $existing['messages'] ?? [];
            }
            $data['messages'][] = $clean;
            file_put_contents($progressFile, json_encode($data), LOCK_EX);
        });

        // 1. Activate module/vertical if a ModuleActivator is bound and a vertical was selected
        if (
            isset($this->state['vertical']) &&
            $this->state['vertical'] !== 'general' &&
            interface_exists(ModuleActivator::class) &&
            app()->bound(ModuleActivator::class)
        ) {
            try {
                $activator = app(ModuleActivator::class);
                $activator->activate($this->state['vertical'], $this->state);
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
                $afterInstall($this->state);
            }
        }

        // 3. Regenerate the App Key (Security Best Practice)
        // Defer to terminating() because key:generate modifies .env and restarts `php artisan serve`
        app()->terminating(function () {
            Artisan::call('key:generate', ['--force' => true]);
        });

        // 4. Clear install progress from session
        session()->forget(self::SESSION_KEY);

        // 5. Mark installed using the configured state manager
        app(InstallationStateManager::class)->markInstalled();

        // 6. Dispatch success event for confetti and UI update
        $redirect = config('installer.redirect_after_install', '/admin');

        $this->dispatch('installer-finishing');
        $this->dispatch('installation-success', ['redirectUrl' => $redirect]);
    }

    protected function isInstalled(): bool
    {
        return app(InstallationStateManager::class)->isInstalled();
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
