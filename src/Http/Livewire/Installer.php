<?php

namespace RelayerCore\LaravelInstaller\Http\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use RelayerCore\LaravelInstaller\Services\StepManager;
use RelayerCore\LaravelInstaller\Steps\CheckPermissions;
use RelayerCore\LaravelInstaller\Steps\CheckRequirements;
use RelayerCore\LaravelInstaller\Steps\ConfigureEnvironment;
use RelayerCore\LaravelInstaller\Steps\CreateAdmin;
use RelayerCore\LaravelInstaller\Steps\RunMigrations;

#[Layout('installer::layouts.installer')]
class Installer extends Component
{
    public string $currentStepId = 'requirements';
    public array $state = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'connection' => 'mysql',
    ];
    public array $errorBag = [];
    public bool $loading = false;
    public array $logs = [];
    public ?array $testConnectionResult = null; // { success: bool, message: string }

    protected StepManager $stepManager;

    public function boot(StepManager $stepManager)
    {
        $this->stepManager = $stepManager;
        $this->registerSteps();
    }
    
    protected function registerSteps(): void
    {
        // Ideally this would be done in the ServiceProvider, 
        // but for now we register them here to keep it simple.
        $this->stepManager->register(new CheckRequirements());
        $this->stepManager->register(new CheckPermissions());
        
        // Inject dependencies manually or via container if we moved registration to SP
        $envWriter = app(\RelayerCore\LaravelInstaller\Services\DotEnvWriter::class);
        $this->stepManager->register(new ConfigureEnvironment($envWriter));
        
        $this->stepManager->register(new RunMigrations());
        $this->stepManager->register(new CreateAdmin());
    }

    public function mount()
    {
        // 1. Auto-init .env if missing (Zero-Config First Load)
        if (!file_exists(base_path('.env')) && file_exists(base_path('.env.example'))) {
            copy(base_path('.env.example'), base_path('.env'));
            
            // Use JS redirect to ensure browser reloads even if headers are sent
            echo "<script>window.location.reload();</script>";
            exit;
        }

        if ($this->isInstalled()) {
             return redirect('/');
        }
    }


    public function next()
    {
        $step = $this->stepManager->getStep($this->currentStepId);
        \Illuminate\Support\Facades\Log::info("Installer: Attempting next step from [{$this->currentStepId}]");
        
        try {
            $validation = $step->validate($this->state);
            \Illuminate\Support\Facades\Log::info("Installer: Validation result for [{$step->id()}]: " . ($validation ? 'PASS' : 'FAIL'));

            if (!$validation) {
                // Validation failed (handled by step throwing or returning false)
                // If the step returns simple false, we might want a generic error
                $this->addError('global', 'Please check your inputs and try again.');
                return;
            }

            $step->process($this->state);
            
            // Advance
            $next = $this->stepManager->getNextStep($this->currentStepId);
            if ($next) {
                \Illuminate\Support\Facades\Log::info("Installer: Moving to next step [{$next->id()}]");
                $this->currentStepId = $next->id();
            } else {
                \Illuminate\Support\Facades\Log::info("Installer: No next step, finishing.");
                // Finale
                $this->finish();
            }
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Installer: Error in next(): " . $e->getMessage());
            $this->addError('global', $e->getMessage());
        }
    }

    public function finish()
    {
        // 1. Regenerate the App Key (Security Best Practice)
        // This invalidates the generic "boot key" and sets a unique one for this app.
        // Note: This will invalidate the current session, which is expected.
        \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);

        // 2. Mark installed
        $installedFile = config('installer.installed_file', storage_path('installed'));
        file_put_contents($installedFile, now());
        
        // 3. Redirect
        $redirect = config('installer.redirect_after_install', '/admin');
        return redirect($redirect);
    }
    
    protected function isInstalled(): bool
    {
        return file_exists(config('installer.installed_file', storage_path('installed')));
    }

    public function testDatabase()
    {
        $step = $this->stepManager->getStep('environment');
        $this->testConnectionResult = null;

        try {
            if (method_exists($step, 'testConnection')) {
                $step->testConnection($this->state);
                $this->testConnectionResult = ['success' => true, 'message' => 'Connection successful! Database is ready.'];
            }
        } catch (\Exception $e) {
            $this->testConnectionResult = ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        $step = $this->stepManager->getStep($this->currentStepId);
        
        return view('installer::installer', [
            'step' => $step,
            'steps' => $this->stepManager->getSteps(),
        ]);
    }
}
