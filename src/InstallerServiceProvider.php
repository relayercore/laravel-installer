<?php

namespace RelayerCore\LaravelInstaller;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RelayerCore\LaravelInstaller\Contracts\EnvironmentWriter;
use RelayerCore\LaravelInstaller\Contracts\InstallerStep;
use RelayerCore\LaravelInstaller\Http\Livewire\Installer;
use RelayerCore\LaravelInstaller\Services\DotEnvWriter;
use RelayerCore\LaravelInstaller\Services\StepManager;

/**
 * Registers all installer services, middleware, routes, views, and translations.
 *
 * This provider is auto-discovered via composer.json's extra.laravel.providers.
 * It bootstraps the installer wizard for any Laravel application, reading the
 * ordered step pipeline from config('installer.steps') so that host apps can
 * add, remove, or reorder steps without modifying this package.
 */
class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/installer.php', 'installer');

        // Register core services
        $this->app->bind(EnvironmentWriter::class, DotEnvWriter::class);

        // StepManager is a singleton so every consumer sees the same registered steps
        $this->app->singleton(StepManager::class);

        // Early installation check — runs before other service providers boot
        $this->earlyInstallationCheck();
    }

    /**
     * Check installation status very early and set a flag.
     * This prevents database errors in other service providers.
     */
    protected function earlyInstallationCheck(): void
    {
        $installedFile = config('installer.installed_file') ?? storage_path('installed');
        $isInstalled = file_exists($installedFile);

        // Store installation status in app container for other providers to check
        $this->app->instance('installer.is_installed', $isInstalled);

        // Force Debug Mode if not installed
        // This ensures Livewire assets (non-minified) load correctly
        // even if the user's default config has debug=false.
        if (!$isInstalled) {
            config(['app.debug' => true]);
        }
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/installer.php' => config_path('installer.php'),
        ], 'installer-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/installer'),
        ], 'installer-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/installer'),
        ], 'installer-lang');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'installer');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'installer');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/installer.php');

        // Register Livewire component
        Livewire::component('installer', Installer::class);

        // Boot the step pipeline from config
        $this->bootSteps();

        // Register middleware
        $this->registerMiddleware();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \RelayerCore\LaravelInstaller\Console\Commands\MakeInstallerStepCommand::class,
            ]);
        }
    }

    /**
     * Resolve each step class from config and register it with the StepManager.
     *
     * Steps are resolved through the container so they benefit from
     * automatic dependency injection (e.g. EnvironmentWriter in ConfigureEnvironment).
     */
    protected function bootSteps(): void
    {
        $stepManager = $this->app->make(StepManager::class);

        foreach (config('installer.steps', []) as $stepClass) {
            if (!is_string($stepClass) || !class_exists($stepClass)) {
                throw new \RuntimeException("Installer step [{$stepClass}] does not exist.");
            }

            $step = $this->app->make($stepClass);

            if (!$step instanceof InstallerStep) {
                throw new \RuntimeException("Installer step [{$stepClass}] must implement " . InstallerStep::class);
            }

            $stepManager->register($step);
        }
    }

    protected function registerMiddleware(): void
    {
        // Add to global middleware stack
        // This ensures the check runs for all requests (web, api, etc.)
        // preventing application errors if the app isn't installed yet.
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(Middleware\CheckInstallation::class);
    }
}
